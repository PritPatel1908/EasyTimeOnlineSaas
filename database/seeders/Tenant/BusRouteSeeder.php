<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\BusRoute;
use Illuminate\Database\Seeder;

class BusRouteSeeder extends Seeder
{
    public function run(): void
    {
        BusRoute::create([
            'code' => 'Default Bus Route',
            'name' => 'Default Bus Route 1',
            'route' => 'Thaltej 1',
            'number' => 'GJ1-hj-7649',
            'driver' => 'Hariom',
            'location_id' => '1',
        ]);

        BusRoute::create([
            'code' => 'Default Bus Route 2',
            'name' => 'Default Bus Route 2',
            'route' => 'Thaltej 2',
            'number' => 'GJ1-hj-7650',
            'driver' => 'Sample Driver',
            'location_id' => '2',
        ]);
    }
}
