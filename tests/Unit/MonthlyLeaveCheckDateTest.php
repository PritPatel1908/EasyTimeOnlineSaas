<?php

namespace Tests\Unit;

use App\Support\MonthlyLeaveCheckDate;
use Carbon\Carbon;
use Tests\TestCase;

class MonthlyLeaveCheckDateTest extends TestCase
{
    public function test_it_normalizes_leading_zero_and_catches_up_a_missed_schedule_day(): void
    {
        $scheduledDate = MonthlyLeaveCheckDate::getScheduledDate(
            Carbon::parse('2026-11-04'),
            '01',
        );

        $this->assertSame('2026-11-01', $scheduledDate?->format('Y-m-d'));
    }

    public function test_it_does_not_schedule_before_the_configured_day(): void
    {
        $this->assertNull(MonthlyLeaveCheckDate::getScheduledDate(
            Carbon::parse('2026-11-04'),
            '05',
        ));
    }

    public function test_it_clamps_a_day_beyond_the_month_to_its_last_day(): void
    {
        $scheduledDate = MonthlyLeaveCheckDate::getScheduledDate(
            Carbon::parse('2026-02-28'),
            '31',
        );

        $this->assertSame('2026-02-28', $scheduledDate?->format('Y-m-d'));
    }

    public function test_it_ignores_invalid_configured_days(): void
    {
        $this->assertNull(MonthlyLeaveCheckDate::getScheduledDate(
            Carbon::parse('2026-11-30'),
            '0',
        ));
        $this->assertNull(MonthlyLeaveCheckDate::getScheduledDate(
            Carbon::parse('2026-11-30'),
            'not-a-day',
        ));
    }
}
