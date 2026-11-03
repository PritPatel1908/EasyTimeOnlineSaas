<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Tenant\ApplyApprovedShortLeavePunches;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ShortLeaveApplication;
use App\Services\Tenant\ShortLeaveAttendancePunchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShortLeaveAttendancePunchServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'attendances2026',
            'attendance_logs',
            'manual_punch_users',
            'manual_punches',
            'approval_statuses',
            'short_leave_applications',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('short_leave_applications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->unsignedInteger('minutes')->nullable();
            $table->unsignedTinyInteger('short_leave_type');
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('approval_statuses', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->string('approval_status');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('manual_punches', function (Blueprint $table): void {
            $table->id();
            $table->string('punch_type');
            $table->date('punch_date');
            $table->time('punch_time');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('manual_punch_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('manual_punch_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('referance_type')->nullable();
            $table->unsignedBigInteger('referance_id')->nullable();
            $table->string('user_code')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->unsignedTinyInteger('punch_type')->nullable();
            $table->boolean('is_out')->default(false);
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->boolean('is_staged')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('attendances2026', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->dateTime('shift_in_time')->nullable();
            $table->dateTime('shift_out_time')->nullable();
            $table->dateTime('in_time')->nullable();
            $table->unsignedBigInteger('in_log_id')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->unsignedBigInteger('out_log_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->decimal('c_off_days', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        foreach ([
            'attendances2026',
            'attendance_logs',
            'manual_punch_users',
            'manual_punches',
            'approval_statuses',
            'short_leave_applications',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_late_coming_punch_is_clamped_to_shift_start_and_created_once(): void
    {
        Bus::fake();
        $this->createApprovedApplication(1, '2026-11-03', 1, 30);
        DB::table('attendance_logs')->insert([
            'id' => 10,
            'user_id' => 1,
            'user_code' => 'E001',
            'datetime' => '2026-11-03 09:20:00',
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 1,
            'date' => '2026-11-03',
            'shift_in_time' => '2026-11-03 09:00:00',
            'shift_out_time' => '2026-11-03 18:00:00',
            'in_time' => '2026-11-03 09:20:00',
            'in_log_id' => 10,
            'is_locked' => false,
        ]);

        $service = app(ShortLeaveAttendancePunchService::class);
        $service->createForApprovedApplication(1);
        $service->createForApprovedApplication(1);

        $punch = ManualPunch::query()->firstOrFail();
        $this->assertSame('1', $punch->punch_type);
        $this->assertSame('2026-11-03', $punch->punch_date->toDateString());
        $this->assertSame('09:00:00', $punch->punch_time->format('H:i:s'));
        $this->assertSame(1, DB::table('manual_punches')->count());
        $this->assertDatabaseHas('attendance_logs', [
            'referance_type' => ManualPunch::class,
            'referance_id' => 1,
            'datetime' => '2026-11-03 09:00:00',
            'is_manual' => true,
        ]);
    }

    public function test_early_going_punch_is_clamped_to_shift_end(): void
    {
        Bus::fake();
        $this->createApprovedApplication(2, '2026-11-03', 2, 30);
        DB::table('attendance_logs')->insert([
            'id' => 20,
            'user_id' => 1,
            'user_code' => 'E001',
            'datetime' => '2026-11-03 17:50:00',
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 1,
            'date' => '2026-11-03',
            'shift_in_time' => '2026-11-03 09:00:00',
            'shift_out_time' => '2026-11-03 18:00:00',
            'out_time' => '2026-11-03 17:50:00',
            'out_log_id' => 20,
            'is_locked' => false,
        ]);

        app(ShortLeaveAttendancePunchService::class)->createForApprovedApplication(2);

        $punch = ManualPunch::query()->firstOrFail();
        $this->assertSame('2', $punch->punch_type);
        $this->assertSame('2026-11-03', $punch->punch_date->toDateString());
        $this->assertSame('18:00:00', $punch->punch_time->format('H:i:s'));
    }

    public function test_break_short_leave_creates_out_and_in_punches_at_selected_times(): void
    {
        Bus::fake();
        $this->createApprovedApplication(3, '2026-11-03', 3, null, '12:00:00', '12:30:00');

        app(ShortLeaveAttendancePunchService::class)->createForApprovedApplication(3);

        $punches = ManualPunch::query()->orderBy('id')->get();
        $this->assertSame(['2', '1'], $punches->pluck('punch_type')->all());
        $this->assertSame(['12:00:00', '12:30:00'], $punches->map(
            static fn (ManualPunch $punch): string => $punch->punch_time->format('H:i:s'),
        )->all());
        $this->assertSame(2, DB::table('manual_punches')->count());
    }

    public function test_late_coming_application_waits_until_attendance_has_a_first_punch(): void
    {
        Bus::fake();
        $this->createApprovedApplication(4, '2026-11-03', 1, 15);
        $service = app(ShortLeaveAttendancePunchService::class);

        $service->createForApprovedApplication(4);
        $this->assertSame(0, DB::table('manual_punches')->count());

        DB::table('attendance_logs')->insert([
            'id' => 40,
            'user_id' => 1,
            'user_code' => 'E001',
            'datetime' => '2026-11-03 09:20:00',
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 1,
            'date' => '2026-11-03',
            'shift_in_time' => '2026-11-03 09:00:00',
            'shift_out_time' => '2026-11-03 18:00:00',
            'in_time' => '2026-11-03 09:20:00',
            'in_log_id' => 40,
            'is_locked' => false,
        ]);

        $service->createForApprovedApplication(4);

        $punch = ManualPunch::query()->firstOrFail();
        $this->assertSame('09:05:00', $punch->punch_time->format('H:i:s'));
    }

    public function test_processed_punch_retries_applications_approved_before_the_attendance_punch(): void
    {
        Bus::fake();
        $this->createApprovedApplication(5, '2026-11-03', 1, 15);
        $log = new AttendanceLog;
        $log->setRawAttributes([
            'id' => 50,
            'user_id' => 1,
            'datetime' => '2026-11-03 09:20:00',
        ]);

        $method = new \ReflectionMethod(ProcessPendingLogs::class, 'dispatchApprovedShortLeavePunches');
        $method->invoke(new ProcessPendingLogs(50), $log);

        Bus::assertDispatched(ApplyApprovedShortLeavePunches::class, static fn ($job): bool => $job->applicationId === 5);
    }

    public function test_processed_punch_does_not_requeue_an_application_with_generated_punches(): void
    {
        Bus::fake();
        $this->createApprovedApplication(7, '2026-11-03', 1, 15);
        $punch = ManualPunch::query()->create([
            'punch_type' => '1',
            'punch_date' => '2026-11-03',
            'punch_time' => '09:00:00',
            'reason' => 'Approved short leave application #7',
        ]);
        $punch->Users()->attach(1);
        $log = new AttendanceLog;
        $log->setRawAttributes([
            'id' => 70,
            'user_id' => 1,
            'datetime' => '2026-11-03 09:00:00',
        ]);

        $method = new \ReflectionMethod(ProcessPendingLogs::class, 'dispatchApprovedShortLeavePunches');
        $method->invoke(new ProcessPendingLogs(70), $log);

        Bus::assertNotDispatched(ApplyApprovedShortLeavePunches::class);
    }

    public function test_generated_punches_are_removed_and_attendance_is_reprocessed_when_application_changes(): void
    {
        Bus::fake();
        $this->createApprovedApplication(6, '2026-11-03', 1, 15);
        DB::table('attendance_logs')->insert([
            'id' => 60,
            'user_id' => 1,
            'user_code' => 'E001',
            'datetime' => '2026-11-03 09:20:00',
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 1,
            'date' => '2026-11-03',
            'shift_in_time' => '2026-11-03 09:00:00',
            'shift_out_time' => '2026-11-03 18:00:00',
            'in_time' => '2026-11-03 09:20:00',
            'in_log_id' => 60,
            'is_locked' => false,
        ]);
        $service = app(ShortLeaveAttendancePunchService::class);
        $service->createForApprovedApplication(6);

        $generatedLogId = DB::table('attendance_logs')
            ->where('referance_type', ManualPunch::class)
            ->value('id');
        DB::table('attendances2026')->update(['in_log_id' => $generatedLogId]);
        $service->removeGeneratedPunches(ShortLeaveApplication::query()->findOrFail(6));

        $this->assertSoftDeleted('manual_punches', ['reason' => 'Approved short leave application #6']);
        $this->assertSoftDeleted('attendance_logs', ['id' => $generatedLogId]);
        $this->assertSame(0, DB::table('attendances2026')->count());
        Bus::assertDispatched(ReprocessAttendanceLogs::class, static fn ($job): bool => $job->employeeIds === [1]);
    }

    private function createApprovedApplication(
        int $id,
        string $date,
        int $type,
        ?int $minutes,
        ?string $fromTime = null,
        ?string $toTime = null,
    ): void {
        DB::table('users')->insert(['id' => 1, 'code' => 'E001']);
        DB::table('short_leave_applications')->insert([
            'id' => $id,
            'user_id' => 1,
            'date' => $date,
            'short_leave_type' => $type,
            'minutes' => $minutes,
            'from_time' => $fromTime,
            'to_time' => $toTime,
            'status' => 1,
        ]);
        DB::table('approval_statuses')->insert([
            'approvable_type' => ShortLeaveApplication::class,
            'approvable_id' => $id,
            'approval_status' => 'approved',
        ]);
    }
}
