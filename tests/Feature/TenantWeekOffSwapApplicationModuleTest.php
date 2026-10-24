<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ApprovalApprove;
use App\Http\Controllers\Tenant\WeekOffSwapApplicationController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Jobs\Tenant\CreateWeekOffSwap;
use App\Jobs\Tenant\CreateWeekOffSwapApplicationApprovals;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Services\Tenant\WeekOffSwapApplicationWorkflow;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Filament\Infolists\Infolist;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class TenantWeekOffSwapApplicationModuleTest extends TestCase
{
    public function test_application_workflow_creates_pivot_and_approval_status_records(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                ['id' => 10, 'approval_flow_code' => 'first-flow', 'status' => 1],
                ['id' => 11, 'approval_flow_code' => 'second-flow', 'status' => 1],
            ]);
            DB::table('approval_flow_details')->insert([
                ['id' => 20, 'level' => 1, 'approval_flow_id' => 10],
                ['id' => 21, 'level' => 2, 'approval_flow_id' => 10],
                ['id' => 22, 'level' => 1, 'approval_flow_id' => 11],
            ]);
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'week_off_swap_approval_flow_id' => 10],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'week_off_swap_approval_flow_id' => 11],
            ]);

            Bus::fake();
            $created = app(WeekOffSwapApplicationWorkflow::class)->create([
                'week_date' => '2026-10-25 00:00:00',
                'swap_date' => '2026-10-26 00:00:00',
                'reason' => 'Personal request',
            ], [101, 102], 101);

            $applicationId = (int) $created->first()->id;
            $this->assertDatabaseHas('week_off_swap_applications', [
                'id' => $applicationId,
                'week_date' => '2026-10-25 00:00:00',
                'swap_date' => '2026-10-26 00:00:00',
                'created_by' => 101,
            ]);
            $this->assertDatabaseHas('week_off_swap_application_users', [
                'week_off_swap_application_id' => $applicationId,
                'user_id' => 101,
            ]);
            $this->assertDatabaseHas('week_off_swap_application_users', [
                'week_off_swap_application_id' => $applicationId,
                'user_id' => 102,
            ]);
            Bus::assertDispatched(CreateWeekOffSwapApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === $applicationId
                && $job->approvalFlowIds === [10, 11]
                && $job->actorId === 101
            ));

            (new CreateWeekOffSwapApplicationApprovals($applicationId, [10, 11], 101))->handle();

            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $applicationId,
                'approvable_type' => WeekOffSwapApplication::class,
                'approval_flow_id' => 10,
                'approval_status' => 'pending',
                'created_by' => 101,
                'updated_by' => 101,
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $applicationId,
                'approvable_type' => WeekOffSwapApplication::class,
                'approval_flow_id' => 11,
                'approval_status' => 'pending',
            ]);
            $this->assertDatabaseCount('approval_status_details', 3);
            $this->assertSame('pending', WeekOffSwapApplication::query()
                ->with('approval_statuses')
                ->findOrFail($applicationId)
                ->overallApprovalStatus());
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_application_model_exposes_import_columns_and_approval_relationships(): void
    {
        $application = new WeekOffSwapApplication;

        $this->assertSame('week_off_swap_applications', $application->getTable());
        $this->assertSame([
            'employee_codes',
            'week_date',
            'swap_date',
            'reason',
            'approval_status',
        ], WeekOffSwapApplication::IMPORT_EXPORT_COLUMNS);
        $this->assertTrue(method_exists($application, 'Users'));
        $this->assertTrue(method_exists($application, 'approval_statuses'));
    }

    public function test_approval_dispatches_week_off_swap_application_for_muster_processing(): void
    {
        Bus::fake();
        $infolist = Mockery::mock(Infolist::class);
        $infolist->shouldReceive('getRecord')->once()->andReturn(new WeekOffSwapApplication);

        ApprovalApprove::infolist($infolist);

        Bus::assertDispatched(CreateWeekOffSwap::class, static fn (CreateWeekOffSwap $job): bool => (
            $job->connection === 'database_tenant'
            && $job->queue === 'processing'
        ));
        $this->assertInstanceOf(CreateWeekOffSwap::class, new CreateWeekOffSwap(new WeekOffSwapApplication));
    }

    public function test_application_form_renders_swap_fields_and_employee_selection(): void
    {
        view()->share('errors', new ViewErrorBag);
        $application = new WeekOffSwapApplication;
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 17,
            'name' => 'Test Employee',
            'code' => 'EMP017',
        ]);
        $application->setRelation('Users', collect([$employee]));

        $html = view('time-management.week-off-swap-applications.partials.form-fields', [
            'weekOffSwapApplication' => $application,
            'users' => collect([$employee]),
            'locations' => collect(),
            'companies' => collect(),
            'departments' => collect(),
            'subDepartments' => collect(),
            'categories' => collect(),
            'subCategories' => collect(),
            'designations' => collect(),
            'grades' => collect(),
            'units' => collect(),
            'busRoutes' => collect(),
        ])->render();

        $this->assertStringContainsString('name="week_date"', $html);
        $this->assertStringContainsString('name="swap_date"', $html);
        $this->assertStringContainsString('name="users[]"', $html);
        $this->assertStringContainsString('<option value="17"', $html);
    }

    public function test_application_import_sample_includes_generated_approval_status_column(): void
    {
        $response = app(WeekOffSwapApplicationController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(WeekOffSwapApplication::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertStringContainsString('ignored on import', $rows[1][4]);
    }

    public function test_notification_export_preview_reads_week_off_swap_application_csv(): void
    {
        Storage::fake('local');
        $fileName = 'week_off_swap_applications_20261024_123456.csv';
        Storage::disk('local')->put(
            'week-off-swap-application-exports/'.$fileName,
            "employee_codes,week_date,swap_date,reason,approval_status\nE001,2026-10-25,2026-10-26,Request,pending\n",
        );

        $readPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');
        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/week-off-swap-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(
            ['employee_codes', 'week_date', 'swap_date', 'reason', 'approval_status'],
            $preview['headers'],
        );
        $this->assertSame(
            [['E001', '2026-10-25', '2026-10-26', 'Request', 'pending']],
            $preview['rows'],
        );
    }

    private function createWorkflowTables(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('week_off_swap_approval_flow_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flows', function (Blueprint $table): void {
            $table->id();
            $table->string('approval_flow_code');
            $table->unsignedTinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flow_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('level')->default(0);
            $table->unsignedBigInteger('approval_flow_id');
            $table->timestamps();
        });
        Schema::create('week_off_swap_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('reason')->nullable();
            $table->date('week_date')->nullable();
            $table->date('swap_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_swap_application_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_swap_application_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('approval_statuses', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->string('approval_status')->default('pending');
            $table->unsignedBigInteger('approval_flow_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_status_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('level')->nullable();
            $table->unsignedBigInteger('approval_status_id');
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->timestamp('timestamp')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('approval_status')->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function dropWorkflowTables(): void
    {
        Schema::dropIfExists('approval_status_details');
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('week_off_swap_application_users');
        Schema::dropIfExists('week_off_swap_applications');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
