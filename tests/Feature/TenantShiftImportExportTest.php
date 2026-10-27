<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ShiftController;
use App\Jobs\Tenant\GenerateShiftExport;
use App\Jobs\Tenant\ProcessShiftImport;
use App\Models\Tenant\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantShiftImportExportTest extends TestCase
{
    public function test_shift_import_export_columns_cover_the_shift_fields(): void
    {
        $this->assertSame([
            'name',
            'code',
            'location',
            'company',
            'in_time',
            'out_time',
            'first_half_end_time',
            'second_half_start_time',
            'brack_start_time',
            'brack_end_time',
            'auto_from',
            'auto_to',
            'is_night_shift',
            'set_cutoff',
            'cutoff_time',
            'status',
        ], Shift::IMPORT_EXPORT_COLUMNS);
    }

    public function test_import_sample_matches_the_shift_import_columns(): void
    {
        $response = app(ShiftController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('shifts_import_sample.csv', $response->headers->get('Content-Disposition', ''));

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(Shift::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(Shift::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_shift_import_accepts_twelve_hour_times_and_normalizes_them(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'shift-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents($filePath, "name,code,auto_to\nEvening,PM,05:14 PM\n");

            $job = new ProcessShiftImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessShiftImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);

            $this->assertSame('17:14:00', $rows[2]['auto_to']);

            $validateImportRow = new ReflectionMethod(ProcessShiftImport::class, 'validateImportRow');
            $this->assertSame([], $validateImportRow->invoke($job, $rows[2]));
        } finally {
            unlink($filePath);
        }
    }

    public function test_shift_import_accepts_a_24_hour_cutoff_duration(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'shift-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents($filePath, "name,code,set_cutoff,cutoff_time\nDay,DAY,Yes,24:00\n");

            $job = new ProcessShiftImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessShiftImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);
            $row = $rows[2];

            $this->assertSame('24:00', $row['cutoff_time']);
            $validateImportRow = new ReflectionMethod(ProcessShiftImport::class, 'validateImportRow');
            $this->assertSame([], $validateImportRow->invoke($job, $row));
        } finally {
            unlink($filePath);
        }
    }

    public function test_export_dispatches_tenant_queue_job(): void
    {
        Bus::fake();

        $response = app(ShiftController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Shift export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateShiftExport::class, function (GenerateShiftExport $job): bool {
            return $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Ashifts_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_job_with_duplicate_toggle(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(\App\Models\Tenant\User::class);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $headers = implode(',', Shift::IMPORT_EXPORT_COLUMNS);
        $file = UploadedFile::fake()->createWithContent(
            'shifts_import_sample.csv',
            $headers . "\n"
        );
        $request = Request::create('/time-management/shifts/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], [
            'file' => $file,
        ]);

        $response = app(ShiftController::class)->import($request);

        $this->assertStringContainsString('Shift import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessShiftImport::class, function (ProcessShiftImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'shift-imports/');
        });
    }

    public function test_download_export_returns_file_response_when_file_exists(): void
    {
        Storage::fake('local');
        $fileName = 'shifts_20261020_123456.csv';
        Storage::disk('local')->put('shift-exports/' . $fileName, "name,code\nMorning,AM\n");

        $response = app(ShiftController::class)->downloadExport($fileName);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('shifts.csv', $response->headers->get('Content-Disposition', ''));
    }
}
