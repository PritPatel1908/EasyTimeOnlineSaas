<?php

namespace Tests\Unit;

use App\Models\Tenant\FinancialYear;
use App\Support\YearlyLeaveCredit;
use Carbon\Carbon;
use Tests\TestCase;

class YearlyLeaveCreditTest extends TestCase
{
    public function test_yearly_check_date_must_match_financial_year_start_month_and_configured_day(): void
    {
        $financialYear = new FinancialYear([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertTrue(YearlyLeaveCredit::isCreditDate(Carbon::parse('2026-01-15'), $financialYear, '15'));
        $this->assertFalse(YearlyLeaveCredit::isCreditDate(Carbon::parse('2026-02-15'), $financialYear, '15'));
        $this->assertFalse(YearlyLeaveCredit::isCreditDate(Carbon::parse('2026-01-14'), $financialYear, '15'));
    }

    public function test_yearly_credit_date_is_caught_up_after_the_configured_day(): void
    {
        $financialYear = new FinancialYear([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertSame(
            '2026-01-15',
            YearlyLeaveCredit::getScheduledCreditDate(Carbon::parse('2026-01-18'), $financialYear, '15')?->format('Y-m-d'),
        );
        $this->assertNull(YearlyLeaveCredit::getScheduledCreditDate(
            Carbon::parse('2026-01-14'),
            $financialYear,
            '15',
        ));
        $this->assertNull(YearlyLeaveCredit::getScheduledCreditDate(
            Carbon::parse('2027-01-18'),
            $financialYear,
            '15',
        ));
    }

    public function test_existing_employee_yearly_credit_uses_financial_year_start_month(): void
    {
        $scheduledDate = YearlyLeaveCredit::getScheduledCreditDate(
            Carbon::parse('2026-11-04'),
            new FinancialYear([
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
            ]),
            '4',
        );

        $this->assertSame('2026-04-04', $scheduledDate?->format('Y-m-d'));
    }

    public function test_initial_muster_uses_due_yearly_credit_date_instead_of_join_date(): void
    {
        $initialDate = YearlyLeaveCredit::getInitialMusterDate(
            Carbon::parse('2026-04-04'),
            new FinancialYear([
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
            ]),
            '4',
            Carbon::parse('2022-06-01'),
            true,
        );

        $this->assertSame('2026-04-04', $initialDate?->format('Y-m-d'));
    }

    public function test_initial_muster_uses_financial_year_credit_date_for_existing_employees(): void
    {
        $initialDate = YearlyLeaveCredit::getInitialMusterDate(
            Carbon::parse('2026-04-02'),
            new FinancialYear([
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
            ]),
            '4',
            Carbon::parse('2022-06-01'),
            true,
        );

        $this->assertSame('2026-04-04', $initialDate?->format('Y-m-d'));
    }

    public function test_initial_muster_uses_join_month_or_next_month_configured_date_for_new_joiners(): void
    {
        $initialDate = YearlyLeaveCredit::getInitialMusterDate(
            Carbon::parse('2026-04-04'),
            new FinancialYear([
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
            ]),
            '4',
            Carbon::parse('2026-10-22'),
            true,
        );

        $this->assertSame('2026-11-04', $initialDate?->format('Y-m-d'));
    }

    public function test_initial_muster_keeps_join_date_for_mixed_monthly_and_yearly_rules(): void
    {
        $initialDate = YearlyLeaveCredit::getInitialMusterDate(
            Carbon::parse('2026-04-04'),
            new FinancialYear([
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
            ]),
            '4',
            Carbon::parse('2026-10-22'),
            true,
            true,
        );

        $this->assertSame('2026-10-22', $initialDate?->format('Y-m-d'));
    }

    public function test_only_pending_initial_join_date_musters_are_corrected_to_the_yearly_date(): void
    {
        $joinDate = Carbon::parse('2022-06-01');
        $yearlyDate = Carbon::parse('2026-04-04');

        $this->assertTrue(YearlyLeaveCredit::shouldCorrectInitialMusterDate(
            Carbon::parse('2022-06-01'),
            $joinDate,
            $yearlyDate,
        ));
        $this->assertFalse(YearlyLeaveCredit::shouldCorrectInitialMusterDate(
            Carbon::parse('2026-02-01'),
            $joinDate,
            $yearlyDate,
        ));
        $this->assertFalse(YearlyLeaveCredit::shouldCorrectInitialMusterDate(
            Carbon::parse('2022-06-01'),
            $joinDate,
            $joinDate,
        ));
    }

    public function test_new_joiner_check_date_uses_joining_month_when_configured_day_is_upcoming(): void
    {
        $financialYear = new FinancialYear([
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
        ]);
        $joinDate = Carbon::parse('2026-06-04');

        $this->assertTrue(YearlyLeaveCredit::isCreditDate(
            Carbon::parse('2026-06-05'),
            $financialYear,
            '5',
            $joinDate,
        ));
    }

    public function test_new_joiner_check_date_moves_to_next_month_when_joining_after_configured_day(): void
    {
        $financialYear = new FinancialYear([
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
        ]);
        $joinDate = Carbon::parse('2026-06-25');

        $this->assertFalse(YearlyLeaveCredit::isCreditDate(
            Carbon::parse('2026-06-05'),
            $financialYear,
            '5',
            $joinDate,
        ));
        $this->assertTrue(YearlyLeaveCredit::isCreditDate(
            Carbon::parse('2026-07-05'),
            $financialYear,
            '5',
            $joinDate,
        ));
    }

    public function test_new_joiner_joining_on_june_fourth_is_eligible_for_the_june_twenty_fifth_credit(): void
    {
        $financialYear = new FinancialYear([
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
        ]);
        $joinDate = Carbon::parse('2026-06-04');
        $checkDate = Carbon::parse('2026-06-25');

        $this->assertFalse(YearlyLeaveCredit::isCreditDate(
            Carbon::parse('2026-06-04'),
            $financialYear,
            '25',
            $joinDate,
        ));
        $this->assertTrue(YearlyLeaveCredit::isCreditDate(
            $checkDate,
            $financialYear,
            '25',
            $joinDate,
        ));
        $this->assertSame(
            20.0,
            YearlyLeaveCredit::calculateCredit(
                24,
                'monthly_based',
                $joinDate,
                $checkDate,
                $financialYear->start_date->copy(),
                $financialYear->end_date->copy(),
                0,
            ),
        );
    }

    public function test_null_new_joiner_mode_credits_the_full_annual_quota(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            12,
            null,
            null,
            Carbon::parse('2026-01-15'),
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-12-31'),
            30,
        );

        $this->assertSame(12.0, $credit);
    }

    public function test_day_based_new_joiner_is_prorated_after_credit_after_days(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            12,
            'day_based',
            Carbon::parse('2026-01-18'),
            Carbon::parse('2026-01-30'),
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-12-31'),
            10,
        );

        $this->assertSame(11.44, $credit);
    }

    public function test_day_based_new_joiner_is_not_credited_before_credit_after_days(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            12,
            'day_based',
            Carbon::parse('2026-01-18'),
            Carbon::parse('2026-01-28'),
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-12-31'),
            10,
        );

        $this->assertNull($credit);
    }

    public function test_monthly_based_new_joiner_receives_credit_for_joining_month_through_financial_year_end(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'monthly_based',
            Carbon::parse('2026-06-15'),
            Carbon::parse('2026-06-25'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2027-03-31'),
            0,
        );

        $this->assertSame(20.0, $credit);
    }

    public function test_monthly_based_new_joiner_respects_credit_after_days(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'monthly_based',
            Carbon::parse('2026-06-15'),
            Carbon::parse('2026-06-25'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2027-03-31'),
            10,
        );

        $this->assertNull($credit);
    }

    public function test_monthly_based_new_joiner_before_financial_year_receives_full_quota(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'monthly_based',
            Carbon::parse('2026-03-31'),
            Carbon::parse('2026-04-04'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2027-03-31'),
            0,
        );

        $this->assertSame(24.0, $credit);
    }

    public function test_fortnightly_new_joiner_receives_credit_for_joining_portion_through_financial_year_end(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'fortnightly',
            Carbon::parse('2026-06-21'),
            Carbon::parse('2026-06-25'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2027-03-31'),
            0,
        );

        $this->assertSame(19.0, $credit);
    }

    public function test_fortnightly_joiner_on_fifteenth_receives_credit_for_first_half_month_portion(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'fortnightly',
            Carbon::parse('2026-06-15'),
            Carbon::parse('2026-06-25'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2027-03-31'),
            0,
        );

        $this->assertSame(20.0, $credit);
    }

    public function test_fortnightly_credit_uses_financial_year_month_count(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'fortnightly',
            Carbon::parse('2026-06-21'),
            Carbon::parse('2026-06-25'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2026-12-31'),
            0,
        );

        $this->assertSame(17.33, $credit);
    }

    public function test_cutoff_mid_joiner_after_fifteenth_starts_credit_from_next_month(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            24,
            'cutoff_mid',
            Carbon::parse('2026-06-21'),
            Carbon::parse('2026-06-25'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2027-03-31'),
            0,
        );

        $this->assertSame(18.0, $credit);
    }

    public function test_based_credits_count_only_periods_that_overlap_financial_year_dates(): void
    {
        $financialYearStart = Carbon::parse('2026-04-20');
        $financialYearEnd = Carbon::parse('2026-12-20');
        $joinDate = Carbon::parse('2026-06-21');
        $checkDate = Carbon::parse('2026-06-25');

        $monthlyCredit = YearlyLeaveCredit::calculateCredit(
            24,
            'monthly_based',
            $joinDate,
            $checkDate,
            $financialYearStart,
            $financialYearEnd,
            0,
        );
        $fortnightlyCredit = YearlyLeaveCredit::calculateCredit(
            24,
            'fortnightly',
            $joinDate,
            $checkDate,
            $financialYearStart,
            $financialYearEnd,
            0,
        );

        $this->assertSame(18.67, $monthlyCredit);
        $this->assertSame(18.35, $fortnightlyCredit);
    }

    public function test_employee_past_first_anniversary_receives_full_quota(): void
    {
        $credit = YearlyLeaveCredit::calculateCredit(
            12,
            'day_based',
            Carbon::parse('2025-01-15'),
            Carbon::parse('2026-01-15'),
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-12-31'),
            0,
        );

        $this->assertSame(12.0, $credit);
    }
}
