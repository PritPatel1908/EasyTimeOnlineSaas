<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Tenant\RunScheduledTenantCron;
use App\Models\Central\TenantCronJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RunScheduledTenantCronTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.central.database' => ':memory:']);
        DB::purge('central');
        Schema::connection('central')->create('tenant_cron_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('task_key');
            $table->string('name');
            $table->string('schedule_type');
            $table->time('schedule_time');
            $table->unsignedSmallInteger('interval_minutes')->nullable();
            $table->boolean('enabled');
            $table->dateTime('next_run_at');
            $table->dateTime('last_run_at')->nullable();
            $table->string('last_status');
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
        DB::connection('central')->table('tenant_cron_jobs')->insert([
            'tenant_id' => 'tenant-one',
            'task_key' => TenantCronJob::TASK_PUNCH_PROCESS,
            'name' => 'Punch Process',
            'schedule_type' => 'interval',
            'schedule_time' => '00:00:00',
            'interval_minutes' => 5,
            'enabled' => true,
            'next_run_at' => now(),
            'last_status' => 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_punch_process_schedule_calls_the_local_log_processing_command_mode(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('app:process-log-schedule', ['--local-only' => true])
            ->andReturn(0);

        (new RunScheduledTenantCron(1, TenantCronJob::TASK_PUNCH_PROCESS))->handle();

        $this->assertSame(
            'dispatched',
            DB::connection('central')->table('tenant_cron_jobs')->value('last_status'),
        );
    }
}
