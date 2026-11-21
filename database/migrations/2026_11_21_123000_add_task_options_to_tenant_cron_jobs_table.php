<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->table('tenant_cron_jobs', function (Blueprint $table): void {
            $table->string('name')->default('Leave Muster');
            $table->string('schedule_type', 16)->default('daily');
            $table->unsignedSmallInteger('interval_minutes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('tenant_cron_jobs', function (Blueprint $table): void {
            $table->dropColumn(['name', 'schedule_type', 'interval_minutes']);
        });
    }
};
