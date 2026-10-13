<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\LeaveTypeController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Jobs\Tenant\GenerateLeaveTypeExport;
use App\Jobs\Tenant\ProcessLeaveTypeImport;
use App\Models\Tenant\Location;
use App\Support\CsvImportTemplate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantLeaveTypeImportExportTest extends TestCase
{
    public function test_import_sample_uses_leave_type_columns(): void
    {
        $response = app(LeaveTypeController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();

        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(['code', 'description', 'location', 'status'], $rows[0]);
        $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]));
        $this->assertCount(4, $rows[1]);
    }

    public function test_import_resolves_location_by_name_or_code(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->softDeletes();
        });

        $locationId = DB::table('locations')->insertGetId([
            'name' => 'Head Office',
            'code' => 'HO-1',
        ]);
        $job = new ProcessLeaveTypeImport(1, 'leave-type-imports/test.csv');
        $resolveLocation = new ReflectionMethod($job, 'resolveLocationValue');

        $this->assertSame($locationId, $resolveLocation->invoke($job, 'Head Office')?->id);
        $this->assertSame($locationId, $resolveLocation->invoke($job, 'HO-1')?->id);
        $this->assertNull($resolveLocation->invoke($job, 'Unknown'));
    }

    public function test_export_dispatches_leave_type_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(LeaveTypeController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Leave Type export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateLeaveTypeExport::class, function (GenerateLeaveTypeExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Aleave_types_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_leave_type_export_notification_preview_reads_generated_file(): void
    {
        Storage::fake('local');
        $fileName = 'leave_types_20261013_115000.csv';
        Storage::disk('local')->put(
            'leave-type-exports/' . $fileName,
            "code,description,location,status\nSL,Sick Leave,HO-1,Active\n",
        );

        $readExportPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');
        [$preview, $error] = $readExportPreview->invoke(
            app(NotificationController::class),
            '/employee-structure/leave-types/export/download/' . $fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['code', 'description', 'location', 'status'], $preview['headers']);
        $this->assertSame([['SL', 'Sick Leave', 'HO-1', 'Active']], $preview['rows']);
    }

    public function test_import_dispatches_leave_type_queue_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(\App\Models\Tenant\User::class);
        $user->shouldReceive('can')->with('create_leavetype')->andReturnTrue();
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
        $file = UploadedFile::fake()->createWithContent(
            'leave_types_import_sample.csv',
            "code,description,location,status\nSL,Sick Leave,HO-1,Active\n",
        );
        $request = Request::create('/employee-structure/leave-types/import', 'POST', [], [], ['file' => $file]);

        $response = app(LeaveTypeController::class)->import($request);

        $this->assertStringContainsString('Leave Type import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessLeaveTypeImport::class, function (ProcessLeaveTypeImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords === false
                && str_contains($job->filePath, 'leave-type-imports/');
        });
    }
}
