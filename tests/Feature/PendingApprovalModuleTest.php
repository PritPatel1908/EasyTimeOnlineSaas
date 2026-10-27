<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ApprovalProcessor;
use App\Http\Controllers\Tenant\PendingApprovalController;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChangeApplication;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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

    public function test_pending_shift_change_requests_show_their_linked_employees(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            Schema::create('shift_change_applications', function ($table): void {
                $table->id();
                $table->string('shift_type')->nullable();
                $table->unsignedBigInteger('shift_rotation_id')->nullable();
                $table->date('from_date')->nullable();
                $table->boolean('is_forever')->default(false);
                $table->date('to_date')->nullable();
                $table->unsignedInteger('status')->default(1);
                $table->text('reason')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
            Schema::create('shift_change_application_users', function ($table): void {
                $table->id();
                $table->unsignedBigInteger('shift_change_application_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
            });
            DB::table('users')->insert([
                'id' => 3,
                'name' => 'Shift Applicant',
                'code' => 'EMP-3',
            ]);
            DB::table('shift_change_applications')->insert([
                'id' => 3,
                'shift_type' => 'rotational',
                'from_date' => '2026-10-22',
                'is_forever' => true,
                'status' => 1,
            ]);
            DB::table('shift_change_application_users')->insert([
                'shift_change_application_id' => 3,
                'user_id' => 3,
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 102,
                'approvable_id' => 3,
                'approvable_type' => ShiftChangeApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 1,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 205,
                'level' => 1,
                'approval_status_id' => 102,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);

            $this->mockTenantUser(10);
            $view = app(PendingApprovalController::class)->index();
            $row = $view->getData()['approvals']->first(
                static fn (array $row): bool => (int) $row['approval']->id === 205,
            );

            $this->assertNotNull($row);
            $this->assertSame('Shift Applicant (EMP-3)', $row['employees']);
        } finally {
            Schema::dropIfExists('shift_change_application_users');
            Schema::dropIfExists('shift_change_applications');
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

    public function test_week_off_approval_shows_readable_weekly_details(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            Schema::create('week_off_change_applications', function ($table): void {
                $table->id();
                $table->string('reason')->nullable();
                $table->date('from_date')->nullable();
                $table->boolean('is_forever')->default(false);
                $table->date('to_date')->nullable();
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
            DB::table('users')->insert(['id' => 3, 'name' => 'Week-off Applicant', 'code' => 'EMP-3']);
            DB::table('week_off_change_applications')->insert([
                'id' => 3,
                'reason' => 'Coverage change',
                'from_date' => '2026-11-01',
                'is_forever' => true,
            ]);
            DB::table('week_off_change_application_details')->insert([
                'week_off_change_application_id' => 3,
                'week_days' => 6,
                'wo_type' => 1,
                'first_week' => true,
                'third_week' => true,
            ]);
            DB::table('week_off_change_application_users')->insert([
                'week_off_change_application_id' => 3,
                'user_id' => 3,
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 102,
                'approvable_id' => 3,
                'approvable_type' => WeekOffChangeApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 1,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 205,
                'level' => 1,
                'approval_status_id' => 102,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);

            $this->mockTenantUser(10);
            $route = Mockery::mock(\Illuminate\Routing\Route::class);
            $route->shouldReceive('parameter')->with('detail', null)->andReturn(205);
            $request = Request::create('/requests/pending/205');
            $request->setRouteResolver(static fn () => $route);
            $view = app(PendingApprovalController::class)->show($request);

            $this->assertSame([
                ['day' => 'Saturday', 'type' => 'Full Day', 'weeks' => '1st Week, 3rd Week'],
            ], $view->getData()['weekOffDetails']);
            $this->assertArrayNotHasKey('Week-off Details', $view->getData()['relatedDetails']);
        } finally {
            Schema::dropIfExists('week_off_change_application_users');
            Schema::dropIfExists('week_off_change_application_details');
            Schema::dropIfExists('week_off_change_applications');
            $this->dropApprovalTables();
        }
    }

    public function test_pending_approval_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('tenant.requests.pending.index'));
        $this->assertTrue(Route::has('tenant.requests.pending.show'));
        $this->assertTrue(Route::has('tenant.requests.pending.decision'));
        $this->assertTrue(Route::has('tenant.requests.pending.bulk-decision'));
    }

    public function test_bulk_decisions_dispatch_one_job_per_selected_request(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('manual_punch_applications')->insert([
                'id' => 10,
                'punch_type' => '1',
                'punch_date' => '2026-10-25',
                'punch_time' => '10:00:00',
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 110,
                'approvable_id' => 10,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 1,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 204,
                'level' => 1,
                'approval_status_id' => 110,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);

            Bus::fake();
            $this->mockTenantUser(10);
            URL::defaults(['tenant' => 'sample']);
            $controller = app(PendingApprovalController::class);

            $approveRequest = Request::create('/requests/pending/bulk-decision', 'POST', [
                'approval_details' => [201],
                'decision' => 'approved',
            ]);
            $approveRequest->setRouteResolver(static fn () => new class
            {
                public function parameter(string $key): ?string
                {
                    return $key === 'tenant' ? 'sample' : null;
                }
            });
            $controller->bulkDecide($approveRequest);

            $rejectRequest = Request::create('/requests/pending/bulk-decision', 'POST', [
                'approval_details' => [204],
                'decision' => 'rejected',
                'rejection_reason' => 'Incorrect details.',
            ]);
            $rejectRequest->setRouteResolver(static fn () => new class
            {
                public function parameter(string $key): ?string
                {
                    return $key === 'tenant' ? 'sample' : null;
                }
            });
            $controller->bulkDecide($rejectRequest);

            $this->assertDatabaseHas('approval_status_details', [
                'id' => 201,
                'approval_status' => 'approved',
            ]);
            $this->assertDatabaseHas('approval_status_details', [
                'id' => 204,
                'approval_status' => 'rejected',
                'rejection_reason' => 'Incorrect details.',
            ]);
            Bus::assertNotDispatched(FinalizeApprovedApplication::class);
        } finally {
            $this->dropApprovalTables();
        }
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
        $permissions = Mockery::mock(BelongsToMany::class);
        $permissions->shouldReceive('where')->andReturnSelf();
        $permissions->shouldReceive('whereIn')->andReturnSelf();
        $permissions->shouldReceive('exists')->andReturn(true);
        $user->shouldReceive('permissions')->andReturn($permissions);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('hasUser')->andReturn(false);
        Auth::shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('id')->andReturn($id);
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
