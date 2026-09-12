<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        Grade::create([
            'name' => 'Default Grade',
            'code' => 'Default Grade',
            'location_id' => '1',
        ]);
    }
}
