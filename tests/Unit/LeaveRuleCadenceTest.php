<?php

namespace Tests\Unit;

use App\Support\LeaveRuleCadence;
use Tests\TestCase;

class LeaveRuleCadenceTest extends TestCase
{
    public function test_yearly_only_rules_do_not_use_monthly_muster_cadence(): void
    {
        $rules = [(object) ['leave_credit_type' => 'yearly', 'present_count_per' => 'monthly']];

        $this->assertFalse(LeaveRuleCadence::hasMonthlyRules($rules));
        $this->assertTrue(LeaveRuleCadence::hasYearlyRules($rules));
    }

    public function test_mixed_rules_keep_both_monthly_and_yearly_muster_cadences(): void
    {
        $rules = [
            (object) ['leave_credit_type' => 'monthly', 'present_count_per' => 'yearly'],
            (object) ['leave_credit_type' => 'yearly', 'present_count_per' => 'monthly'],
        ];

        $this->assertTrue(LeaveRuleCadence::hasMonthlyRules($rules));
        $this->assertTrue(LeaveRuleCadence::hasYearlyRules($rules));
    }

    public function test_legacy_rules_without_cadence_are_treated_as_monthly(): void
    {
        $rules = [(object) ['leave_credit_type' => null, 'present_count_per' => 'yearly']];

        $this->assertTrue(LeaveRuleCadence::hasMonthlyRules($rules));
        $this->assertFalse(LeaveRuleCadence::hasYearlyRules($rules));
    }

    public function test_manual_credit_rules_are_not_scheduled_as_monthly_or_yearly(): void
    {
        $rules = [(object) ['leave_credit_type' => 'manual']];

        $this->assertFalse(LeaveRuleCadence::hasMonthlyRules($rules));
        $this->assertFalse(LeaveRuleCadence::hasYearlyRules($rules));
    }
}
