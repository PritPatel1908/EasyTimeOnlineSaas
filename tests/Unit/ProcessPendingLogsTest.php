<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Attendance\AttCalculator;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use Illuminate\Contracts\Database\ModelIdentifier;
use Tests\TestCase;

class ProcessPendingLogsTest extends TestCase
{
    public function test_job_serializes_attendance_log_id_without_restoring_tenant_model(): void
    {
        $log = new AttendanceLog;
        $log->setAttribute('id', 42);

        $serializedJob = serialize(new ProcessPendingLogs($log));
        $restoredJob = unserialize($serializedJob);

        $this->assertStringNotContainsString(ModelIdentifier::class, $serializedJob);
        $this->assertSame('42', $restoredJob->uniqueId());
    }

    public function test_manual_punch_direction_does_not_require_a_machine(): void
    {
        foreach ([false, true] as $isOut) {
            $log = new AttendanceLog;
            $log->setAttribute('is_manual', true);
            $log->setAttribute('is_out', $isOut);

            $calculator = new AttCalculator($log);
            $calculator->checkInOrOut();

            $this->assertSame($isOut, $log->is_out);
        }
    }

    public function test_attendance_shift_fields_remain_empty_when_no_shift_is_configured(): void
    {
        $calculator = new AttCalculator(new AttendanceLog);
        $attendance = new Attendance;

        $currentAttendance = new \ReflectionProperty(AttCalculator::class, 'curatt');
        $currentAttendance->setValue($calculator, $attendance);

        $attendanceDate = new \ReflectionProperty(AttCalculator::class, 'attendance_date');
        $attendanceDate->setValue($calculator, '2026-10-26');

        $applyShift = new \ReflectionMethod(AttCalculator::class, 'applyCurrentShiftToAttendance');
        $applyShift->invoke($calculator);

        $this->assertNull($attendance->shift_id);
        $this->assertNull($attendance->shift_code);
        $this->assertNull($attendance->shift_in_time);
        $this->assertNull($attendance->shift_out_time);
        $this->assertFalse($attendance->is_night_shift);
    }

    public function test_missing_setting_helper_disables_optional_attendance_rules_without_failing(): void
    {
        $calculator = new AttCalculator(new AttendanceLog);
        $isSettingEnabled = new \ReflectionMethod(AttCalculator::class, 'isSettingEnabled');

        $this->assertFalse($isSettingEnabled->invoke($calculator, 'half_day_rules'));
    }
}
