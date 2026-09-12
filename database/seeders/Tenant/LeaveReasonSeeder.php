<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\LeaveReason;
use Illuminate\Database\Seeder;

class LeaveReasonSeeder extends Seeder
{
    public function run(): void
    {
        LeaveReason::create(['leave_reason' => 'Personal', 'status' => true]);
        LeaveReason::create(['leave_reason' => 'Family Function', 'status' => true]);
    }
}
