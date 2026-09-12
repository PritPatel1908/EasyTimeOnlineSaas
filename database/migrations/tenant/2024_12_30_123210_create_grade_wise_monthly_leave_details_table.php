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
        Schema::create('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_wise_leave_id')->nullable()->constrained('grade_wise_leaves');
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->boolean('allow_c_f')->nullable();
            $table->integer('increment')->nullable();
            $table->tinyInteger('proportionate')->nullable();
            $table->boolean('count_weekly_off_as_present')->default(false);
            $table->boolean('count_holiday_as_present')->default(false);
            $table->tinyInteger('start_credit')->nullable();
            $table->integer('credit_after_days')->nullable();
            $table->tinyInteger('start_use_based_on')->nullable();
            $table->integer('start_use_after_days')->nullable();
            $table->boolean('allow_sandwich')->default(false);
            $table->foreignId('sandwich_leave_id')->nullable()->constrained('leave_types');
            $table->tinyInteger('max_accumulation')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('allow_negative_balance')->default(false);
            $table->decimal('negative_balance', 10, 2)->default(0);
            $table->boolean('allow_backdate_leave')->default(false);
            $table->integer('backdate_day_limit')->nullable();
            $table->boolean('allow_advance_leave')->default(false);
            $table->integer('advance_day_limit')->nullable();
            $table->boolean('allow_leave_encashment')->default(false);
            $table->boolean('allow_leave_lapse')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_wise_yearly_leave_details');
    }
};
