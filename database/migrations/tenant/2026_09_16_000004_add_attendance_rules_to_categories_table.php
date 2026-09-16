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
        Schema::table('categories', function (Blueprint $table) {
            $table->json('company_id')->nullable()->after('location_id');
            $table->string('canteen_break_limit')->nullable()->after('company_id');
            $table->boolean('need_approval_for_overtime')->nullable()->after('canteen_break_limit');
            $table->boolean('regular_ot_on_wo')->nullable()->after('need_approval_for_overtime');
            $table->boolean('bypass_timing_rule')->nullable()->after('regular_ot_on_wo');
            $table->boolean('ignore_before_after_shift_punch')->nullable()->after('bypass_timing_rule');
            $table->boolean('fix_work_hours')->nullable()->after('ignore_before_after_shift_punch');
            $table->boolean('fix_work_hours_as_per_shift')->nullable()->after('fix_work_hours');
            $table->string('fix_work_hours_value')->nullable()->after('fix_work_hours_as_per_shift');
            $table->boolean('ignore_break_in_attendance')->nullable()->after('fix_work_hours_value');
            $table->boolean('reset_halfday_rule_cycle')->nullable()->after('ignore_break_in_attendance');
            $table->boolean('give_double_ot_in_public_holiday')->nullable()->after('reset_halfday_rule_cycle');
            $table->boolean('give_double_coff_in_public_holiday')->nullable()->after('give_double_ot_in_public_holiday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'company_id',
                'canteen_break_limit',
                'need_approval_for_overtime',
                'regular_ot_on_wo',
                'bypass_timing_rule',
                'ignore_before_after_shift_punch',
                'fix_work_hours',
                'fix_work_hours_as_per_shift',
                'fix_work_hours_value',
                'ignore_break_in_attendance',
                'reset_halfday_rule_cycle',
                'give_double_ot_in_public_holiday',
                'give_double_coff_in_public_holiday',
            ]);
        });
    }
};

