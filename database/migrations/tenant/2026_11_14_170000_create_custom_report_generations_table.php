<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_report_generations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('custom_report_id')
                ->constrained('custom_reports')
                ->cascadeOnDelete();
            $table->string('report_format', 10);
            $table->string('report_status', 20)->default('generating');
            $table->string('report_path')->nullable();
            $table->timestamps();

            $table->index(['custom_report_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_report_generations');
    }
};
