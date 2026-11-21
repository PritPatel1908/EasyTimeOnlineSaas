<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Central\TenantCronJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantCronJobMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.central.database' => ':memory:']);
        DB::purge('central');
        Schema::connection('central')->create('tenants', function (Blueprint $table): void {
            $table->string('id')->primary();
        });
        DB::connection('central')->table('tenants')->insert([
            ['id' => 'tenant-one'],
            ['id' => 'tenant-two'],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_migration_creates_a_default_daily_cron_for_existing_tenants(): void
    {
        Carbon::setTestNow('2026-11-21 09:00:00');
        $migration = require database_path('migrations/2026_11_21_120000_create_tenant_cron_jobs_table.php');
        $upgrade = require database_path('migrations/2026_11_21_123000_add_task_options_to_tenant_cron_jobs_table.php');

        $migration->up();
        $upgrade->up();

        $cronJobs = DB::connection('central')
            ->table('tenant_cron_jobs')
            ->orderBy('tenant_id')
            ->get();

        $this->assertCount(2, $cronJobs);
        $this->assertSame(
            [TenantCronJob::TASK_LEAVE_MUSTER, TenantCronJob::TASK_LEAVE_MUSTER],
            $cronJobs->pluck('task_key')->all(),
        );
        $this->assertSame(
            ['2026-11-22 00:00:00', '2026-11-22 00:00:00'],
            $cronJobs->pluck('next_run_at')->all(),
        );
        $this->assertSame(['Leave Muster', 'Leave Muster'], $cronJobs->pluck('name')->all());
        $this->assertSame(['daily', 'daily'], $cronJobs->pluck('schedule_type')->all());
    }
}
