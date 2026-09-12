<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\SubDepartment;
use Illuminate\Database\Seeder;

class SubDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        SubDepartment::create([
            'name' => 'Default Sub Department 1',
            'code' => 'Default Sub Department 1',
            'location_id' => '1',
            'department_id' => '1',
        ]);

        SubDepartment::create([
            'name' => 'Default Sub Department 2',
            'code' => 'Default Sub Department 2',
            'location_id' => '2',
            'department_id' => '2',
        ]);
    }
}
