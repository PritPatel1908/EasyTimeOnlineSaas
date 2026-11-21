<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('tenant_cron_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('task_key', 64);
            $table->time('schedule_time')->default('00:00:00');
            $table->boolean('enabled')->default(true);
            $table->dateTime('next_run_at');
            $table->dateTime('last_run_at')->nullable();
            $table->string('last_status', 20)->default('never');
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'task_key']);
            $table->index(['enabled', 'next_run_at']);
        });

        $nextRunAt = now()->startOfDay()->addDay();
        $createdAt = now();

        $tenantIds = DB::connection('central')
            ->table('tenants')
            ->pluck('id');

        foreach ($tenantIds as $tenantId) {
            DB::connection('central')->table('tenant_cron_jobs')->insert([
                'tenant_id' => $tenantId,
                'task_key' => 'create_leave_muster',
                'schedule_time' => '00:00:00',
                'enabled' => true,
                'next_run_at' => $nextRunAt,
                'last_status' => 'never',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenant_cron_jobs');
    }
};
