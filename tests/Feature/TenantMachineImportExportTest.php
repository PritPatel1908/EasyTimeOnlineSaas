<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\MachineController;
use App\Jobs\Tenant\GenerateMachineExport;
use App\Jobs\Tenant\ProcessMachineImport;
use App\Models\Tenant\Machine;
use App\Support\CsvImportTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantMachineImportExportTest extends TestCase
{
    public function test_export_dispatches_machine_export_job(): void
    {
        Bus::fake();

        $response = app(MachineController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Machine export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateMachineExport::class, function (GenerateMachineExport $job): bool {
            return $job->userId === null
                && $job->connection === 'database_tenant'
                && preg_match('/\Amachines_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_sample_matches_machine_model_columns(): void
    {
        $response = app(MachineController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('machines_import_sample.csv', $response->headers->get('Content-Disposition', ''));

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(Machine::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]));
        $this->assertCount(count(Machine::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_import_dispatches_job_with_duplicate_update_setting(): void
    {
        Bus::fake();
        Storage::fake('local');

        $user = Mockery::mock(\App\Models\Tenant\User::class);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $file = UploadedFile::fake()->createWithContent(
            'machines_import_sample.csv',
            "name,machine_ip,machine_serial_number,dms_device_id,dms_area_id,sync_dms,network_status,access_direction,location,company,area,status\nGate 1,10.0.0.2,SN-1,12,4,1,online,in,HQ,ACME,Main,Active\n",
        );

        $request = Request::create('/company-structure/machines/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], ['file' => $file]);

        $response = app(MachineController::class)->import($request);

        $this->assertStringContainsString('Machine import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessMachineImport::class, function (ProcessMachineImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'machine-imports/');
        });
    }
}
