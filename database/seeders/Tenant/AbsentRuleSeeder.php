<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\AbsentRule;
use Illuminate\Database\Seeder;

class AbsentRuleSeeder extends Seeder
{
    public function run(): void
    {
        AbsentRule::create([
            'code' => 'Default Absent Rule',
            'description' => 'Default Absent Rule Description',
            'late_coming_minutes' => '10',
            'work_hr_less_than_minutes' => '200',
            'no_of_late' => '3',
            'consecutive_late_coming' => '3',
            'ignore_month_end_late' => true,
            'early_going_minutes' => '10',
            'no_of_early' => '3',
            'consecutive_early_going' => '3',
            'ignore_month_end_early' => true,
        ]);
    }
}
