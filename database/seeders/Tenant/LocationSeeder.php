<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::create([
            'name' => 'Default Location 1',
            'code' => 'Default Location 1',
        ]);

        Location::create([
            'name' => 'Default Location 2',
            'code' => 'Default Location 2',
        ]);
    }
}
