<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        Area::create([
            'code' => 'Default Area',
            'name' => 'Default Area',
            'location_id' => 1,
        ]);
    }
}
