<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\User;
use App\Services\Tenant\MasterSnapshotService;
use App\Support\CustomReportTableBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantApproverSnapshotTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.approver_snapshot_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'approver_snapshot_test',
        ]);
        DB::purge('approver_snapshot_test');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('user_type')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('approval_flow_id')->nullable();
            $table->unsignedBigInteger('shift_change_approval_flow_id')->nullable();
            $table->unsignedBigInteger('week_off_change_approval_flow_id')->nullable();
            $table->unsignedBigInteger('week_off_swap_approval_flow_id')->nullable();
            $table->unsignedBigInteger('manual_punch_approval_flow_id')->nullable();
            $table->unsignedBigInteger('manual_attendance_approval_flow_id')->nullable();
            $table->unsignedBigInteger('leave_approval_flow_id')->nullable();
            $table->unsignedBigInteger('short_leave_approval_flow_id')->nullable();
            $table->unsignedBigInteger('coff_approval_flow_id')->nullable();
            $table->unsignedBigInteger('od_approval_flow_id')->nullable();
            $table->unsignedBigInteger('overtime_approval_flow_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flows', function (Blueprint $table): void {
            $table->id();
            $table->string('approval_flow_code');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flow_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('approval_flow_id');
            $table->unsignedInteger('level');
            $table->timestamps();
        });
        Schema::create('approval_flow_detail_user', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('master_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('master_type');
            $table->unsignedBigInteger('master_id');
            $table->string('type');
            $table->json('value');
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('for_all')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('approver_snapshot_test');
        DB::purge('approver_snapshot_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_approvers_from_all_assigned_flows_are_snapshotted_and_exported_from_snapshot(): void
    {
        $flow = ApprovalFlow::query()->create(['approval_flow_code' => 'LEAVE']);
        $firstLevel = $flow->approval_flow_details()->create(['level' => 1]);
        $secondLevel = $flow->approval_flow_details()->create(['level' => 2]);
        $firstLevel->users()->sync([20]);
        $secondLevel->users()->sync([21]);
        DB::table('users')->insert([
            ['id' => 10, 'user_type' => 'employee', 'name' => 'Employee', 'leave_approval_flow_id' => $flow->id],
            ['id' => 20, 'user_type' => 'approver', 'name' => 'Approver One', 'leave_approval_flow_id' => null],
            ['id' => 21, 'user_type' => 'approver', 'name' => 'Approver Two', 'leave_approval_flow_id' => null],
        ]);
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 10,
            'user_type' => 'employee',
            'leave_approval_flow_id' => $flow->id,
        ], true);

        app(MasterSnapshotService::class)->captureApproversForEmployee($employee);

        $snapshot = MasterSnapshot::query()->sole();
        $this->assertSame(MasterSnapshot::APPROVERS_SNAPSHOT_TYPE, $snapshot->master_type);
        $this->assertSame([
            'approval_flows' => [[
                'module' => 'Leave',
                'flow_id' => $flow->id,
                'flow_code' => 'LEAVE',
                'levels' => [
                    ['level' => 1, 'approvers' => [['id' => 20, 'name' => 'Approver One', 'code' => '']]],
                    ['level' => 2, 'approvers' => [['id' => 21, 'name' => 'Approver Two', 'code' => '']]],
                ],
            ]],
        ], $snapshot->value);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'approvers'],
                ['field_name' => 'date'],
            ],
            today()->subDays(19)->toDateString(),
            today()->toDateString(),
            'vertical',
            [$employee],
        );

        $this->assertSame([], $table['headers']);
        $this->assertContains(['Leave (LEAVE) - Level 1: Approver One; Level 2: Approver Two'], $table['rows']);
    }

    public function test_flow_approver_changes_create_a_new_employee_snapshot(): void
    {
        $flow = ApprovalFlow::query()->create(['approval_flow_code' => 'LEAVE']);
        $detail = $flow->approval_flow_details()->create(['level' => 1]);
        $detail->users()->sync([20]);
        DB::table('users')->insert([
            ['id' => 10, 'user_type' => 'employee', 'name' => 'Employee', 'leave_approval_flow_id' => $flow->id],
            ['id' => 20, 'user_type' => 'approver', 'name' => 'Approver One', 'leave_approval_flow_id' => null],
            ['id' => 21, 'user_type' => 'approver', 'name' => 'Approver Two', 'leave_approval_flow_id' => null],
        ]);
        $service = app(MasterSnapshotService::class);
        $service->captureApproverSnapshotsForFlow($flow);

        $detail->users()->sync([21]);
        $service->captureApproverSnapshotsForFlow($flow, [10]);

        $snapshots = MasterSnapshot::query()->orderBy('id')->get();
        $this->assertCount(2, $snapshots);
        $this->assertSame('Approver One', $snapshots[0]->value['approval_flows'][0]['levels'][0]['approvers'][0]['name']);
        $this->assertSame(today()->toDateString(), $snapshots[0]->to_date->toDateString());
        $this->assertSame('Approver Two', $snapshots[1]->value['approval_flows'][0]['levels'][0]['approvers'][0]['name']);
    }
}
