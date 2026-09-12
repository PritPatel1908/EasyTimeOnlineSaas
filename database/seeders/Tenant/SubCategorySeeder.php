<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    public function run(): void
    {
        SubCategory::create([
            'name' => 'Default Sub Category 1',
            'code' => 'Default Sub Category 1',
            'location_id' => '1',
            'category_id' => '1',
        ]);

        SubCategory::create([
            'name' => 'Default Sub Category 2',
            'code' => 'Default Sub Category 2',
            'location_id' => '2',
            'category_id' => '2',
        ]);
    }
}
