<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        Designation::create([
            'name' => 'Default Designation',
            'code' => 'Default Designation',
            'location_id' => '1',
            'company_id' => '1',
            'category_id' => '1',
        ]);
    }
}
