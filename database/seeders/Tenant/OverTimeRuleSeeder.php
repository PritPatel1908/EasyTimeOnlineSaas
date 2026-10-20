<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\OvertimeRule;
use Illuminate\Database\Seeder;

class OverTimeRuleSeeder extends Seeder
{
    public function run(): void
    {
        $overtimeRule = OvertimeRule::create([
            'code' => 'Default OverTime Rule',
            'ignore_late_going' => false,
            'ignore_overtime' => 0,
            'ignore_late_going_overtime' => 0,
            'description' => 'Default OverTime Rule Description',
        ]);

        foreach ([['00:00', '01:00', '00:00'], ['01:00', '02:00', '01:00'], ['02:00', '03:00', '02:00']] as [$from, $to, $head]) {
            $overtimeRule->overtime_slabs()->create([
                'value_from' => $from,
                'value_to' => $to,
                'head_value' => $head,
                'overtime_rule_id' => $overtimeRule->id,
            ]);
        }
    }
}
