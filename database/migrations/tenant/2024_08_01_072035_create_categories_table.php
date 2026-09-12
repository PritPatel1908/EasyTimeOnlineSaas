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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('email')->nullable();
            $table->json('location_id')->nullable();
            $table->boolean('is_week_off_paid')->nullable();
            $table->boolean('is_holiday_paid')->nullable();
            $table->boolean('single_punch_allowed_present')->nullable();
            $table->boolean('single_punch_allowed_half_day')->nullable();
            $table->integer('max_short_leave_minutes_per_month')->nullable();
            $table->integer('max_short_leave_minutes_per_application')->nullable();
            $table->integer('max_occurance_of_short_leave_in_month')->nullable();
            $table->integer('advance_short_leave_application')->nullable();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
