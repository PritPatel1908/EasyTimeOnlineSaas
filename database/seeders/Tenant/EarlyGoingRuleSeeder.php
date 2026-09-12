<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\EarlyGoingRule;
use Illuminate\Database\Seeder;

class EarlyGoingRuleSeeder extends Seeder
{
    public function run(): void
    {
        EarlyGoingRule::create([
            'code' => 'Default Early Going Rule',
            'ignore_early_going_minutes' => '10',
            'description' => 'Default Early Going Rule Description',
        ]);
    }
}
