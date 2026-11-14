<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_report_id')
                ->constrained('custom_reports')
                ->cascadeOnDelete();
            $table->json('custom_report_fields');
            $table->json('custom_report_filter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_report_details');
    }
};
