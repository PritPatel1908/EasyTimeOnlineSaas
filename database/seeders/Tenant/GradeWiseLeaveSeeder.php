<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\GradeWiseLeave;
use Illuminate\Database\Seeder;

class GradeWiseLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $gradeWiseLeave = GradeWiseLeave::create([
            'name' => 'Annual Leave',
            'financial_year_id' => 1,
            'is_monthly' => true,
        ]);

        $gradeWiseLeave->grade_wise_monthly_leave_details()->create([
            'grade_wise_leave_id' => $gradeWiseLeave->id,
            'leave_type_id' => 3,
            'allow_c_f' => true,
            'increment' => 2,
            'proportionate' => 1,
            'allow_leave_encashment' => true,
            'count_weekly_off_as_present' => true,
            'count_holiday_as_present' => true,
            'start_credit' => 1,
            'credit_after_days' => 0,
            'start_use_based_on' => 1,
            'start_use_after_days' => 6,
            'allow_sandwich' => true,
            'sandwich_leave_id' => 6,
            'allow_backdate_leave' => true,
            'backdate_day_limit' => 2,
            'allow_advance_leave' => true,
            'advance_day_limit' => 4,
            'max_accumulation' => 20,
            'is_paid' => true,
            'allow_negative_balance' => true,
            'negative_balance' => -1,
        ]);
    }
}
