<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AreaController;
use App\Jobs\Tenant\GenerateAreaExport;
use App\Jobs\Tenant\ProcessAreaImport;
use App\Models\Tenant\Area;
use App\Support\CsvImportTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantAreaImportExportTest extends TestCase
{
    public function test_export_dispatches_area_export_job(): void
    {
        Bus::fake();

        $response = app(AreaController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Area export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateAreaExport::class, function (GenerateAreaExport $job): bool {
            return $job->userId === null
                && $job->connection === 'database_tenant'
                && preg_match('/\Aareas_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_sample_matches_area_model_columns(): void
    {
        $response = app(AreaController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('areas_import_sample.csv', $response->headers->get('Content-Disposition', ''));

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame([
            'code',
            'name',
            'company',
            'location',
            'time_limit_in_min',
            'dms_area_id',
            'status',
        ], $rows[0]);
        $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]));
        $this->assertCount(count(Area::IMPORT_EXPORT_COLUMNS), $rows[1]);
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
            'areas_import_sample.csv',
            "code,name,company,location,time_limit_in_min,dms_area_id,status\nA-1,Shop Floor,ACME,HQ,30,145,Inactive\n",
        );

        $request = Request::create('/company-structure/areas/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], ['file' => $file]);

        $response = app(AreaController::class)->import($request);

        $this->assertStringContainsString('Area import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessAreaImport::class, function (ProcessAreaImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'area-imports/');
        });
    }
}
