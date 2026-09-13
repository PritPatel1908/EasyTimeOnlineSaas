<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\CompanyController;
use App\Jobs\Tenant\GenerateCompanyExport;
use App\Jobs\Tenant\ProcessCompanyImport;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantCompanyImportExportTest extends TestCase
{
    public function test_default_queue_connection_is_tenant_aware(): void
    {
        $this->assertSame('database_tenant', config('queue.default'));
    }

    public function test_queue_tenancy_bootstrapper_is_enabled(): void
    {
        $bootstrappers = config('tenancy.bootstrappers', []);

        $this->assertContains(
            \Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
            $bootstrappers,
        );
    }

    public function test_export_dispatches_queue_job(): void
    {
        Bus::fake();

        $response = app(CompanyController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue(str_contains($response->getSession()->get('success', ''), 'Company export has started'));
        Bus::assertDispatched(GenerateCompanyExport::class, function (GenerateCompanyExport $job): bool {
            return $job->userId === null
                && $job->connection === 'database_tenant'
                && $job->queue === 'tenant'
                && preg_match('/\Acompanies_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_sample_uses_common_location_header(): void
    {
        $response = app(CompanyController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('companies_import_sample.csv', $response->headers->get('Content-Disposition', ''));

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();

        $this->assertSame("name,code,email,status,location\n", $csv);
    }

    public function test_import_dispatches_queue_job_with_duplicate_toggle(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(\App\Models\Tenant\User::class);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $file = UploadedFile::fake()->createWithContent(
            'companies.csv',
            "name,code,email,status,location_id,location_name,location_code\nUpdated Company,CMP-001,updated@example.com,Active,1,HQ,HQ\n"
        );

        $request = Request::create('/company-structure/companies/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], [
            'file' => $file,
        ]);

        $response = app(CompanyController::class)->import($request);

        $this->assertTrue(str_contains($response->getSession()->get('success', ''), 'Company import has started'));
        Bus::assertDispatched(ProcessCompanyImport::class, function (ProcessCompanyImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords === true
                && str_contains($job->filePath, 'company-imports/');
        });
    }

    public function test_download_export_returns_streamed_response_when_export_file_exists(): void
    {
        Storage::fake('local');
        $fileName = 'companies_20260913_123456.csv';
        Storage::disk('local')->put('company-exports/' . $fileName, "name,code\nDefault Company,COMP-1\n");

        $response = app(CompanyController::class)->downloadExport($fileName);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('companies.csv', $response->headers->get('Content-Disposition', ''));
    }
}
