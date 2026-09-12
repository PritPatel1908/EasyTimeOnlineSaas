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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_title')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade');
            $table->string('range')->nullable();
            $table->timestamp('from_datetime')->nullable();
            $table->timestamp('to_datetime')->nullable();
            $table->string('report_type');
            $table->string('report_format');
            $table->tinyInteger('user_type')->default(1); // 1 for employee, 2 for guest, 3 for all
            $table->json('selected_columns')->nullable();
            $table->json('group_by_columns')->nullable();
            $table->json('sort_by_columns')->nullable();
            $table->string('data_format');
            $table->string('report_status')->default('generating');
            $table->string('report_path')->nullable();
            $table->boolean('print_filter_header')->nullable()->default(true);
            $table->string('report_orientation')->nullable()->default('P');
            $table->string('report_size')->nullable()->default('A4');
            $table->integer('font_size')->nullable()->default('9');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
