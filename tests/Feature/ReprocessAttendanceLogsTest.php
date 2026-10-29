<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AttendanceLogController;
use App\Jobs\Tenant\DeleteAttendanceLogs;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
use App\Jobs\Tenant\RestoreAttendanceLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReprocessAttendanceLogsTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        config([
            'database.connections.reprocess_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'reprocess_test',
        ]);
        DB::purge('reprocess_test');

        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('user_type')->nullable();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->softDeletes();
        });

        Schema::create('attendance_logs', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_code')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_staged')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->boolean('has_error')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_out')->default(false);
            $table->boolean('debug_me')->default(false);
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('process_tags', function ($table): void {
            $table->id();
            $table->morphs('taggable');
            $table->string('name');
            $table->timestamps();
        });

        foreach (['attendances2026', 'in_out_musters2026'] as $tableName) {
            Schema::create($tableName, function ($table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('attendance_log_id')->nullable();
                $table->date('date')->nullable();
                $table->unsignedBigInteger('in_log_id')->nullable();
                $table->unsignedBigInteger('out_log_id')->nullable();
                $table->boolean('is_locked')->default(false);
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        DB::purge('reprocess_test');
        config(['database.default' => $this->originalDefaultConnection]);
        config()->offsetUnset('database.connections.reprocess_test');

        parent::tearDown();
    }

    public function test_job_resets_ignored_logs_and_removes_matching_unlocked_musters_before_reprocessing(): void
    {
        $matchingLogId = DB::table('attendance_logs')->insertGetId([
            'user_code' => null,
            'datetime' => '2026-03-10 09:00:00',
            'is_calculated' => true,
            'is_staged' => false,
            'is_ignored' => true,
            'is_locked' => false,
            'debug_me' => false,
        ]);
        $outsideRangeLogId = DB::table('attendance_logs')->insertGetId([
            'datetime' => '2026-03-12 09:00:00',
            'is_calculated' => true,
            'is_staged' => false,
            'is_locked' => false,
            'debug_me' => false,
        ]);

        foreach (['attendances2026', 'in_out_musters2026'] as $tableName) {
            DB::table($tableName)->insert([
                'date' => '2026-03-10',
                'in_log_id' => $matchingLogId,
                'is_locked' => false,
            ]);
            DB::table($tableName)->insert([
                'date' => '2026-03-12',
                'in_log_id' => $outsideRangeLogId,
                'is_locked' => false,
            ]);
        }

        (new ReprocessAttendanceLogs('2026-03-10 00:00:00', '2026-03-10 23:59:59'))->handle();

        $this->assertSame(1, DB::table('attendances2026')->count());
        $this->assertSame(1, DB::table('in_out_musters2026')->count());
        $this->assertFalse((bool) DB::table('attendance_logs')->where('id', $matchingLogId)->value('is_calculated'));
        $this->assertTrue((bool) DB::table('attendance_logs')->where('id', $matchingLogId)->value('is_staged'));
        $this->assertFalse((bool) DB::table('attendance_logs')->where('id', $matchingLogId)->value('is_ignored'));
        $this->assertTrue((bool) DB::table('attendance_logs')->where('id', $outsideRangeLogId)->value('is_calculated'));
    }

    public function test_reprocessing_does_not_reset_or_delete_attendance_for_status_zero_logs(): void
    {
        $inactiveLogId = DB::table('attendance_logs')->insertGetId([
            'user_code' => 'INACTIVE',
            'datetime' => '2026-03-10 09:00:00',
            'status' => 0,
            'is_calculated' => true,
            'is_staged' => false,
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 101,
            'date' => '2026-03-10',
            'in_log_id' => $inactiveLogId,
            'is_locked' => false,
        ]);

        (new ReprocessAttendanceLogs('2026-03-10 00:00:00', '2026-03-10 23:59:59'))->handle();

        $this->assertSame(1, DB::table('attendances2026')->count());
        $this->assertTrue((bool) DB::table('attendance_logs')->where('id', $inactiveLogId)->value('is_calculated'));
    }

    public function test_reprocess_form_dispatches_selected_employee_and_datetime_range(): void
    {
        Bus::fake();
        $employeeId = DB::table('users')->insertGetId([
            'user_type' => 'employee',
            'name' => 'Example Employee',
            'code' => 'EMP001',
        ]);

        $response = app(AttendanceLogController::class)->reprocessRange(Request::create(
            '/time-management/attendance-logs/reprocess-range',
            'POST',
            [
                'start_datetime' => '2026-03-10T09:00',
                'end_datetime' => '2026-03-10T17:00',
                'employee_ids' => [(string) $employeeId],
            ],
        ));

        $this->assertStringEndsWith('/time-management/attendance-logs', $response->getTargetUrl());
        Bus::assertDispatched(ReprocessAttendanceLogs::class, static function (ReprocessAttendanceLogs $job) use ($employeeId): bool {
            return $job->startDateTime === '2026-03-10 09:00:00'
                && $job->endDateTime === '2026-03-10 17:00:59'
                && $job->employeeIds === [$employeeId]
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
    }

    public function test_delete_range_form_dispatches_selected_employee_and_datetime_range(): void
    {
        Bus::fake();
        $employeeId = DB::table('users')->insertGetId([
            'user_type' => 'employee',
            'name' => 'Example Employee',
            'code' => 'EMP001',
        ]);

        $response = app(AttendanceLogController::class)->deleteRange(Request::create(
            '/time-management/attendance-logs/delete-range',
            'POST',
            [
                'start_datetime' => '2026-03-10T09:00',
                'end_datetime' => '2026-03-10T17:00',
                'employee_ids' => [(string) $employeeId],
            ],
        ));

        $this->assertStringEndsWith('/time-management/attendance-logs', $response->getTargetUrl());
        Bus::assertDispatched(DeleteAttendanceLogs::class, static function (DeleteAttendanceLogs $job) use ($employeeId): bool {
            return $job->startDateTime === '2026-03-10 09:00:00'
                && $job->endDateTime === '2026-03-10 17:00:59'
                && $job->employeeIds === [$employeeId]
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
    }

    public function test_restore_range_form_dispatches_selected_employee_and_datetime_range(): void
    {
        Bus::fake();
        $employeeId = DB::table('users')->insertGetId([
            'user_type' => 'employee',
            'name' => 'Example Employee',
            'code' => 'EMP001',
        ]);

        $response = app(AttendanceLogController::class)->restoreRange(Request::create(
            '/time-management/attendance-logs/restore-range',
            'POST',
            [
                'start_datetime' => '2026-03-10T09:00',
                'end_datetime' => '2026-03-10T17:00',
                'employee_ids' => [(string) $employeeId],
            ],
        ));

        $this->assertStringEndsWith('/time-management/attendance-logs?deleted=1', $response->getTargetUrl());
        Bus::assertDispatched(RestoreAttendanceLogs::class, static function (RestoreAttendanceLogs $job) use ($employeeId): bool {
            return $job->startDateTime === '2026-03-10 09:00:00'
                && $job->endDateTime === '2026-03-10 17:00:59'
                && $job->employeeIds === [$employeeId]
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
    }

    public function test_restore_range_job_restores_only_matching_deleted_punches_with_requested_flags(): void
    {
        $matchingLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 101,
            'datetime' => '2026-03-10 12:00:00',
            'is_calculated' => true,
            'is_ignored' => true,
            'status' => 0,
            'deleted_at' => now(),
        ]);
        $outsideRangeLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 101,
            'datetime' => '2026-03-11 12:00:00',
            'status' => 0,
            'deleted_at' => now(),
        ]);
        $differentEmployeeLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 102,
            'datetime' => '2026-03-10 12:00:00',
            'status' => 0,
            'deleted_at' => now(),
        ]);

        (new RestoreAttendanceLogs('2026-03-10 09:00:00', '2026-03-10 17:00:59', [101]))->handle();

        $restoredLog = DB::table('attendance_logs')->where('id', $matchingLogId)->first();
        $this->assertNull($restoredLog->deleted_at);
        $this->assertSame(1, (int) $restoredLog->status);
        $this->assertSame(0, (int) $restoredLog->is_calculated);
        $this->assertSame(0, (int) $restoredLog->is_ignored);
        $this->assertNotNull(DB::table('attendance_logs')->where('id', $outsideRangeLogId)->value('deleted_at'));
        $this->assertNotNull(DB::table('attendance_logs')->where('id', $differentEmployeeLogId)->value('deleted_at'));
    }

    public function test_delete_range_job_deletes_only_matching_unlocked_active_punches(): void
    {
        $matchingLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 101,
            'datetime' => '2026-03-10 12:00:00',
            'is_calculated' => true,
            'is_ignored' => false,
            'is_locked' => false,
            'status' => 1,
        ]);
        DB::table('attendances2026')->insert([
            'attendance_log_id' => $matchingLogId,
            'date' => '2026-03-10',
        ]);
        $lockedLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 101,
            'datetime' => '2026-03-10 13:00:00',
            'is_locked' => true,
            'status' => 1,
        ]);
        $outsideRangeLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 101,
            'datetime' => '2026-03-11 12:00:00',
            'status' => 1,
        ]);
        $differentEmployeeLogId = DB::table('attendance_logs')->insertGetId([
            'user_id' => 102,
            'datetime' => '2026-03-10 12:00:00',
            'status' => 1,
        ]);

        (new DeleteAttendanceLogs('2026-03-10 09:00:00', '2026-03-10 17:00:59', [101]))->handle();

        $deletedLog = DB::table('attendance_logs')->where('id', $matchingLogId)->first();
        $this->assertSame(0, (int) $deletedLog->status);
        $this->assertSame(0, (int) $deletedLog->is_calculated);
        $this->assertSame(1, (int) $deletedLog->is_ignored);
        $this->assertNotNull($deletedLog->deleted_at);
        $this->assertSame($matchingLogId, (int) DB::table('attendances2026')->value('attendance_log_id'));
        $this->assertNull(DB::table('attendance_logs')->where('id', $lockedLogId)->value('deleted_at'));
        $this->assertNull(DB::table('attendance_logs')->where('id', $outsideRangeLogId)->value('deleted_at'));
        $this->assertNull(DB::table('attendance_logs')->where('id', $differentEmployeeLogId)->value('deleted_at'));
    }

    public function test_individual_reprocess_resets_and_dispatches_an_ignored_log(): void
    {
        Bus::fake();
        $logId = DB::table('attendance_logs')->insertGetId([
            'datetime' => '2026-03-10 09:00:00',
            'is_calculated' => true,
            'is_staged' => false,
            'is_ignored' => true,
            'is_locked' => false,
            'debug_me' => false,
        ]);
        $request = Request::create('/time-management/attendance-logs/'.$logId.'/reprocess', 'POST');
        $request->setRouteResolver(fn () => new class($logId)
        {
            public function __construct(private int $logId) {}

            public function parameter(string $name, mixed $default = null): mixed
            {
                return $name === 'attendance_log' ? $this->logId : $default;
            }
        });

        $response = app(AttendanceLogController::class)->reprocess($request);

        $this->assertStringEndsWith('/time-management/attendance-logs/'.$logId, $response->getTargetUrl());
        $this->assertFalse((bool) DB::table('attendance_logs')->where('id', $logId)->value('is_calculated'));
        $this->assertFalse((bool) DB::table('attendance_logs')->where('id', $logId)->value('is_ignored'));
        Bus::assertDispatched(ProcessPendingLogs::class, fn (ProcessPendingLogs $job): bool => $job->uniqueId() === (string) $logId);
    }

    public function test_deleting_a_punch_marks_it_inactive_ignored_and_uncalculated(): void
    {
        $logId = DB::table('attendance_logs')->insertGetId([
            'datetime' => '2026-03-10 09:00:00',
            'is_calculated' => true,
            'is_ignored' => false,
            'status' => 1,
        ]);

        $response = app(AttendanceLogController::class)->destroy($this->requestForLog($logId, 'delete'));

        $log = DB::table('attendance_logs')->where('id', $logId)->first();
        $this->assertStringEndsWith('/time-management/attendance-logs', $response->getTargetUrl());
        $this->assertSame(0, (int) $log->status);
        $this->assertSame(0, (int) $log->is_calculated);
        $this->assertSame(1, (int) $log->is_ignored);
        $this->assertNotNull($log->deleted_at);
    }

    public function test_restoring_a_deleted_punch_marks_it_active_ignored_off_and_uncalculated(): void
    {
        $logId = DB::table('attendance_logs')->insertGetId([
            'datetime' => '2026-03-10 09:00:00',
            'is_calculated' => true,
            'is_ignored' => true,
            'status' => 0,
            'deleted_at' => now(),
        ]);

        $response = app(AttendanceLogController::class)->restore($this->requestForLog($logId, 'restore'));

        $log = DB::table('attendance_logs')->where('id', $logId)->first();
        $this->assertStringEndsWith('/time-management/attendance-logs?deleted=1', $response->getTargetUrl());
        $this->assertSame(1, (int) $log->status);
        $this->assertSame(0, (int) $log->is_calculated);
        $this->assertSame(0, (int) $log->is_ignored);
        $this->assertNull($log->deleted_at);
    }

    private function requestForLog(int $logId, string $action): Request
    {
        $request = Request::create('/time-management/attendance-logs/'.$logId.'/'.$action, 'POST');
        $request->setRouteResolver(static fn () => new class($logId)
        {
            public function __construct(private int $logId) {}

            public function parameter(string $name, mixed $default = null): mixed
            {
                return $name === 'attendance_log' ? $this->logId : $default;
            }
        });

        return $request;
    }
}
