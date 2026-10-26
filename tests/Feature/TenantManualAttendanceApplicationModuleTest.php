<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\NotificationController;
use App\Jobs\Tenant\ApproveUserManualAttendance;
use App\Jobs\Tenant\CreateManualAttendanceApplicationApprovals;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Jobs\Tenant\ProcessManualAttendanceApplicationPunches;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Services\Tenant\ManualAttendanceApplicationWorkflow;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use Tests\TestCase;

class TenantManualAttendanceApplicationModuleTest extends TestCase
{
    public function test_workflow_creates_application_and_approval_status_records_for_assigned_flows(): void
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
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'manual_attendance_approval_flow_id' => 10],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'manual_attendance_approval_flow_id' => 11],
            ]);

            Bus::fake();
            $workflow = app(ManualAttendanceApplicationWorkflow::class);
            $application = $workflow->create([
                'in_time' => '2026-10-20 09:00:00',
                'out_time' => '2026-10-20 18:00:00',
                'reason' => 'Attendance correction',
            ], [101, 102], 101);

            $applicationId = (int) $application->id;
            $this->assertDatabaseHas('manual_attendance_applications', [
                'id' => $applicationId,
                'in_time' => '2026-10-20 09:00:00',
                'out_time' => '2026-10-20 18:00:00',
                'reason' => 'Attendance correction',
            ]);
            $this->assertDatabaseHas('manual_attendance_application_users', [
                'manual_attendance_application_id' => $applicationId,
                'user_id' => 101,
            ]);
            $this->assertDatabaseHas('manual_attendance_application_users', [
                'manual_attendance_application_id' => $applicationId,
                'user_id' => 102,
            ]);
            Bus::assertDispatched(CreateManualAttendanceApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === $applicationId
                && $job->approvalFlowIds === [10, 11]
                && $job->actorId === 101
            ));

            (new CreateManualAttendanceApplicationApprovals($applicationId, [10, 11], 101))->handle();

            $this->assertSame(2, DB::table('approval_statuses')
                ->where('approvable_id', $applicationId)
                ->where('approvable_type', ManualAttendanceApplication::class)
                ->count());
            $this->assertSame(3, DB::table('approval_status_details')
                ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
                ->where('approval_statuses.approvable_id', $applicationId)
                ->where('approval_statuses.approvable_type', ManualAttendanceApplication::class)
                ->count());
            $this->assertDatabaseHas('approval_status_details', [
                'approval_status_id' => DB::table('approval_statuses')
                    ->where('approvable_id', $applicationId)
                    ->where('approval_flow_id', 10)
                    ->value('id'),
                'level' => 1,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
                'created_by' => 101,
                'updated_by' => 101,
            ]);
            $this->assertSame(
                'pending',
                ManualAttendanceApplication::query()->with('approval_statuses')->findOrFail($applicationId)->overallApprovalStatus(),
            );

            $workflow->update($application, [
                'in_time' => '2026-10-21 08:30:00',
                'out_time' => '2026-10-21 17:30:00',
                'reason' => 'Updated correction',
            ], [102], 102);
            (new CreateManualAttendanceApplicationApprovals($applicationId, [11], 102))->handle();

            $this->assertDatabaseHas('manual_attendance_applications', [
                'id' => $applicationId,
                'in_time' => '2026-10-21 08:30:00',
                'reason' => 'Updated correction',
            ]);
            $this->assertDatabaseMissing('manual_attendance_application_users', [
                'manual_attendance_application_id' => $applicationId,
                'user_id' => 101,
            ]);
            $this->assertSame(1, DB::table('approval_statuses')
                ->where('approvable_id', $applicationId)
                ->where('approvable_type', ManualAttendanceApplication::class)
                ->count());
            $this->assertSame(1, DB::table('approval_status_details')
                ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
                ->where('approval_statuses.approvable_id', $applicationId)
                ->where('approval_statuses.approvable_type', ManualAttendanceApplication::class)
                ->whereNull('approval_status_details.deleted_at')
                ->count());
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_application_model_declares_target_tables_and_export_fields(): void
    {
        $application = new ManualAttendanceApplication;

        $this->assertSame('manual_attendance_applications', $application->getTable());
        $this->assertSame('datetime', $application->getCasts()['in_time']);
        $this->assertSame([
            'employee_codes',
            'in_time',
            'out_time',
            'reason',
            'approval_status',
        ], ManualAttendanceApplication::IMPORT_EXPORT_COLUMNS);
        $this->assertTrue(method_exists($application, 'Users'));
        $this->assertTrue(method_exists($application, 'approval_statuses'));
        $this->assertNotContains('shift_rotation_id', $application->getFillable());
        $this->assertNotContains('shift_type', $application->getFillable());
        $this->assertNotContains('shift_id', $application->getFillable());
        $this->assertFalse(method_exists($application, 'shift'));
        $this->assertFalse(method_exists($application, 'shift_rotation'));
    }

    public function test_final_approval_queues_manual_punch_processing_instead_of_creating_attendance(): void
    {
        Schema::create('manual_attendance_applications', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('in_time');
            $table->dateTime('out_time');
            $table->tinyInteger('status')->default(1);
            $table->text('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_statuses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('approvable_id');
            $table->string('approvable_type');
            $table->string('approval_status');
            $table->unsignedBigInteger('approval_flow_id')->nullable();
            $table->timestamps();
        });

        try {
            $applicationId = (int) DB::table('manual_attendance_applications')->insertGetId([
                'in_time' => '2026-10-20 09:00:00',
                'out_time' => '2026-10-20 18:00:00',
                'status' => 1,
            ]);
            DB::table('approval_statuses')->insert([
                'approvable_id' => $applicationId,
                'approvable_type' => ManualAttendanceApplication::class,
                'approval_status' => 'approved',
            ]);

            Bus::fake();
            (new FinalizeApprovedApplication(ManualAttendanceApplication::class, $applicationId))->handle();

            Bus::assertDispatched(ProcessManualAttendanceApplicationPunches::class, static fn (ProcessManualAttendanceApplicationPunches $job): bool => (
                $job->manualAttendanceApplicationId === $applicationId
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing'
            ));
            Bus::assertNotDispatched(ApproveUserManualAttendance::class);
        } finally {
            Schema::dropIfExists('approval_statuses');
            Schema::dropIfExists('manual_attendance_applications');
        }
    }

    public function test_notification_preview_reads_manual_attendance_application_export(): void
    {
        Storage::fake('local');
        $fileName = 'manual_attendance_applications_20261024_110400.csv';
        Storage::disk('local')->put(
            'manual-attendance-application-exports/'.$fileName,
            "employee_codes,in_time,approval_status\nE001,2026-10-24 09:00:00,pending\n",
        );
        $readPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/manual-attendance-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'in_time', 'approval_status'], $preview['headers']);
        $this->assertSame([['E001', '2026-10-24 09:00:00', 'pending']], $preview['rows']);
    }

    private function createWorkflowTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('manual_attendance_approval_flow_id')->nullable();
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
        Schema::create('manual_attendance_applications', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('in_time');
            $table->dateTime('out_time');
            $table->unsignedTinyInteger('status')->default(1);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('manual_attendance_application_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('manual_attendance_application_id');
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
        Schema::dropIfExists('manual_attendance_application_users');
        Schema::dropIfExists('manual_attendance_applications');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
