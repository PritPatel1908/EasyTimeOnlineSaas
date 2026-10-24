<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualShiftChangeController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\WeekOffChangeController;
use App\Jobs\Tenant\CreateWeekOffMuster;
use App\Jobs\Tenant\ProcessWeekOffChangeImport;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChange;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\Guard;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantManualStructureImportExportTest extends TestCase
{
    public function test_manual_shift_change_import_sample_matches_export_columns(): void
    {
        $response = app(ManualShiftChangeController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $rows = $this->readCsvResponse($response);

        $this->assertSame(ShiftChange::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(ShiftChange::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('employee codes separated by commas', $rows[1][0]);
        $this->assertStringContainsString('shift codes separated by commas', $rows[1][2]);
    }

    public function test_week_off_change_import_sample_matches_export_columns(): void
    {
        $response = app(WeekOffChangeController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $rows = $this->readCsvResponse($response);

        $this->assertSame(WeekOffChange::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(WeekOffChange::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('employee codes separated by commas', $rows[1][0]);
        $this->assertStringContainsString('weekday name', $rows[1][5]);
        $this->assertStringContainsString('Yes/No', $rows[1][7]);
    }

    public function test_week_off_change_import_dispatches_an_import_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);
        $file = UploadedFile::fake()->createWithContent(
            'week_off_changes_import_sample.csv',
            implode(',', WeekOffChange::IMPORT_EXPORT_COLUMNS)
                . "\nE001,2026-10-23,0,2026-10-24,Correction,0,1,1,0,0,0,0\n",
        );
        $request = Request::create(
            '/time-management/week-off-changes/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(WeekOffChangeController::class)->import($request);

        $this->assertStringContainsString('Week-off change import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessWeekOffChangeImport::class, static function (ProcessWeekOffChangeImport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'import'
                && str_contains($job->filePath, 'week-off-change-imports/');
        });
        Bus::assertNotDispatched(CreateWeekOffMuster::class);
    }

    public function test_notification_preview_reads_manual_shift_change_exports(): void
    {
        Storage::fake('local');
        $fileName = 'manual_shift_changes_20261023_123456.csv';
        Storage::disk('local')->put(
            'manual-shift-change-exports/'.$fileName,
            "employee_codes,shift_type,shifts\nE001,fixed,DAY\n",
        );
        $readPreview = new \ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/manual-shift-changes/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'shift_type', 'shifts'], $preview['headers']);
        $this->assertSame([['E001', 'fixed', 'DAY']], $preview['rows']);
    }

    public function test_notification_preview_reads_week_off_change_exports(): void
    {
        Storage::fake('local');
        $fileName = 'week_off_changes_20261023_123456.csv';
        Storage::disk('local')->put(
            'week-off-change-exports/'.$fileName,
            "employee_codes,from_date,week_days,wo_types,first_week\nE001,2026-10-23,Sunday,Full Day,Yes\n",
        );
        $readPreview = new \ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/week-off-changes/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'from_date', 'week_days', 'wo_types', 'first_week'], $preview['headers']);
        $this->assertSame([['E001', '2026-10-23', 'Sunday', 'Full Day', 'Yes']], $preview['rows']);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readCsvResponse(StreamedResponse $response): array
    {
        ob_start();
        ($response->getCallback())();
        $contents = ob_get_clean();

        return array_map('str_getcsv', explode("\n", trim((string) $contents)));
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }
}
