<?php

namespace App\Support;

use App\Models\Tenant\FinancialYear;
use Carbon\Carbon;

class YearlyLeaveCredit
{
    private const FORTNIGHTLY_PORTION_END_DAY = 15;

    public static function getScheduledCreditDate(
        Carbon $date,
        ?FinancialYear $financialYear,
        ?string $configuredDay,
        ?Carbon $joinDate = null,
    ): ?Carbon {
        if ($financialYear?->start_date === null || $financialYear?->end_date === null || ! is_numeric($configuredDay)) {
            return null;
        }

        $checkDate = $date->copy()->startOfDay();
        $startDate = $financialYear->start_date->copy()->startOfDay();
        $endDate = $financialYear->end_date->copy()->startOfDay();
        $day = (int) $configuredDay;

        if ($day < 1 || $day > 31 || ! $checkDate->betweenIncluded($startDate, $endDate)) {
            return null;
        }

        $scheduledDate = self::getFinancialYearCreditDate($startDate, $endDate, $day, $joinDate);

        return $scheduledDate !== null && $checkDate->gte($scheduledDate)
            ? $scheduledDate
            : null;
    }

    public static function isCreditDate(
        Carbon $date,
        ?FinancialYear $financialYear,
        ?string $configuredDay,
        ?Carbon $joinDate = null,
    ): bool {
        $scheduledDate = self::getScheduledCreditDate($date, $financialYear, $configuredDay, $joinDate);

        return $scheduledDate !== null && $date->isSameDay($scheduledDate);
    }

    public static function getInitialMusterDate(
        Carbon $today,
        ?FinancialYear $financialYear,
        ?string $configuredDay,
        ?Carbon $joinDate,
        bool $hasYearlyCreditRule,
        bool $hasMonthlyCreditRule = false,
    ): ?Carbon {
        if ($hasYearlyCreditRule) {
            if ($financialYear?->start_date !== null
                && $financialYear->end_date !== null
                && is_numeric($configuredDay)
                && (int) $configuredDay >= 1
                && (int) $configuredDay <= 31) {
                $scheduledDate = self::getFinancialYearCreditDate(
                    $financialYear->start_date->copy()->startOfDay(),
                    $financialYear->end_date->copy()->startOfDay(),
                    (int) $configuredDay,
                    $joinDate,
                );

                if ($scheduledDate !== null) {
                    $today = $today->copy()->startOfDay();
                    $joinDate = $joinDate?->copy()->startOfDay();
                    $isNewJoinerInCurrentFinancialYear = $joinDate !== null
                        && $joinDate->betweenIncluded(
                            $financialYear->start_date->copy()->startOfDay(),
                            $financialYear->end_date->copy()->startOfDay(),
                        );

                    if (! $hasMonthlyCreditRule
                        || (! $isNewJoinerInCurrentFinancialYear && $scheduledDate->lte($today))) {
                        return $scheduledDate;
                    }
                }
            }
        }

        return $joinDate?->copy();
    }

    private static function getFinancialYearCreditDate(
        Carbon $financialYearStart,
        Carbon $financialYearEnd,
        int $configuredDay,
        ?Carbon $joinDate,
    ): ?Carbon {
        $joinDate = $joinDate?->copy()->startOfDay();
        $isNewJoiner = $joinDate !== null
            && $joinDate->betweenIncluded($financialYearStart, $financialYearEnd);

        if (! $isNewJoiner) {
            $scheduledDate = self::getConfiguredDateInMonth($financialYearStart, $configuredDay);
        } else {
            $sameMonthDate = self::getConfiguredDateInMonth($joinDate, $configuredDay);
            $scheduledDate = $joinDate->day <= $sameMonthDate->day
                ? $sameMonthDate
                : self::getConfiguredDateInMonth($joinDate->copy()->addMonthNoOverflow()->startOfMonth(), $configuredDay);
        }

        return $scheduledDate;
    }

    private static function getConfiguredDateInMonth(Carbon $date, int $configuredDay): Carbon
    {
        $monthStart = $date->copy()->startOfMonth();

        return $monthStart->day(min($configuredDay, $monthStart->daysInMonth));
    }

    public static function shouldCorrectInitialMusterDate(
        ?Carbon $pendingDate,
        ?Carbon $joinDate,
        ?Carbon $initialDate,
    ): bool {
        return $pendingDate !== null
            && $joinDate !== null
            && $initialDate !== null
            && $pendingDate->isSameDay($joinDate)
            && ! $initialDate->isSameDay($joinDate);
    }

    public static function calculateCredit(
        float $annualQuota,
        ?string $newJoinerBasedOnCredit,
        ?Carbon $joinDate,
        Carbon $checkDate,
        Carbon $financialYearStart,
        Carbon $financialYearEnd,
        int $creditAfterDays,
    ): ?float {
        if ($newJoinerBasedOnCredit === null) {
            return round($annualQuota, 2);
        }

        if (! in_array($newJoinerBasedOnCredit, ['day_based', 'monthly_based', 'fortnightly', 'cutoff_mid'], true)
            || $joinDate === null
            || $joinDate->gt($checkDate)) {
            return null;
        }

        $daysSinceJoining = $joinDate->diffInDays($checkDate);
        if ($creditAfterDays > 0 && $daysSinceJoining <= $creditAfterDays) {
            return null;
        }

        if ($joinDate->copy()->addYear()->lte($checkDate)) {
            return round($annualQuota, 2);
        }

        if ($newJoinerBasedOnCredit === 'monthly_based') {
            if ($joinDate->lt($financialYearStart)) {
                return round($annualQuota, 2);
            }

            if ($joinDate->gt($financialYearEnd)) {
                return null;
            }

            $totalMonths = self::countMonthlyPeriods($financialYearStart, $financialYearEnd);
            $remainingMonths = self::countMonthlyPeriods($joinDate, $financialYearEnd);

            if ($totalMonths <= 0 || $remainingMonths <= 0) {
                return null;
            }

            return round(($annualQuota / $totalMonths) * $remainingMonths, 2);
        }

        if (in_array($newJoinerBasedOnCredit, ['fortnightly', 'cutoff_mid'], true)) {
            if ($joinDate->lt($financialYearStart)) {
                return round($annualQuota, 2);
            }

            if ($joinDate->gt($financialYearEnd)) {
                return null;
            }

            $remainingPeriodStart = $newJoinerBasedOnCredit === 'cutoff_mid'
                && $joinDate->day > self::FORTNIGHTLY_PORTION_END_DAY
                ? $joinDate->copy()->addMonthNoOverflow()->startOfMonth()
                : $joinDate;
            $totalPortions = self::countFortnightlyPeriods($financialYearStart, $financialYearEnd);
            $remainingPortions = self::countFortnightlyPeriods($remainingPeriodStart, $financialYearEnd);
            if ($totalPortions <= 0 || $remainingPortions <= 0) {
                return null;
            }

            return round(($annualQuota / $totalPortions) * $remainingPortions, 2);
        }

        $periodDays = $financialYearStart->diffInDays($financialYearEnd) + 1;
        if ($periodDays <= 0) {
            return null;
        }

        $elapsedDays = $joinDate->lt($financialYearStart)
            ? 0
            : $financialYearStart->diffInDays($joinDate);
        $remainingDays = max(0, $periodDays - $elapsedDays);

        return round(($annualQuota / $periodDays) * $remainingDays, 2);
    }

    private static function countMonthlyPeriods(Carbon $periodStart, Carbon $periodEnd): int
    {
        $periodStart = $periodStart->copy()->startOfDay();
        $periodEnd = $periodEnd->copy()->startOfDay();
        if ($periodStart->gt($periodEnd)) {
            return 0;
        }

        $count = 0;
        $month = $periodStart->copy()->startOfMonth();
        while ($month->lte($periodEnd)) {
            $count++;
            $month->addMonthNoOverflow();
        }

        return $count;
    }

    private static function countFortnightlyPeriods(Carbon $periodStart, Carbon $periodEnd): int
    {
        $periodStart = $periodStart->copy()->startOfDay();
        $periodEnd = $periodEnd->copy()->startOfDay();
        if ($periodStart->gt($periodEnd)) {
            return 0;
        }

        $count = 0;
        $month = $periodStart->copy()->startOfMonth();
        while ($month->lte($periodEnd)) {
            $firstPortionEnd = $month->copy()
                ->day(min(self::FORTNIGHTLY_PORTION_END_DAY, $month->daysInMonth))
                ->endOfDay();
            $secondPortionStart = $firstPortionEnd->copy()->addDay()->startOfDay();
            $monthEnd = $month->copy()->endOfMonth()->endOfDay();

            if ($firstPortionEnd->gte($periodStart) && $month->lte($periodEnd)) {
                $count++;
            }
            if ($monthEnd->gte($periodStart) && $secondPortionStart->lte($periodEnd)) {
                $count++;
            }

            $month->addMonthNoOverflow();
        }

        return $count;
    }
}
