<?php

namespace App\Support;

class LeaveRuleCadence
{
    public static function hasMonthlyRules(iterable $rules): bool
    {
        foreach ($rules as $rule) {
            if (($rule->leave_credit_type ?? 'monthly') === 'monthly') {
                return true;
            }
        }

        return false;
    }

    public static function hasYearlyRules(iterable $rules): bool
    {
        foreach ($rules as $rule) {
            if (($rule->leave_credit_type ?? 'monthly') === 'yearly') {
                return true;
            }
        }

        return false;
    }
}
