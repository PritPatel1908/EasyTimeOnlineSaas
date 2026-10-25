<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\LeaveApplicationController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Jobs\Tenant\CreateLeaveApplicationApprovals;
use App\Jobs\Tenant\GenerateLeaveApplicationExport;
use App\Jobs\Tenant\ProcessLeaveApplicationImport;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\LeaveApplicationApprovalWorkflow;
use App\Support\CsvImportTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantLeaveApplicationImportExportTest extends TestCase
{
    public function test_leave_count_preview_uses_application_half_day_rules(): void
    {
        $calculateLeaveCount = new ReflectionMethod(LeaveApplicationController::class, 'calculateLeaveCount');
        $controller = app(LeaveApplicationController::class);

        $this->assertSame(0.5, $calculateLeaveCount->invoke($controller, '2026-10-25', '2026-10-25', true, true, false));
        $this->assertSame(1.0, $calculateLeaveCount->invoke($controller, '2026-10-25', '2026-10-25', false, false, false));
        $this->assertSame(2.0, $calculateLeaveCount->invoke($controller, '2026-10-25', '2026-10-27', false, true, true));
        $this->assertSame(1.5, $calculateLeaveCount->invoke($controller, '2026-10-25', '2026-10-27', false, true, false));
        $this->assertSame(0.0, $calculateLeaveCount->invoke($controller, '2026-10-25', '2026-10-25', false, true, true));
    }

    public function test_import_sample_uses_leave_application_columns(): void
    {
        $response = app(LeaveApplicationController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame([
            'employee_codes',
            'leave_type_code',
            'leave_reason',
            'reason_explanation',
            'is_only_half',
            'from_date',
            'is_second_half',
            'application_date',
            'to_date',
            'is_first_half',
            'leave_count',
            'status',
        ], $rows[0]);
        $this->assertTrue(CsvImportTemplate::isHintRow($rows[1]));
        $this->assertCount(12, $rows[1]);
    }

    public function test_export_dispatches_leave_application_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(LeaveApplicationController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Leave application export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateLeaveApplicationExport::class, function (GenerateLeaveApplicationExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Aleave_applications_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_leave_application_queue_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(User::class);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
        $file = UploadedFile::fake()->createWithContent(
            'leave_applications_import.csv',
            "employee_codes,leave_type_code,leave_reason,reason_explanation,is_only_half,from_date,is_second_half,application_date,to_date,is_first_half,leave_count,status\n",
        );
        $request = Request::create('/time-management/leave-applications/import', 'POST', [], [], ['file' => $file]);

        $response = app(LeaveApplicationController::class)->import($request);

        $this->assertStringContainsString('Leave application import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessLeaveApplicationImport::class, function (ProcessLeaveApplicationImport $job): bool {
            return $job->userId === 17
                && str_contains($job->filePath, 'leave-application-imports/');
        });
    }

    public function test_leave_application_approval_workflow_dispatches_approval_job(): void
    {
        Bus::fake();
        $application = new LeaveApplication;
        $application->id = 42;

        app(LeaveApplicationApprovalWorkflow::class)->dispatch($application, [3, 7], 17);

        Bus::assertDispatched(CreateLeaveApplicationApprovals::class, function (CreateLeaveApplicationApprovals $job): bool {
            return $job->applicationId === 42
                && $job->approvalFlowIds === [3, 7]
                && $job->actorId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
    }

    public function test_leave_application_id_comes_from_named_path_parameter_not_tenant_domain(): void
    {
        $route = new \Illuminate\Routing\Route(
            ['GET'],
            '/time-management/leave-applications/{leave_application}/edit',
            [
                'uses' => 'UnusedController@edit',
                'domain' => '{tenant}.saas.test',
            ],
        );
        $request = Request::create(
            'http://indinf.saas.test/time-management/leave-applications/1/edit',
        );
        $route->bind($request);
        $request->setRouteResolver(static fn () => $route);

        $applicationId = new ReflectionMethod(LeaveApplicationController::class, 'applicationId');

        $this->assertSame('indinf', $route->parameter('tenant'));
        $this->assertSame('1', $route->parameter('leave_application'));
        $this->assertSame(1, $applicationId->invoke(app(LeaveApplicationController::class), $request));
    }

    public function test_leave_application_export_notification_preview_reads_generated_file(): void
    {
        Storage::fake('local');
        $fileName = 'leave_applications_20261025_115000.csv';
        Storage::disk('local')->put(
            'leave-application-exports/'.$fileName,
            "employee_codes,leave_type_code,leave_reason,status\nE001,SL,Medical,Active\n",
        );

        $readExportPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');
        [$preview, $error] = $readExportPreview->invoke(
            app(NotificationController::class),
            '/time-management/leave-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'leave_type_code', 'leave_reason', 'status'], $preview['headers']);
        $this->assertSame([['E001', 'SL', 'Medical', 'Active']], $preview['rows']);
    }
}
