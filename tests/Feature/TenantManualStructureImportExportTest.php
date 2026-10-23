<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualShiftChangeController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\WeekOffChangeController;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\WeekOffChange;
use Illuminate\Support\Facades\Storage;
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
        $this->assertStringContainsString('comma-separated', $rows[1][5]);
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
            "employee_codes,from_date,week_days,wo_types,first_week\nE001,2026-10-23,0,1,1\n",
        );
        $readPreview = new \ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/week-off-changes/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'from_date', 'week_days', 'wo_types', 'first_week'], $preview['headers']);
        $this->assertSame([['E001', '2026-10-23', '0', '1', '1']], $preview['rows']);
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
}
