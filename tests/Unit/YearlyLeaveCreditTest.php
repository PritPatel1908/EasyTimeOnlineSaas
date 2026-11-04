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

    public function test_missing_yearly_credit_muster_is_created_even_if_a_later_date_was_already_checked(): void
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
        $this->assertTrue(YearlyLeaveCredit::shouldCreateCreditMuster($scheduledDate, false));
        $this->assertFalse(YearlyLeaveCredit::shouldCreateCreditMuster($scheduledDate, true));
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

    public function test_initial_muster_keeps_join_date_when_yearly_credit_date_is_not_due(): void
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

        $this->assertSame('2022-06-01', $initialDate?->format('Y-m-d'));
    }

    public function test_initial_muster_does_not_use_yearly_date_before_employee_join_date(): void
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

    public function test_next_yearly_check_date_is_one_year_after_last_checked_date(): void
    {
        $nextDate = YearlyLeaveCredit::getNextScheduledDate(Carbon::parse('2026-04-04'));

        $this->assertSame('2027-04-04', $nextDate?->format('Y-m-d'));
        $this->assertNull(YearlyLeaveCredit::getNextScheduledDate(null));
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
