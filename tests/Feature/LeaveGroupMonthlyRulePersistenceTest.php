<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\LeaveGroupController;
use App\Http\Requests\Tenant\UpdateLeaveGroupRequest;
use App\Models\Tenant\LeaveGroup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class LeaveGroupMonthlyRulePersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('grade_wise_leaves', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('financial_year_id')->nullable();
            $table->boolean('is_monthly')->default(true);
            $table->unsignedTinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grade_wise_leave_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->json('leave_types_not_to_be_clubbed')->nullable();
            $table->json('include_leaves')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('grade_wise_monthly_leave_details');
        Schema::dropIfExists('grade_wise_leaves');

        parent::tearDown();
    }

    public function test_new_monthly_rule_is_not_removed_during_existing_rule_cleanup(): void
    {
        $leaveGroup = LeaveGroup::query()->create([
            'name' => 'Test Leave Group',
            'financial_year_id' => null,
            'is_monthly' => true,
            'status' => 1,
        ]);
        $existingRule = $leaveGroup->grade_wise_monthly_leave_details()->create([
            'leave_type_id' => 1,
        ]);
        $request = Mockery::mock(UpdateLeaveGroupRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'name' => 'Test Leave Group',
            'financial_year_id' => null,
            'is_monthly' => true,
            'status' => 1,
            'monthly_rules_present' => '1',
            'monthly_rules' => [
                ['id' => $existingRule->id, 'leave_type_id' => 1],
                ['leave_type_id' => 2],
            ],
        ]);
        $request->shouldReceive('route')->with('leave_group')->andReturn($leaveGroup->id);

        app(LeaveGroupController::class)->update($request);

        $this->assertDatabaseHas('grade_wise_monthly_leave_details', [
            'grade_wise_leave_id' => $leaveGroup->id,
            'leave_type_id' => 1,
        ]);
        $this->assertDatabaseHas('grade_wise_monthly_leave_details', [
            'grade_wise_leave_id' => $leaveGroup->id,
            'leave_type_id' => 2,
        ]);
        $this->assertSame(2, $leaveGroup->grade_wise_monthly_leave_details()->count());
    }
}
