<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TimeCardOvertimeDisplayTest extends TestCase
{
    public function test_overtime_is_automatically_approved_when_category_does_not_require_approval(): void
    {
        $html = $this->renderAttendanceRecord(false);

        $this->assertSame(2, substr_count($html, '1h 15m'));
        $this->assertStringContainsString('>Approved Overtime</span>', $html);
    }

    public function test_approved_overtime_remains_separate_when_category_requires_approval(): void
    {
        $html = $this->renderAttendanceRecord(true);

        $this->assertStringContainsString('1h 15m', $html);
        $this->assertStringContainsString('0h 30m', $html);
        $this->assertStringContainsString('>Approved Overtime</span>', $html);
    }

    private function renderAttendanceRecord(bool $needsOvertimeApproval): string
    {
        $attendance = (object) [
            'id' => 1,
            'user' => (object) [
                'name' => 'Test Employee',
                'code' => 'EMP001',
                'category' => (object) ['need_approval_for_overtime' => $needsOvertimeApproval],
            ],
            'date' => Carbon::parse('2026-11-01'),
            'shift_code' => null,
            'shift' => null,
            'in_time' => null,
            'out_time' => null,
            'work_time' => 0,
            'is_late' => false,
            'late_minutes' => 0,
            'is_early' => false,
            'early_minutes' => 0,
            'is_half_day' => false,
            'is_absent' => false,
            'is_over_time' => true,
            'total_overtime_minutes' => 75,
            'row_overtime_minutes' => 75,
            'approved_overtime_minutes' => 0,
            'has_error' => false,
            'is_holiday' => false,
            'is_weekoff' => false,
            'status_master' => (object) ['alias' => null],
        ];
        $attendanceRecords = new LengthAwarePaginator([$attendance], 1, 50);

        return view('time-management.time-card.partials.records', [
            'attendanceRecords' => $attendanceRecords,
            'year' => 2026,
        ])->render();
    }
}
