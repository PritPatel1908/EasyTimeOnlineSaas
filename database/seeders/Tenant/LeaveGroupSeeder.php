<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\LeaveGroup;
use Illuminate\Database\Seeder;

class LeaveGroupSeeder extends Seeder
{
    public function run(): void
    {
        LeaveGroup::create([
            'code' => 'Default Leave Group',
            'description' => 'Default Leave Group Description',
            'location_id' => '1',
            'user_id' => '1',
            'leave_type_id' => '1',
        ]);
    }
}
