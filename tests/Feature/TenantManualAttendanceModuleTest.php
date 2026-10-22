<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualAttendanceController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Requests\Tenant\StoreManualAttendanceRequest;
use App\Jobs\Tenant\GenerateManualAttendanceExport;
use App\Jobs\Tenant\ProcessManualAttendanceImport;
use App\Models\Tenant\ManualAttendance;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantManualAttendanceModuleTest extends TestCase
{
    public function test_import_export_columns_match_manual_attendance_fields(): void
    {
        $this->assertSame([
            'employee_codes',
            'location',
            'company',
            'shift_type',
            'shift',
            'shift_rotation',
            'in_time',
            'out_time',
            'reason',
        ], ManualAttendance::IMPORT_EXPORT_COLUMNS);
        $this->assertNotContains('status', ManualAttendance::IMPORT_EXPORT_COLUMNS);
    }

    public function test_manual_attendance_request_validates_shift_fields_and_datetime_values(): void
    {
        $rules = (new StoreManualAttendanceRequest())->rules();

        $this->assertSame(['required', 'array', 'min:1'], $rules['users']);
        $this->assertSame(['nullable', 'string', 'in:auto,fixed,rotational'], $rules['shift_type']);
        $this->assertContains('required_if:shift_type,auto,fixed', $rules['shift_id']);
        $this->assertContains('required_if:shift_type,rotational', $rules['shift_rotation_id']);
        $this->assertContains('after_or_equal:in_time', $rules['out_time']);
    }

    public function test_import_sample_uses_manual_attendance_columns(): void
    {
        $response = app(ManualAttendanceController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(ManualAttendance::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(ManualAttendance::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('employee codes', $rows[1][0]);
        $this->assertStringContainsString('YYYY-MM-DD HH:MM:SS', $rows[6]);
    }

    public function test_import_job_parses_attendance_headers_and_values(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'manual-attendance-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents(
                $filePath,
                "employee_codes,location,company,shift_type,shift,shift_rotation,in_time,out_time,reason\nE001,Main,ACME,fixed,DAY,,2026-10-20 09:00:00,2026-10-20 18:00:00,Correction\n",
            );
            $job = new ProcessManualAttendanceImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessManualAttendanceImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);

            $this->assertSame('E001', $rows[2]['employee_codes']);
            $this->assertSame('fixed', $rows[2]['shift_type']);
            $this->assertSame('2026-10-20 09:00:00', $rows[2]['in_time']);
        } finally {
            unlink($filePath);
        }
    }

    public function test_export_dispatches_manual_attendance_export_job(): void
    {
        Bus::fake();
        $this->mockTenantUser(17);

        $response = app(ManualAttendanceController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Manual attendance export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateManualAttendanceExport::class, function (GenerateManualAttendanceExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Amanual_attendances_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_manual_attendance_import_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);
        $file = UploadedFile::fake()->createWithContent(
            'manual_attendances_import_sample.csv',
            implode(',', ManualAttendance::IMPORT_EXPORT_COLUMNS) . "\nE001,,,fixed,DAY,,2026-10-20 09:00:00,2026-10-20 18:00:00,Correction\n",
        );
        $request = Request::create(
            '/time-management/manual-attendances/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(ManualAttendanceController::class)->import($request);

        $this->assertStringContainsString('Manual attendance import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessManualAttendanceImport::class, function (ProcessManualAttendanceImport $job): bool {
            return $job->userId === 17 && str_contains($job->filePath, 'manual-attendance-imports/');
        });
    }

    public function test_notification_preview_reads_manual_attendance_exports(): void
    {
        Storage::fake('local');
        $fileName = 'manual_attendances_20261020_123456.csv';
        Storage::disk('local')->put('manual-attendance-exports/' . $fileName, "employee_codes,in_time\nE001,2026-10-20 09:00:00\n");
        $readPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/manual-attendances/export/download/' . $fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'in_time'], $preview['headers']);
        $this->assertSame([['E001', '2026-10-20 09:00:00']], $preview['rows']);
    }

    public function test_manual_attendance_form_uses_schema_fields_without_status(): void
    {
        view()->share('errors', new ViewErrorBag());

        $html = view('time-management.manual-attendances.partials.form-fields', [
            'attendance' => new ManualAttendance(),
            'users' => collect(),
            'locations' => collect(),
            'companies' => collect(),
            'shifts' => collect(),
            'shiftRotations' => collect(),
        ])->render();

        $this->assertStringContainsString('name="in_time"', $html);
        $this->assertStringContainsString('name="out_time"', $html);
        $this->assertStringContainsString('name="shift_rotation_id"', $html);
        $this->assertStringNotContainsString('name="status"', $html);
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }
}
