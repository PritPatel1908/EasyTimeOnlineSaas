<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ApprovalProcessor;
use App\Http\Controllers\Tenant\PendingApprovalController;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Jobs\Tenant\CancelApprovedRequest;
use App\Jobs\Tenant\SendManualPunchApprovalEmails;
use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\ApprovedApplicationEffectSnapshot;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Services\Tenant\ApprovedApplicationEffectSnapshotService;
use App\Services\Tenant\ApprovedRequestCancellationService;
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
            Bus::assertDispatched(SendManualPunchApprovalEmails::class, static fn (SendManualPunchApprovalEmails $job): bool => (
                $job->applicationId === 9
                && $job->approvalStatusDetailId === 201
                && $job->actorId === 10
            ));

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

    public function test_approved_page_only_lists_fully_approved_requests_assigned_to_the_user(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_statuses')->where('id', 100)->update(['approval_status' => 'approved']);
            DB::table('approval_status_details')->whereIn('id', [201, 202])->update([
                'approval_status' => 'approved',
            ]);
            DB::table('manual_punch_applications')->insert([
                'id' => 10,
                'punch_type' => '1',
                'punch_date' => '2026-10-25',
                'punch_time' => '10:00:00',
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 101,
                'approvable_id' => 10,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 1,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 203,
                'level' => 1,
                'approval_status_id' => 101,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);
            $this->mockTenantUser(10);

            $view = app(PendingApprovalController::class)->approved();

            $this->assertSame([201], $view->getData()['approvals']->pluck('approval.id')->all());
            $this->assertSame('Approved Requests', $view->getData()['pageTitle']);
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_cancelling_a_fully_approved_request_records_reason_and_cancels_every_flow(): void
    {
            $this->createApprovalTables();

            try {
                $this->createApplicationWithFlow();
                DB::table('manual_punch_application_users')->insert([
                    'manual_punch_application_id' => 9,
                    'user_id' => 10,
                ]);
                DB::table('approval_statuses')->where('id', 100)->update(['approval_status' => 'approved']);
                DB::table('approval_status_details')->whereIn('id', [201, 202])->update([
                    'approval_status' => 'approved',
                ]);
                ApprovedApplicationEffectSnapshot::query()->create([
                    'approvable_type' => ManualPunchApplication::class,
                    'approvable_id' => 9,
                    'snapshot' => [
                        'user_ids' => [10],
                        'start_date' => '2026-10-24',
                        'end_date' => '2026-10-24',
                        'tables' => [],
                        'log_snapshots' => [],
                        'user_values' => [],
                        'user_shifts' => null,
                        'application_status' => null,
                    ],
                ]);

                Bus::fake();
                $cancellations = app(ApprovedRequestCancellationService::class);
                $approval = ApprovalStatusDetails::query()->findOrFail(201);
                $cancellations->requestCancellation(
                    $approval,
                    10,
                    'Duplicate request',
                );
                Bus::assertDispatched(CancelApprovedRequest::class, static fn (CancelApprovedRequest $job): bool => (
                    $job->approvalStatusDetailId === 201
                ));

                (new CancelApprovedRequest(201))->handle($cancellations);

                $this->assertDatabaseHas('approved_application_effect_snapshots', [
                    'approvable_type' => ManualPunchApplication::class,
                    'approvable_id' => 9,
                    'cancellation_status' => 'completed',
                    'cancelled_by' => 10,
                    'cancellation_reason' => 'Duplicate request',
                ]);

                $this->assertDatabaseHas('approval_statuses', [
                    'id' => 100,
                    'approval_status' => 'cancelled',
                ]);
                $this->assertDatabaseHas('approval_status_details', [
                    'id' => 201,
                    'approval_status' => 'cancelled',
                ]);
                $this->assertDatabaseHas('approval_status_details', [
                    'id' => 202,
                    'approval_status' => 'cancelled',
                ]);
            } finally {
                $this->dropApprovalTables();
            }
    }

    public function test_shift_change_snapshot_restore_regenerates_schedule_and_pivot_ids(): void
    {
        Schema::dropIfExists('shift_musters');
        Schema::dropIfExists('user_shifts');
        Schema::create('shift_musters', function ($table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_locked')->default(false);
            $table->string('shift')->nullable();
        });
        Schema::create('user_shifts', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id');
        });

        try {
            DB::table('shift_musters')->insert([
                'id' => 1,
                'date' => '2026-11-14',
                'user_id' => 2,
                'is_locked' => false,
                'shift' => '[3]',
            ]);
            DB::table('user_shifts')->insert([
                'id' => 1,
                'user_id' => 2,
                'shift_id' => 3,
            ]);

            $snapshot = new ApprovedApplicationEffectSnapshot([
                'snapshot' => [
                    'user_ids' => [2],
                    'start_date' => '2026-11-14',
                    'end_date' => '2026-11-14',
                    'tables' => [[
                        'table' => 'shift_musters',
                        'user_ids' => [2],
                        'start_date' => '2026-11-14',
                        'end_date' => '2026-11-14',
                        'rows' => [[
                            'id' => 9,
                            'date' => '2026-11-14',
                            'user_id' => 2,
                            'is_locked' => false,
                            'shift' => '[1,2]',
                        ]],
                    ]],
                    'log_snapshots' => [],
                    'user_values' => [],
                    'user_shifts' => [[
                        'id' => 20,
                        'user_id' => 2,
                        'shift_id' => 1,
                    ], [
                        'id' => 21,
                        'user_id' => 2,
                        'shift_id' => 2,
                    ]],
                    'application_status' => null,
                ],
            ]);

            app(ApprovedApplicationEffectSnapshotService::class)->restore(
                new ShiftChangeApplication(),
                $snapshot,
            );

            $this->assertDatabaseHas('shift_musters', [
                'date' => '2026-11-14',
                'user_id' => 2,
                'shift' => '[1,2]',
            ]);
            $this->assertNotSame(9, (int) DB::table('shift_musters')->value('id'));
            $this->assertSame([1, 2], DB::table('user_shifts')
                ->where('user_id', 2)
                ->orderBy('shift_id')
                ->pluck('shift_id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all());
            $this->assertNotSame(
                [20, 21],
                DB::table('user_shifts')->where('user_id', 2)->orderBy('shift_id')->pluck('id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all(),
            );
        } finally {
            Schema::dropIfExists('shift_musters');
            Schema::dropIfExists('user_shifts');
        }
    }

    public function test_rejected_page_only_lists_rejected_requests_assigned_to_the_user(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_statuses')->where('id', 100)->update(['approval_status' => 'rejected']);
            DB::table('approval_status_details')->where('id', 201)->update([
                'approval_status' => 'rejected',
                'rejection_reason' => 'Insufficient details.',
            ]);
            DB::table('approval_status_details')->where('id', 202)->update(['approval_status' => 'cancelled']);
            $this->mockTenantUser(10);

            $view = app(PendingApprovalController::class)->rejected();

            $this->assertSame([201], $view->getData()['approvals']->pluck('approval.id')->all());
            $this->assertSame('Rejected Requests', $view->getData()['pageTitle']);
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_cancelled_page_only_lists_cancelled_requests_assigned_to_the_user(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_statuses')->where('id', 100)->update([
                'approval_status' => 'cancelled',
            ]);
            $this->mockTenantUser(10);

            $levelOneView = app(PendingApprovalController::class)->cancelled();
            $this->assertSame([201], $levelOneView->getData()['approvals']->pluck('approval.id')->all());

            $this->mockTenantUser(11);
            $levelTwoView = app(PendingApprovalController::class)->cancelled();
            $this->assertSame([202], $levelTwoView->getData()['approvals']->pluck('approval.id')->all());
            $this->assertSame('Cancelled Requests', $levelTwoView->getData()['pageTitle']);
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_shift_change_application_reports_cancelled_status(): void
    {
        $application = new ShiftChangeApplication();
        $application->setRelation('approval_statuses', collect([
            new ApprovalStatus(['approval_status' => 'cancelled']),
        ]));

        $this->assertSame('cancelled', $application->overallApprovalStatus());
    }

    public function test_cancelled_request_detail_includes_its_cancellation_reason(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_statuses')->where('id', 100)->update([
                'approval_status' => 'cancelled',
            ]);
            ApprovedApplicationEffectSnapshot::query()->create([
                'approvable_type' => ManualPunchApplication::class,
                'approvable_id' => 9,
                'snapshot' => [
                    'user_ids' => [10],
                    'start_date' => '2026-10-24',
                    'end_date' => '2026-10-24',
                    'tables' => [],
                    'log_snapshots' => [],
                    'user_values' => [],
                    'user_shifts' => null,
                    'application_status' => null,
                ],
                'cancellation_status' => 'completed',
                'cancellation_reason' => 'Schedule conflict',
                'cancelled_at' => now(),
            ]);

            $this->mockTenantUser(10);
            $route = Mockery::mock(\Illuminate\Routing\Route::class);
            $route->shouldReceive('parameter')->with('detail', null)->andReturn(201);
            $request = Request::create('/requests/cancelled/201');
            $request->setRouteResolver(static fn () => $route);
            $view = app(PendingApprovalController::class)->showCancelled($request);

            $this->assertSame('Schedule conflict', $view->getData()['cancellationReason']);
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_upcoming_page_only_lists_the_level_immediately_after_the_active_level(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_flow_details')->insert(['id' => 22, 'level' => 3, 'approval_flow_id' => 1]);
            DB::table('users')->insert(['id' => 12, 'name' => 'Level Three', 'code' => 'A3']);
            DB::table('approval_flow_detail_user')->insert(['approval_flow_detail_id' => 22, 'user_id' => 12]);
            DB::table('approval_status_details')->insert([
                'id' => 203,
                'level' => 3,
                'approval_status_id' => 100,
                'approval_flow_detail_id' => 22,
                'approval_status' => 'pending',
            ]);

            $this->mockTenantUser(11);
            $view = app(PendingApprovalController::class)->upcoming();

            $this->assertSame([202], $view->getData()['approvals']->pluck('approval.id')->all());
        } finally {
            $this->dropApprovalTables();
        }
    }

    public function test_upcoming_page_does_not_skip_an_incomplete_level(): void
    {
        $this->createApprovalTables();

        try {
            $this->createApplicationWithFlow();
            DB::table('approval_flow_details')->insert(['id' => 22, 'level' => 3, 'approval_flow_id' => 1]);
            DB::table('users')->insert(['id' => 12, 'name' => 'Level Three', 'code' => 'A3']);
            DB::table('approval_flow_detail_user')->insert(['approval_flow_detail_id' => 22, 'user_id' => 12]);
            DB::table('approval_status_details')->insert([
                'id' => 203,
                'level' => 3,
                'approval_status_id' => 100,
                'approval_flow_detail_id' => 22,
                'approval_status' => 'pending',
            ]);
            $this->mockTenantUser(12);

            $view = app(PendingApprovalController::class)->upcoming();

            $this->assertSame([], $view->getData()['approvals']->pluck('approval.id')->all());
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
            Bus::fake();
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
            Bus::assertDispatched(SendManualPunchApprovalEmails::class, static fn (SendManualPunchApprovalEmails $job): bool => (
                $job->applicationId === 9
                && $job->approvalStatusDetailId === 201
                && $job->actorId === 10
                && $job->decision === 'rejected'
            ));
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
        $this->assertTrue(Route::has('tenant.requests.approved.index'));
        $this->assertTrue(Route::has('tenant.requests.approved.show'));
        $this->assertTrue(Route::has('tenant.requests.rejected.index'));
        $this->assertTrue(Route::has('tenant.requests.rejected.show'));
        $this->assertTrue(Route::has('tenant.requests.cancelled.index'));
        $this->assertTrue(Route::has('tenant.requests.cancelled.show'));
        $this->assertTrue(Route::has('tenant.requests.upcoming.index'));
        $this->assertTrue(Route::has('tenant.requests.upcoming.show'));
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
        Schema::create('approved_application_effect_snapshots', function ($table): void {
            $table->id();
            $table->morphs('approvable');
            $table->json('snapshot');
            $table->string('cancellation_status')->nullable();
            $table->unsignedBigInteger('cancellation_requested_by')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->text('cancellation_error')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
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
        Schema::dropIfExists('approved_application_effect_snapshots');
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
