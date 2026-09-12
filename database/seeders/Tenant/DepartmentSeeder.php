<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create([
            'name' => 'Default Department 1',
            'code' => 'Default Department 1',
            'location_id' => '1',
            'company_id' => '1',
        ]);

        Department::create([
            'name' => 'Default Department 2',
            'code' => 'Default Department 2',
            'location_id' => '2',
            'company_id' => '2',
        ]);
    }
}
