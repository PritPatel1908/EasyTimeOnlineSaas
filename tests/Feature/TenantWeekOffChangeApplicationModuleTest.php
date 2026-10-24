<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\WOTypeEnumn;
use App\Jobs\Tenant\CreateWeekOffChangeApplicationApprovals;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Services\Tenant\WeekOffChangeApplicationWorkflow;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantWeekOffChangeApplicationModuleTest extends TestCase
{
    public function test_application_creation_queues_and_creates_approval_statuses_and_levels(): void
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
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'week_off_change_approval_flow_id' => 10],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'week_off_change_approval_flow_id' => 11],
            ]);

            Bus::fake();
            $application = app(WeekOffChangeApplicationWorkflow::class)->create([
                'from_date' => '2026-10-24',
                'is_forever' => false,
                'to_date' => '2026-12-31',
                'reason' => 'Change weekly rest days',
                'details' => [[
                    'week_days' => 0,
                    'wo_type' => WOTypeEnumn::FullDay->value,
                    'first_week' => 1,
                    'second_week' => 0,
                    'third_week' => 1,
                    'fourth_week' => 0,
                    'fifth_week' => 0,
                ]],
            ], [101, 102], 101);

            $this->assertDatabaseHas('week_off_change_applications', [
                'id' => $application->id,
                'reason' => 'Change weekly rest days',
            ]);
            $this->assertDatabaseHas('week_off_change_application_users', [
                'week_off_change_application_id' => $application->id,
                'user_id' => 101,
            ]);
            $this->assertDatabaseHas('week_off_change_application_details', [
                'week_off_change_application_id' => $application->id,
                'week_days' => 0,
                'wo_type' => WOTypeEnumn::FullDay->value,
                'first_week' => 1,
            ]);
            Bus::assertDispatched(CreateWeekOffChangeApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === (int) $application->id
                && $job->approvalFlowIds === [10, 11]
                && $job->actorId === 101
            ));

            (new CreateWeekOffChangeApplicationApprovals((int) $application->id, [10, 11], 101))->handle();

            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $application->id,
                'approvable_type' => WeekOffChangeApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 10,
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $application->id,
                'approvable_type' => WeekOffChangeApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 11,
            ]);
            $this->assertSame(3, DB::table('approval_status_details')->count());
            $this->assertSame('pending', $application->fresh()->overallApprovalStatus());

            app(WeekOffChangeApplicationWorkflow::class)->update($application, [
                'from_date' => '2026-11-01 00:00:00',
                'is_forever' => true,
                'to_date' => null,
                'reason' => 'Updated weekly rest days',
                'details' => [[
                    'week_days' => 2,
                    'wo_type' => WOTypeEnumn::SecondHalf->value,
                    'first_week' => 0,
                    'second_week' => 1,
                    'third_week' => 0,
                    'fourth_week' => 0,
                    'fifth_week' => 0,
                ]],
            ], [102], 102);

            (new CreateWeekOffChangeApplicationApprovals((int) $application->id, [11], 102))->handle();

            $this->assertDatabaseHas('week_off_change_applications', [
                'id' => $application->id,
                'from_date' => '2026-11-01 00:00:00',
                'is_forever' => 1,
                'to_date' => null,
                'reason' => 'Updated weekly rest days',
            ]);
            $this->assertDatabaseHas('week_off_change_application_details', [
                'week_off_change_application_id' => $application->id,
                'week_days' => 2,
                'wo_type' => WOTypeEnumn::SecondHalf->value,
                'second_week' => 1,
            ]);
            $this->assertSame(1, DB::table('week_off_change_application_users')
                ->where('week_off_change_application_id', $application->id)
                ->where('user_id', 102)
                ->count());
            $this->assertSame(0, DB::table('approval_statuses')
                ->where('approvable_id', $application->id)
                ->where('approvable_type', WeekOffChangeApplication::class)
                ->where('approval_flow_id', 10)
                ->whereNull('deleted_at')
                ->count());
            $this->assertSame(1, DB::table('approval_statuses')
                ->where('approvable_id', $application->id)
                ->where('approvable_type', WeekOffChangeApplication::class)
                ->where('approval_flow_id', 11)
                ->whereNull('deleted_at')
                ->count());
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_application_model_exposes_the_application_tables_and_approval_relations(): void
    {
        $application = new WeekOffChangeApplication;

        $this->assertSame('week_off_change_applications', $application->getTable());
        $this->assertSame([
            'employee_codes',
            'from_date',
            'is_forever',
            'to_date',
            'reason',
            'week_days',
            'wo_types',
            'first_week',
            'second_week',
            'third_week',
            'fourth_week',
            'fifth_week',
            'approval_status',
        ], WeekOffChangeApplication::IMPORT_EXPORT_COLUMNS);
        $this->assertTrue(method_exists($application, 'Users'));
        $this->assertTrue(method_exists($application, 'WeekOffChangeDetails'));
        $this->assertTrue(method_exists($application, 'approval_statuses'));
    }

    private function createWorkflowTables(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('week_off_change_approval_flow_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flows', function ($table): void {
            $table->id();
            $table->string('approval_flow_code');
            $table->unsignedTinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flow_details', function ($table): void {
            $table->id();
            $table->unsignedInteger('level')->default(0);
            $table->unsignedBigInteger('approval_flow_id');
            $table->timestamps();
        });
        Schema::create('week_off_change_applications', function ($table): void {
            $table->id();
            $table->string('reason')->nullable();
            $table->date('from_date')->nullable();
            $table->boolean('is_forever')->default(false);
            $table->date('to_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_change_application_details', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_change_application_id');
            $table->integer('week_days')->nullable();
            $table->tinyInteger('wo_type')->nullable();
            $table->boolean('first_week')->default(false);
            $table->boolean('second_week')->default(false);
            $table->boolean('third_week')->default(false);
            $table->boolean('fourth_week')->default(false);
            $table->boolean('fifth_week')->default(false);
            $table->timestamps();
        });
        Schema::create('week_off_change_application_users', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_change_application_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('approval_statuses', function ($table): void {
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
        Schema::create('approval_status_details', function ($table): void {
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
        Schema::dropIfExists('week_off_change_application_users');
        Schema::dropIfExists('week_off_change_application_details');
        Schema::dropIfExists('week_off_change_applications');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
