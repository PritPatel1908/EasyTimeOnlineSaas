<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Default Company 1',
            'code' => 'Default Company 1',
            'location_id' => '1',
        ]);

        Company::create([
            'name' => 'Default Company 2',
            'code' => 'Defaulr Company 2',
            'location_id' => '2',
        ]);
    }
}
