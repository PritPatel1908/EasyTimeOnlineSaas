<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\LateComingRule;
use Illuminate\Database\Seeder;

class LateComingRuleSeeder extends Seeder
{
    public function run(): void
    {
        LateComingRule::create([
            'code' => 'Default Late Coming Rule',
            'ignore_late_coming_minutes' => '10',
            'description' => 'Default Late Coming Rule Description',
        ]);
    }
}
