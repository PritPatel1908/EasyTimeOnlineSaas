<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ShortLeaveType;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\ShortLeaveApplicationController;
use App\Http\Requests\Tenant\StoreShortLeaveApplicationRequest;
use App\Jobs\Tenant\ApplyApprovedShortLeavePunches;
use App\Jobs\Tenant\ApproveShortLeaveApplication;
use App\Jobs\Tenant\CreateShortLeaveApplicationApprovals;
use App\Jobs\Tenant\ProcessShortLeaveApplicationImport;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\ShortLeaveApplicationWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class TenantShortLeaveApplicationModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'activity_log',
            'approval_status_details',
            'approval_statuses',
            'short_leave_applications',
            'manual_punch_users',
            'manual_punches',
            'approval_flow_details',
            'approval_flows',
            'bus_routes',
            'units',
            'grades',
            'designations',
            'sub_categories',
            'categories',
            'sub_departments',
            'departments',
            'locations',
            'companies',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->json('company_id')->nullable();
            $table->json('location_id')->nullable();
            $table->json('department_id')->nullable();
            $table->json('sub_department_id')->nullable();
            $table->json('category_id')->nullable();
            $table->json('sub_category_id')->nullable();
            $table->json('designation_id')->nullable();
            $table->json('grade_id')->nullable();
            $table->json('unit_id')->nullable();
            $table->json('bus_route_id')->nullable();
            $table->unsignedBigInteger('short_leave_approval_flow_id')->nullable();
            $table->integer('short_leave_minutes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        foreach ([
            'companies', 'locations', 'departments', 'sub_departments', 'categories',
            'sub_categories', 'designations', 'grades', 'units', 'bus_routes',
        ] as $tableName) {
            Schema::create($tableName, function ($table) use ($tableName): void {
                $table->id();
                $table->string('name');
                if ($tableName === 'categories') {
                    $table->unsignedInteger('max_short_leave_minutes_per_month')->nullable();
                    $table->unsignedInteger('max_short_leave_minutes_per_application')->nullable();
                    $table->unsignedInteger('max_occurance_of_short_leave_in_month')->nullable();
                    $table->unsignedInteger('advance_short_leave_application')->nullable();
                }
                if (in_array($tableName, ['companies', 'locations'], true)) {
                    $table->string('code')->nullable();
                }
                $table->softDeletes();
            });
        }
        Schema::create('activity_log', function ($table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('event')->nullable();
            $table->string('description')->nullable();
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
        Schema::create('short_leave_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reason')->nullable();
            $table->date('date')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->integer('minutes')->nullable();
            $table->tinyInteger('short_leave_type')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
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
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('manual_punches', function ($table): void {
            $table->id();
            $table->string('punch_type');
            $table->date('punch_date');
            $table->time('punch_time');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('manual_punch_users', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('manual_punch_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('approval_status_details');
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('short_leave_applications');
        Schema::dropIfExists('manual_punch_users');
        Schema::dropIfExists('manual_punches');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('bus_routes');
        Schema::dropIfExists('units');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sub_departments');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_model_exposes_existing_short_leave_fields_and_csv_columns(): void
    {
        $application = new ShortLeaveApplication;

        $this->assertSame('short_leave_applications', $application->getTable());
        $this->assertSame([
            'employee_code',
            'reason',
            'date',
            'from_time',
            'to_time',
            'minutes',
            'short_leave_type',
            'approval_status',
        ], ShortLeaveApplication::IMPORT_EXPORT_COLUMNS);
        $this->assertTrue(method_exists($application, 'user'));
        $this->assertTrue(method_exists($application, 'approval_statuses'));
        $this->assertSame('Break Between Working Hours', ShortLeaveType::BreakBetweenWorkingHours->getLabel());
    }

    public function test_workflow_creates_application_and_short_leave_approval_levels(): void
    {
        DB::table('approval_flows')->insert([
            'id' => 10,
            'approval_flow_code' => 'short-leave-flow',
            'status' => 1,
        ]);
        DB::table('approval_flow_details')->insert([
            ['id' => 20, 'level' => 1, 'approval_flow_id' => 10],
            ['id' => 21, 'level' => 2, 'approval_flow_id' => 10],
        ]);
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
            'short_leave_approval_flow_id' => 10,
            'short_leave_minutes' => 0,
        ]);

        Bus::fake();
        $application = app(ShortLeaveApplicationWorkflow::class)->create([
            'user_id' => 101,
            'reason' => 'Doctor visit',
            'date' => '2026-10-24',
            'from_time' => '10:00:00',
            'to_time' => '10:30:00',
            'minutes' => 30,
            'short_leave_type' => 3,
            'status' => 0,
        ], 101);

        $this->assertDatabaseHas('short_leave_applications', [
            'id' => $application->id,
            'user_id' => 101,
            'date' => '2026-10-24',
            'minutes' => 30,
            'short_leave_type' => 3,
            'status' => 1,
        ]);
        Bus::assertDispatched(CreateShortLeaveApplicationApprovals::class, static fn ($job): bool => (
            $job->applicationId === (int) $application->id
            && $job->approvalFlowIds === [10]
            && $job->actorId === 101
        ));

        (new CreateShortLeaveApplicationApprovals((int) $application->id, [10], 101))->handle();
        $this->assertDatabaseHas('approval_statuses', [
            'approvable_id' => $application->id,
            'approvable_type' => ShortLeaveApplication::class,
            'approval_status' => 'pending',
            'approval_flow_id' => 10,
        ]);
        $this->assertSame(2, DB::table('approval_status_details')
            ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
            ->where('approval_statuses.approvable_id', $application->id)
            ->where('approval_statuses.approvable_type', ShortLeaveApplication::class)
            ->count());
    }

    public function test_workflow_requires_the_employee_short_leave_approval_flow(): void
    {
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
        ]);

        $this->expectException(ValidationException::class);
        app(ShortLeaveApplicationWorkflow::class)->create([
            'user_id' => 101,
            'date' => '2026-10-24',
            'minutes' => 30,
            'short_leave_type' => 1,
            'status' => 1,
        ], 1);
    }

    public function test_workflow_creates_a_separate_application_for_each_selected_employee(): void
    {
        DB::table('approval_flows')->insert([
            'id' => 10,
            'approval_flow_code' => 'short-leave-flow',
            'status' => 1,
        ]);
        DB::table('approval_flow_details')->insert([
            'id' => 20,
            'level' => 1,
            'approval_flow_id' => 10,
        ]);
        DB::table('users')->insert([
            ['id' => 101, 'name' => 'Employee One', 'short_leave_approval_flow_id' => 10],
            ['id' => 102, 'name' => 'Employee Two', 'short_leave_approval_flow_id' => 10],
        ]);
        Bus::fake();

        $applications = app(ShortLeaveApplicationWorkflow::class)->createForUsers([
            'date' => '2026-10-24',
            'minutes' => 30,
            'short_leave_type' => 1,
            'status' => 1,
        ], [101, 102], 501);

        $this->assertCount(2, $applications);
        $this->assertDatabaseCount('short_leave_applications', 2);
        $this->assertDatabaseHas('short_leave_applications', ['user_id' => 101]);
        $this->assertDatabaseHas('short_leave_applications', ['user_id' => 102]);
        Bus::assertDispatchedTimes(CreateShortLeaveApplicationApprovals::class, 2);
    }

    public function test_controller_resolves_employees_using_organization_filters(): void
    {
        DB::table('users')->insert([
            [
                'id' => 101,
                'name' => 'Employee One',
                'company_id' => '[1]',
                'department_id' => '[5]',
            ],
            [
                'id' => 102,
                'name' => 'Employee Two',
                'company_id' => '[2]',
                'department_id' => '[5]',
            ],
            [
                'id' => 103,
                'name' => 'Employee Three',
                'company_id' => '[1]',
                'department_id' => '[6]',
            ],
        ]);
        $method = new ReflectionMethod(ShortLeaveApplicationController::class, 'resolveUserIds');

        $userIds = $method->invoke(app(ShortLeaveApplicationController::class), [
            'company_ids' => [1],
            'employee_filters' => ['department_id' => [5]],
        ]);

        $this->assertSame([101], $userIds);
    }

    public function test_attendance_scope_includes_active_legacy_and_approved_applications_only(): void
    {
        DB::table('short_leave_applications')->insert([
            ['id' => 1, 'user_id' => 101, 'date' => '2026-10-24', 'status' => 1],
            ['id' => 2, 'user_id' => 101, 'date' => '2026-10-24', 'status' => 1],
            ['id' => 3, 'user_id' => 101, 'date' => '2026-10-24', 'status' => 1],
            ['id' => 4, 'user_id' => 101, 'date' => '2026-10-24', 'status' => 0],
        ]);
        DB::table('approval_statuses')->insert([
            [
                'approvable_id' => 2,
                'approvable_type' => ShortLeaveApplication::class,
                'approval_status' => 'pending',
            ],
            [
                'approvable_id' => 3,
                'approvable_type' => ShortLeaveApplication::class,
                'approval_status' => 'approved',
            ],
        ]);

        $ids = ShortLeaveApplication::query()
            ->applicableToAttendance()
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame([1, 3], $ids);
    }

    public function test_updating_approved_application_reverses_its_previous_minute_credit(): void
    {
        DB::table('approval_flows')->insert([
            'id' => 10,
            'approval_flow_code' => 'short-leave-flow',
            'status' => 1,
        ]);
        DB::table('approval_flow_details')->insert([
            'id' => 20,
            'level' => 1,
            'approval_flow_id' => 10,
        ]);
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
            'short_leave_approval_flow_id' => 10,
            'short_leave_minutes' => 75,
        ]);
        DB::table('short_leave_applications')->insert([
            'id' => 1,
            'user_id' => 101,
            'date' => '2026-10-24',
            'minutes' => 30,
            'short_leave_type' => 1,
            'status' => 1,
        ]);
        DB::table('approval_statuses')->insert([
            'approvable_id' => 1,
            'approvable_type' => ShortLeaveApplication::class,
            'approval_status' => 'approved',
            'approval_flow_id' => 10,
        ]);
        Bus::fake();

        app(ShortLeaveApplicationWorkflow::class)->update(
            ShortLeaveApplication::query()->findOrFail(1),
            [
                'user_id' => 101,
                'date' => '2026-10-25',
                'minutes' => 45,
                'short_leave_type' => 1,
                'status' => 1,
            ],
            101,
        );

        $this->assertDatabaseHas('users', ['id' => 101, 'short_leave_minutes' => 45]);
        $this->assertDatabaseHas('short_leave_applications', [
            'id' => 1,
            'date' => '2026-10-25',
            'minutes' => 45,
        ]);
    }

    public function test_deleting_approved_application_reverses_its_minute_credit(): void
    {
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
            'short_leave_minutes' => 75,
        ]);
        DB::table('short_leave_applications')->insert([
            'id' => 1,
            'user_id' => 101,
            'date' => '2026-10-24',
            'minutes' => 30,
            'short_leave_type' => 1,
            'status' => 1,
        ]);
        DB::table('approval_statuses')->insert([
            'approvable_id' => 1,
            'approvable_type' => ShortLeaveApplication::class,
            'approval_status' => 'approved',
        ]);

        app(ShortLeaveApplicationWorkflow::class)->delete(
            ShortLeaveApplication::query()->findOrFail(1),
        );

        $this->assertDatabaseHas('users', ['id' => 101, 'short_leave_minutes' => 45]);
        $this->assertSoftDeleted('short_leave_applications', ['id' => 1]);
    }

    public function test_approval_job_credits_only_active_approved_applications(): void
    {
        Bus::fake();
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
            'short_leave_minutes' => 10,
        ]);
        DB::table('short_leave_applications')->insert([
            'id' => 1,
            'user_id' => 101,
            'date' => '2026-10-24',
            'from_time' => '10:00:00',
            'to_time' => '10:30:00',
            'minutes' => 30,
            'short_leave_type' => 3,
            'status' => 1,
        ]);
        DB::table('approval_statuses')->insert([
            'approvable_id' => 1,
            'approvable_type' => ShortLeaveApplication::class,
            'approval_status' => 'approved',
        ]);

        $application = ShortLeaveApplication::query()->findOrFail(1);
        (new ApproveShortLeaveApplication($application))->handle();
        $this->assertDatabaseHas('users', ['id' => 101, 'short_leave_minutes' => 40]);
        Bus::assertDispatched(ApplyApprovedShortLeavePunches::class, static fn ($job): bool => $job->applicationId === 1);

        DB::table('short_leave_applications')->where('id', 1)->update(['status' => 0]);
        (new ApproveShortLeaveApplication($application))->handle();
        $this->assertDatabaseHas('users', ['id' => 101, 'short_leave_minutes' => 40]);
    }

    public function test_controller_clone_prefills_an_application_without_creating_a_record(): void
    {
        DB::table('short_leave_applications')->insert([
            'id' => 1,
            'user_id' => 101,
            'reason' => 'Original reason',
            'date' => '2026-10-24',
            'minutes' => 20,
            'short_leave_type' => 1,
            'status' => 1,
        ]);
        view()->share('errors', new ViewErrorBag);
        $route = Mockery::mock();
        $route->shouldReceive('parameter')->with('short_leave_application', null)->andReturn(1);
        $request = Request::create('/');
        $request->setRouteResolver(static fn () => $route);

        $view = app(ShortLeaveApplicationController::class)->clone($request);

        $this->assertSame('time-management.short-leave-applications.add', $view->name());
        $this->assertTrue($view->getData()['isClone']);
        $this->assertSame('Original reason', $view->getData()['application']->reason);
        $this->assertSame(1, DB::table('short_leave_applications')->count());
    }

    public function test_request_rules_include_database_fields_without_client_controlled_approval_status(): void
    {
        $rules = (new StoreShortLeaveApplicationRequest)->rules();

        foreach (['users', 'reason', 'date', 'from_time', 'to_time', 'minutes', 'short_leave_type'] as $field) {
            $this->assertArrayHasKey($field, $rules);
        }
        $this->assertArrayNotHasKey('status', $rules);
        $this->assertArrayNotHasKey('approval_status', $rules);

        $dateCheckRequest = StoreShortLeaveApplicationRequest::create('/', 'POST', [
            'live_date_check' => '1',
            'short_leave_type' => 3,
        ]);
        $this->assertSame(
            ['nullable', 'integer', 'min:1', 'max:1440'],
            $dateCheckRequest->rules()['minutes'],
        );
    }

    public function test_category_short_leave_rules_reject_application_over_per_request_limit(): void
    {
        $this->createCategoryEmployee([
            'max_short_leave_minutes_per_month' => 180,
            'max_short_leave_minutes_per_application' => 90,
            'max_occurance_of_short_leave_in_month' => 2,
            'advance_short_leave_application' => 2,
        ]);

        try {
            app(ShortLeaveApplicationWorkflow::class)->create([
                'user_id' => 101,
                'date' => now()->toDateString(),
                'minutes' => 91,
                'short_leave_type' => 1,
            ], 101);
            $this->fail('The per-application short leave limit should reject the request.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('minutes', $exception->errors());
        }

        try {
            app(ShortLeaveApplicationWorkflow::class)->create([
                'user_id' => 101,
                'date' => now()->toDateString(),
                'minutes' => 90,
                'short_leave_type' => 1,
            ], 101);
            $this->fail('The employee has no approval flow configured.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('user_id', $exception->errors());
        }
    }

    public function test_category_short_leave_rules_reject_monthly_minutes_and_occurrence_overages(): void
    {
        $this->createCategoryEmployee([
            'max_short_leave_minutes_per_month' => 180,
            'max_short_leave_minutes_per_application' => 90,
            'max_occurance_of_short_leave_in_month' => 2,
            'advance_short_leave_application' => 2,
        ]);
        DB::table('short_leave_applications')->insert([
            [
                'user_id' => 101,
                'date' => now()->startOfMonth()->toDateString(),
                'minutes' => 100,
                'short_leave_type' => 1,
                'status' => 1,
            ],
            [
                'user_id' => 101,
                'date' => now()->startOfMonth()->addDays(1)->toDateString(),
                'minutes' => 30,
                'short_leave_type' => 2,
                'status' => 1,
            ],
        ]);

        try {
            app(ShortLeaveApplicationWorkflow::class)->create([
                'user_id' => 101,
                'date' => now()->toDateString(),
                'minutes' => 60,
                'short_leave_type' => 1,
            ], 101);
            $this->fail('The monthly occurrence limit should reject the request.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('date', $exception->errors());
        }

        DB::table('short_leave_applications')->where('id', '>', 0)->delete();
        DB::table('short_leave_applications')->insert([
            'user_id' => 101,
            'date' => now()->toDateString(),
            'minutes' => 130,
            'short_leave_type' => 1,
            'status' => 1,
        ]);

        try {
            app(ShortLeaveApplicationWorkflow::class)->create([
                'user_id' => 101,
                'date' => now()->toDateString(),
                'minutes' => 60,
                'short_leave_type' => 1,
            ], 101);
            $this->fail('The monthly minutes limit should reject the request.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('minutes', $exception->errors());
        }
    }

    public function test_live_validation_reports_monthly_occurrence_limit_without_creating_an_application(): void
    {
        $this->createCategoryEmployee([
            'max_short_leave_minutes_per_month' => 180,
            'max_short_leave_minutes_per_application' => 90,
            'max_occurance_of_short_leave_in_month' => 2,
            'advance_short_leave_application' => 2,
        ]);
        DB::table('short_leave_applications')->insert([
            [
                'user_id' => 101,
                'date' => now()->startOfMonth()->toDateString(),
                'minutes' => 30,
                'short_leave_type' => 1,
                'status' => 1,
            ],
            [
                'user_id' => 101,
                'date' => now()->startOfMonth()->addDay()->toDateString(),
                'minutes' => 30,
                'short_leave_type' => 1,
                'status' => 1,
            ],
        ]);

        $errors = app(ShortLeaveApplicationWorkflow::class)->liveValidationErrors([
            'date' => now()->toDateString(),
            'short_leave_type' => 1,
        ], [101], dateOnly: true);

        $this->assertArrayHasKey('date', $errors);
        $this->assertStringContainsString('maximum of 2 short leave application(s)', $errors['date'][0]);
        $this->assertSame(2, DB::table('short_leave_applications')->count());
    }

    public function test_category_short_leave_rules_reject_dates_beyond_advance_limit(): void
    {
        $this->createCategoryEmployee([
            'max_short_leave_minutes_per_month' => 180,
            'max_short_leave_minutes_per_application' => 90,
            'max_occurance_of_short_leave_in_month' => 2,
            'advance_short_leave_application' => 2,
        ]);

        try {
            app(ShortLeaveApplicationWorkflow::class)->create([
                'user_id' => 101,
                'date' => now()->addDays(3)->toDateString(),
                'minutes' => 30,
                'short_leave_type' => 1,
            ], 101);
            $this->fail('The advance-day limit should reject the request.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('date', $exception->errors());
        }
    }

    public function test_import_parser_accepts_generated_approval_status_column(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'short-leave-import-');
        $this->assertNotFalse($file);

        try {
            file_put_contents(
                $file,
                "employee_code,reason,date,from_time,to_time,minutes,short_leave_type,status,approval_status\nE001,Break,2026-10-24,10:00,10:30,30,3,1,approved\n",
            );
            $job = new ProcessShortLeaveApplicationImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessShortLeaveApplicationImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $file);

            $this->assertSame('approved', $rows[2]['approval_status']);
            $this->assertSame('E001', $rows[2]['employee_code']);
        } finally {
            unlink($file);
        }
    }

    public function test_import_row_derives_break_minutes_from_times(): void
    {
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
        ]);
        $job = new ProcessShortLeaveApplicationImport(1, 'unused');
        $resolveRow = new ReflectionMethod(ProcessShortLeaveApplicationImport::class, 'resolveRow');
        [$payload, $errors] = $resolveRow->invoke($job, [
            'employee_code' => 'E001',
            'reason' => 'Break',
            'date' => '2026-10-24',
            'from_time' => '10:00',
            'to_time' => '10:30',
            'minutes' => '',
            'short_leave_type' => '3',
            'status' => '0',
        ]);

        $this->assertSame([], $errors);
        $this->assertSame(30, $payload['minutes']);
        $this->assertSame('10:00:00', $payload['from_time']);
        $this->assertSame('10:30:00', $payload['to_time']);
        $this->assertArrayNotHasKey('status', $payload);
    }

    public function test_short_leave_form_renders_all_table_input_fields(): void
    {
        view()->share('errors', new ViewErrorBag);
        $employee = new User;
        $employee->setRawAttributes(['id' => 101, 'name' => 'Employee One', 'code' => 'E001']);

        $html = view('time-management.short-leave-applications.partials.form-fields', [
            'application' => null,
            'users' => collect([$employee]),
            'selectedUserIds' => [],
            'companies' => collect(),
            'locations' => collect(),
            'departments' => collect(),
            'subDepartments' => collect(),
            'categories' => collect(),
            'subCategories' => collect(),
            'designations' => collect(),
            'grades' => collect(),
            'units' => collect(),
            'busRoutes' => collect(),
        ])->render();
        foreach (['users[]', 'reason', 'date', 'from_time', 'to_time', 'minutes', 'short_leave_type', 'company_ids[]', 'location_ids[]', 'employee_filters[department_id][]'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $html);
        }
        $this->assertStringNotContainsString('Record Status', $html);
        $this->assertStringNotContainsString('name="status"', $html);
        $this->assertStringContainsString('data-short-leave-feedback-for="date"', $html);
    }

    public function test_notification_controller_previews_short_leave_exports(): void
    {
        Storage::fake('local');
        $fileName = 'short_leave_applications_20261024_123456.csv';
        Storage::disk('local')->put(
            'short-leave-application-exports/'.$fileName,
            "employee_code,date,minutes\nE001,2026-10-24,30\n",
        );

        $method = new ReflectionMethod(NotificationController::class, 'readExportPreview');
        [$preview, $error] = $method->invoke(
            app(NotificationController::class),
            '/short-leave-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_code', 'date', 'minutes'], $preview['headers']);
        $this->assertSame(['E001', '2026-10-24', '30'], $preview['rows'][0]);
    }

    /**
     * @param  array<string, int>  $limits
     */
    private function createCategoryEmployee(array $limits): void
    {
        DB::table('categories')->insert(['id' => 1, 'name' => 'Short Leave Limited'] + $limits);
        DB::table('users')->insert([
            'id' => 101,
            'name' => 'Employee One',
            'code' => 'E001',
            'category_id' => json_encode([1], JSON_THROW_ON_ERROR),
        ]);
    }
}
