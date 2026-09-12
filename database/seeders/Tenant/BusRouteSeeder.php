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
            'route' => 'Thaltej 1',
            'number' => 'GJ1-hj-7649',
            'driver' => 'Hariom',
            'location_id' => '1',
        ]);
    }
}
