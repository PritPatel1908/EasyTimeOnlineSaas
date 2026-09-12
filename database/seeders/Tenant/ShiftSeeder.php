<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftRotation;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        Shift::create([
            'name' => 'Shift 1',
            'code' => 'SF1',
            'location_id' => '1',
            'company_id' => '1',
            'in_time' => '09:30:00',
            'out_time' => '18:30:00',
            'first_half_end_time' => '13:59:59',
            'second_half_start_time' => '14:00:00',
            'auto_from' => '09:15:00',
            'auto_to' => '18:44:59',
            'set_cutoff' => true,
            'cutoff_time' => '22:00:00',
        ]);

        $rotation = ShiftRotation::create([
            'code' => 'Shift Rotation 1',
            'start_date' => '2024-11-01',
            'skip_days_on_month_end' => true,
        ]);

        $rotation->rotation_values()->create([
            'shift_rotation_id' => $rotation->id,
            'shift_id' => 1,
            'days' => 31,
        ]);
    }
}
