<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AttendanceLogController;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
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

    public function test_job_removes_matching_unlocked_musters_and_attendance_before_reprocessing_logs(): void
    {
        $matchingLogId = DB::table('attendance_logs')->insertGetId([
            'user_code' => null,
            'datetime' => '2026-03-10 09:00:00',
            'is_calculated' => true,
            'is_staged' => false,
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
        $this->assertTrue((bool) DB::table('attendance_logs')->where('id', $outsideRangeLogId)->value('is_calculated'));
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
}
