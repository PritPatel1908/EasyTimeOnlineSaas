<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\HalfDayRule;
use Illuminate\Database\Seeder;

class HalfDayRuleSeeder extends Seeder
{
    public function run(): void
    {
        HalfDayRule::create([
            'code' => 'Default Half Day Rule',
            'description' => 'Default Half Day Rule Description',
            'late_coming_minutes' => '10',
            'work_hr_less_than_minutes' => '310',
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
