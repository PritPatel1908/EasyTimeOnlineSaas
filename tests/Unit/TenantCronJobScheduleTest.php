<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\Tenant\RunScheduledTenantCron;
use App\Models\Central\TenantCronJob;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TenantCronJobScheduleTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_daily_schedule_uses_today_when_the_time_is_still_ahead_and_tomorrow_otherwise(): void
    {
        Carbon::setTestNow('2026-11-21 09:00:00');

        $today = TenantCronJob::nextDailyRunAt('10:15:00');
        $tomorrow = TenantCronJob::nextDailyRunAt('08:00:00');

        $this->assertSame('2026-11-21 10:15:00', $today->toDateTimeString());
        $this->assertSame('2026-11-22 08:00:00', $tomorrow->toDateTimeString());

        Carbon::setTestNow('2026-11-21 10:15:00');
        $sameTime = TenantCronJob::nextDailyRunAt('10:15:00');

        $this->assertSame('2026-11-22 10:15:00', $sameTime->toDateTimeString());
    }

    public function test_interval_schedule_runs_after_the_configured_number_of_minutes(): void
    {
        Carbon::setTestNow('2026-11-21 09:00:00');

        $nextRun = TenantCronJob::nextRunAt('interval', intervalMinutes: 5);

        $this->assertSame('2026-11-21 09:05:00', $nextRun->toDateTimeString());
    }

    public function test_scheduled_leave_task_uses_the_existing_tenant_processing_worker(): void
    {
        $job = new RunScheduledTenantCron(1, TenantCronJob::TASK_LEAVE_MUSTER);

        $this->assertSame('database_tenant', $job->connection);
        $this->assertSame('processing', $job->queue);
    }
}
