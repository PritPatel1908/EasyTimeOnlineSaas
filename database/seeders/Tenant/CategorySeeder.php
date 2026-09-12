<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'name' => 'Default Category 1',
            'code' => 'Default Category 1',
            'location_id' => '1',
            'is_week_off_paid' => true,
            'is_holiday_paid' => true,
            'single_punch_allowed_present' => true,
            'single_punch_allowed_half_day' => true,
            'max_short_leave_minutes_per_month' => '90',
            'max_short_leave_minutes_per_application' => '90',
            'max_occurance_of_short_leave_in_month' => '3',
            'advance_short_leave_application' => '3',
        ]);

        Category::create([
            'name' => 'Default Category 2',
            'code' => 'Default Category 2',
            'location_id' => '2',
            'is_week_off_paid' => true,
            'is_holiday_paid' => true,
            'single_punch_allowed_present' => true,
            'single_punch_allowed_half_day' => true,
            'max_short_leave_minutes_per_month' => '180',
            'max_short_leave_minutes_per_application' => '60',
            'max_occurance_of_short_leave_in_month' => '6',
            'advance_short_leave_application' => '3',
        ]);
    }
}
