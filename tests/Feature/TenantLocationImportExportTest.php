<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\LocationController;
use App\Jobs\Tenant\GenerateLocationExport;
use App\Jobs\Tenant\ProcessLocationImport;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantLocationImportExportTest extends TestCase
{
    public function test_import_sample_uses_location_columns(): void
    {
        $response = app(LocationController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();

        $this->assertSame("name,code,email,latitude,longitude,status\n", $csv);
    }

    public function test_export_dispatches_location_queue_job(): void
    {
        Bus::fake();

        $response = app(LocationController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Location export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateLocationExport::class, function (GenerateLocationExport $job): bool {
            return $job->userId === null
                && $job->connection === 'database_tenant'
                && $job->queue === 'tenant'
                && preg_match('/\Alocations_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_location_queue_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(\App\Models\Tenant\User::class);
        $user->shouldReceive('can')->with('create_location')->andReturnTrue();
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
        $file = UploadedFile::fake()->createWithContent(
            'locations.csv',
            "name,code,email,latitude,longitude,status\nHQ,LOC-001,hq@example.com,23.02,72.57,Active\n",
        );
        $request = Request::create('/company-structure/locations/import', 'POST', [], [], ['file' => $file]);

        $response = app(LocationController::class)->import($request);

        $this->assertStringContainsString('Location import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessLocationImport::class, function (ProcessLocationImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords === false
                && str_contains($job->filePath, 'location-imports/');
        });
    }
}
