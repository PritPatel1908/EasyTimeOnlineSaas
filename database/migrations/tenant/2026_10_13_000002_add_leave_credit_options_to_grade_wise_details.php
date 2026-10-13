<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->integer('eligible_after_days')->nullable();
            $table->decimal('min_avail', 10, 2)->nullable();
            $table->decimal('max_avail', 10, 2)->nullable();
            $table->json('leave_types_not_to_be_clubbed')->nullable();
            $table->string('leave_credit_type')->default('monthly');
        });

        Schema::table('grade_wise_yearly_leave_details', function (Blueprint $table) {
            $table->integer('eligible_after_days')->nullable();
            $table->integer('credit_after_days')->nullable();
            $table->decimal('min_avail', 10, 2)->nullable();
            $table->decimal('max_avail', 10, 2)->nullable();
            $table->json('leave_types_not_to_be_clubbed')->nullable();
            $table->string('leave_credit_type')->default('yearly');
        });
    }

    public function down(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->dropColumn([
                'eligible_after_days',
                'min_avail',
                'max_avail',
                'leave_types_not_to_be_clubbed',
                'leave_credit_type',
            ]);
        });

        Schema::table('grade_wise_yearly_leave_details', function (Blueprint $table) {
            $table->dropColumn([
                'eligible_after_days',
                'credit_after_days',
                'min_avail',
                'max_avail',
                'leave_types_not_to_be_clubbed',
                'leave_credit_type',
            ]);
        });
    }
};
