<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AttendanceLogController;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\TestCase;

class TenantAttendanceLogModuleTest extends TestCase
{
    public function test_reprocess_resets_log_state_and_dispatches_the_processing_job(): void
    {
        Bus::fake();

        $attendanceLog = Mockery::mock(AttendanceLog::class)->makePartial();
        $attendanceLog->setRawAttributes([
            'id' => 42,
            'is_locked' => false,
            'is_ignored' => false,
        ]);
        $attendanceLog->shouldReceive('update')
            ->once()
            ->with([
                'is_calculated' => false,
                'is_staged' => false,
                'has_error' => false,
            ])
            ->andReturn(true);

        $response = app(AttendanceLogController::class)->reprocess($this->requestForLog($attendanceLog));

        $this->assertStringEndsWith('/time-management/attendance-logs/42', $response->getTargetUrl());
        Bus::assertDispatched(ProcessPendingLogs::class, static function (ProcessPendingLogs $job): bool {
            return $job->uniqueId() === '42'
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
    }

    public function test_locked_and_ignored_logs_are_not_reprocessed(): void
    {
        Bus::fake();

        foreach ([
            ['id' => 43, 'is_locked' => true, 'is_ignored' => false],
            ['id' => 44, 'is_locked' => false, 'is_ignored' => true],
        ] as $attributes) {
            $attendanceLog = Mockery::mock(AttendanceLog::class)->makePartial();
            $attendanceLog->setRawAttributes($attributes);

            app(AttendanceLogController::class)->reprocess($this->requestForLog($attendanceLog));
        }

        Bus::assertNothingDispatched();
    }

    private function requestForLog(AttendanceLog $attendanceLog): Request
    {
        $request = Request::create('/time-management/attendance-logs/'.$attendanceLog->id.'/reprocess', 'POST');
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('parameter')
            ->with('attendance_log', null)
            ->andReturn($attendanceLog);
        $request->setRouteResolver(static fn () => $route);

        return $request;
    }
}
