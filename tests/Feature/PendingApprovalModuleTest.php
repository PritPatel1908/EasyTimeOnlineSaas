<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ApprovalProcessor;
use App\Http\Controllers\Tenant\PendingApprovalController;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PendingApprovalModuleTest extends TestCase
{
    public function test_pending_page_only_lists_the_assigned_active_level_and_advances_all_levels(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            Bus::fake();
            $this->mockTenantUser(10);

            $view = app(PendingApprovalController::class)->index();
            $this->assertSame([201], $view->getData()['approvals']->pluck('approval.id')->all());

            try {
                ApprovalProcessor::processDecision(202, 11, 'approved', null);
                $this->fail('A later approval level must not be actioned early.');
            } catch (RuntimeException $exception) {
                $this->assertSame('This approval level is not active yet.', $exception->getMessage());
            }

            ApprovalProcessor::processDecision(201, 10, 'approved', null);
            $this->assertDatabaseHas('approval_status_details', [
                'id' => 201,
                'approval_status' => 'approved',
                'user_id' => 10,
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'id' => 100,
                'approval_status' => 'pending',
            ]);
            Bus::assertNotDispatched(FinalizeApprovedApplication::class);

            ApprovalProcessor::processDecision(202, 11, 'approved', null);
            $this->assertDatabaseHas('approval_statuses', [
                'id' => 100,
                'approval_status' => 'approved',
            ]);
            Bus::assertDispatched(FinalizeApprovedApplication::class, static fn (FinalizeApprovedApplication $job): bool => (
                $job->approvableType === ManualPunchApplication::class
                && $job->approvableId === 9
            ));
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_rejection_saves_reason_and_cancels_other_pending_flows(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_flows')->insert(['id' => 2, 'approval_flow_code' => 'parallel-flow']);
            DB::table('approval_flow_details')->insert(['id' => 22, 'level' => 1, 'approval_flow_id' => 2]);
            DB::table('approval_flow_detail_user')->insert([
                'approval_flow_detail_id' => 22,
                'user_id' => 10,
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 101,
                'approvable_id' => 9,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 2,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 203,
                'level' => 1,
                'approval_status_id' => 101,
                'approval_flow_detail_id' => 22,
                'approval_status' => 'pending',
            ]);

            ApprovalProcessor::processDecision(201, 10, 'rejected', 'Incorrect request details.');

            $this->assertDatabaseHas('approval_status_details', [
                'id' => 201,
                'approval_status' => 'rejected',
                'rejection_reason' => 'Incorrect request details.',
                'user_id' => 10,
            ]);
            $this->assertDatabaseHas('approval_status_details', [
                'id' => 203,
                'approval_status' => 'cancelled',
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'id' => 100,
                'approval_status' => 'rejected',
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'id' => 101,
                'approval_status' => 'cancelled',
            ]);
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_pending_approval_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('tenant.requests.pending.index'));
        $this->assertTrue(Route::has('tenant.requests.pending.show'));
        $this->assertTrue(Route::has('tenant.requests.pending.decision'));
    }

    private function createApprovalTables(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
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
        Schema::create('approval_flow_detail_user', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('manual_punch_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('manual_punch_id')->nullable();
            $table->string('punch_type');
            $table->date('punch_date');
            $table->time('punch_time');
            $table->string('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('manual_punch_application_users', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('manual_punch_application_id');
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
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function createApplicationWithFlow(): void
    {
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Level One', 'code' => 'A1'],
            ['id' => 11, 'name' => 'Level Two', 'code' => 'A2'],
        ]);
        DB::table('approval_flows')->insert(['id' => 1, 'approval_flow_code' => 'two-level-flow']);
        DB::table('approval_flow_details')->insert([
            ['id' => 20, 'level' => 1, 'approval_flow_id' => 1],
            ['id' => 21, 'level' => 2, 'approval_flow_id' => 1],
        ]);
        DB::table('approval_flow_detail_user')->insert([
            ['approval_flow_detail_id' => 20, 'user_id' => 10],
            ['approval_flow_detail_id' => 21, 'user_id' => 11],
        ]);
        DB::table('manual_punch_applications')->insert([
            'id' => 9,
            'punch_type' => '1',
            'punch_date' => '2026-10-24',
            'punch_time' => '09:00:00',
        ]);
        DB::table('approval_statuses')->insert([
            'id' => 100,
            'approvable_id' => 9,
            'approvable_type' => ManualPunchApplication::class,
            'approval_status' => 'pending',
            'approval_flow_id' => 1,
        ]);
        DB::table('approval_status_details')->insert([
            [
                'id' => 201,
                'level' => 1,
                'approval_status_id' => 100,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ],
            [
                'id' => 202,
                'level' => 2,
                'approval_status_id' => 100,
                'approval_flow_detail_id' => 21,
                'approval_status' => 'pending',
            ],
        ]);
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }

    private function dropApprovalTables(): void
    {
        Schema::dropIfExists('approval_status_details');
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('manual_punch_application_users');
        Schema::dropIfExists('manual_punch_applications');
        Schema::dropIfExists('approval_flow_detail_user');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
