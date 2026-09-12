<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['SL', 'CL', 'PL', 'CO', 'OL', 'LWP', 'L1', 'L2', 'L3', 'L4', 'L5'] as $code) {
            LeaveType::create([
                'code' => $code,
                'description' => 'Leave Type Is '.$code,
                'location_id' => '1',
            ]);
        }
    }
}
