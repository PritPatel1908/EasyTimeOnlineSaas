<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::create([
            'name' => 'Default Unit 1',
            'code' => 'Default Unit 1',
            'location_id' => '1',
        ]);
    }
}
