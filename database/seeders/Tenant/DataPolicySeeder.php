<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\DataPolicy;
use Illuminate\Database\Seeder;

class DataPolicySeeder extends Seeder
{
    public function run(): void
    {
        DataPolicy::create([
            'code' => 'Default Data Policy',
            'name' => 'Default Data Policy',
        ]);
    }
}
