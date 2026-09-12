<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holiday = Holiday::create([
            'name' => 'Uttarayan',
            'date' => '2025-01-14',
        ]);

        $holiday->locations()->sync([1, 2]);
        $holiday->categories()->sync([1, 2]);
        $holiday->shifts()->sync([1]);
    }
}
