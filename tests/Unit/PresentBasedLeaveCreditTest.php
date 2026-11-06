<?php

namespace Tests\Unit;

use App\Support\PresentBasedLeaveCredit;
use Carbon\Carbon;
use Tests\TestCase;

class PresentBasedLeaveCreditTest extends TestCase
{
    public function test_monthly_basis_uses_the_month_before_the_credit_month(): void
    {
        [$start, $end] = PresentBasedLeaveCredit::getAttendancePeriod(
            'monthly',
            Carbon::parse('2026-04-15'),
            Carbon::parse('2026-04-01'),
        );

        $this->assertSame('2026-03-01', $start->toDateString());
        $this->assertSame('2026-03-31', $end->toDateString());
    }

    public function test_yearly_basis_uses_financial_year_to_date(): void
    {
        [$start, $end] = PresentBasedLeaveCredit::getAttendancePeriod(
            'yearly',
            Carbon::parse('2026-09-10'),
            Carbon::parse('2026-04-01'),
        );

        $this->assertSame('2026-04-01', $start->toDateString());
        $this->assertSame('2026-09-10', $end->toDateString());
    }

    public function test_credit_is_proportional_to_present_days_in_the_period(): void
    {
        $credit = PresentBasedLeaveCredit::calculate(
            2,
            30,
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-03-31'),
        );

        $this->assertSame(1.94, $credit);
    }

    public function test_paid_leave_statuses_are_counted_as_present_days(): void
    {
        $attendanceRows = collect([
            (object) ['code' => 'PP', 'is_half_day' => false],
            (object) ['code' => 'PP', 'is_half_day' => false],
            (object) ['code' => 'PP', 'is_half_day' => false],
            (object) ['code' => 'PL', 'is_half_day' => false],
            (object) ['code' => 'PL', 'is_half_day' => false],
            (object) ['code' => 'PL', 'is_half_day' => false],
        ]);

        $presentDays = PresentBasedLeaveCredit::countPresentAttendanceDays(
            $attendanceRows,
            ['PL'],
            false,
            false,
        );

        $this->assertSame(6.0, $presentDays);
    }

    public function test_threshold_credit_rules_use_configured_amount_at_threshold_and_monthly_amount_above_it(): void
    {
        $periodStart = Carbon::parse('2026-07-01');
        $periodEnd = Carbon::parse('2026-07-31');

        $this->assertSame(
            1.0,
            PresentBasedLeaveCredit::calculate(2, 3, $periodStart, $periodEnd, 3, 1),
        );
        $this->assertSame(
            2.0,
            PresentBasedLeaveCredit::calculate(2, 4, $periodStart, $periodEnd, 3, 1),
        );
        $this->assertSame(
            0.0,
            PresentBasedLeaveCredit::calculate(2, 2, $periodStart, $periodEnd, 3, 1),
        );
    }
}
