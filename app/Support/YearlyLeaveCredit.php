<?php

namespace App\Support;

use App\Models\Tenant\FinancialYear;
use Carbon\Carbon;

class YearlyLeaveCredit
{
    public static function getScheduledCreditDate(Carbon $date, ?FinancialYear $financialYear, ?string $configuredDay): ?Carbon
    {
        if ($financialYear?->start_date === null || $financialYear?->end_date === null || ! is_numeric($configuredDay)) {
            return null;
        }

        $checkDate = $date->copy()->startOfDay();
        $startDate = $financialYear->start_date->copy()->startOfDay();
        $endDate = $financialYear->end_date->copy()->startOfDay();
        $day = (int) $configuredDay;

        if ($day < 1 || $day > $startDate->daysInMonth || ! $checkDate->betweenIncluded($startDate, $endDate)) {
            return null;
        }

        $scheduledDate = $startDate->copy()->day($day);

        return $checkDate->gte($scheduledDate) && $scheduledDate->betweenIncluded($startDate, $endDate)
            ? $scheduledDate
            : null;
    }

    public static function isCreditDate(Carbon $date, ?FinancialYear $financialYear, ?string $configuredDay): bool
    {
        $scheduledDate = self::getScheduledCreditDate($date, $financialYear, $configuredDay);

        return $scheduledDate !== null && $date->isSameDay($scheduledDate);
    }

    public static function shouldCreateCreditMuster(?Carbon $scheduledDate, bool $alreadyExists): bool
    {
        return $scheduledDate !== null && ! $alreadyExists;
    }

    public static function getNextScheduledDate(?Carbon $lastCheckDate): ?Carbon
    {
        return $lastCheckDate?->copy()->addYear();
    }

    public static function getInitialMusterDate(
        Carbon $today,
        ?FinancialYear $financialYear,
        ?string $configuredDay,
        ?Carbon $joinDate,
        bool $hasYearlyCreditRule,
    ): ?Carbon {
        if ($hasYearlyCreditRule) {
            $scheduledDate = self::getScheduledCreditDate($today, $financialYear, $configuredDay);
            if ($scheduledDate !== null
                && ($joinDate === null || ! $joinDate->copy()->startOfDay()->gt($scheduledDate))) {
                return $scheduledDate;
            }
        }

        return $joinDate?->copy();
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

        if ($newJoinerBasedOnCredit !== 'day_based' || $joinDate === null || $joinDate->gt($checkDate)) {
            return null;
        }

        $daysSinceJoining = $joinDate->diffInDays($checkDate);
        if ($creditAfterDays > 0 && $daysSinceJoining <= $creditAfterDays) {
            return null;
        }

        if ($joinDate->copy()->addYear()->lte($checkDate)) {
            return round($annualQuota, 2);
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
}
