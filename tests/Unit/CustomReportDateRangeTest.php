<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CustomReportDateRange;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class CustomReportDateRangeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_today_preset_is_resolved_against_the_current_date_each_time(): void
    {
        $resolver = new CustomReportDateRange;

        Carbon::setTestNow('2026-11-20 09:00:00');
        $firstRange = $resolver->resolve('today');
        Carbon::setTestNow('2026-11-21 09:00:00');
        $nextRange = $resolver->resolve('today');

        $this->assertSame('2026-11-20', $firstRange['from']->toDateString());
        $this->assertSame('2026-11-20', $firstRange['to']->toDateString());
        $this->assertSame('2026-11-21', $nextRange['from']->toDateString());
        $this->assertSame('2026-11-21', $nextRange['to']->toDateString());
    }

    public function test_period_presets_resolve_to_the_end_of_their_period(): void
    {
        Carbon::setTestNow('2026-11-20 09:00:00');

        $resolver = new CustomReportDateRange;
        $month = $resolver->resolve('this_month');
        $lastWeek = $resolver->resolve('last_week');
        $lastMonth = $resolver->resolve('last_month');
        $lastQuarter = $resolver->resolve('last_quarter');
        $lastYear = $resolver->resolve('last_year');

        $this->assertSame('2026-11-01', $month['from']->toDateString());
        $this->assertSame('2026-11-30', $month['to']->toDateString());
        $this->assertSame('2026-11-09', $lastWeek['from']->toDateString());
        $this->assertSame('2026-11-15', $lastWeek['to']->toDateString());
        $this->assertSame('2026-10-01', $lastMonth['from']->toDateString());
        $this->assertSame('2026-10-31', $lastMonth['to']->toDateString());
        $this->assertSame('2026-07-01', $lastQuarter['from']->toDateString());
        $this->assertSame('2026-09-30', $lastQuarter['to']->toDateString());
        $this->assertSame('2025-01-01', $lastYear['from']->toDateString());
        $this->assertSame('2025-12-31', $lastYear['to']->toDateString());
    }

    public function test_custom_preset_keeps_the_user_selected_dates(): void
    {
        $range = (new CustomReportDateRange)->resolve(
            'custom',
            CarbonImmutable::parse('2026-10-01'),
            CarbonImmutable::parse('2026-10-15'),
        );

        $this->assertSame('2026-10-01', $range['from']->toDateString());
        $this->assertSame('2026-10-15', $range['to']->toDateString());
    }
}
