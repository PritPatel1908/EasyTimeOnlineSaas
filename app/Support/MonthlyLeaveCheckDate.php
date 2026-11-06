<?php

namespace App\Support;

use Carbon\Carbon;

class MonthlyLeaveCheckDate
{
    public static function getNextScheduledDate(
        Carbon $lastCheckDate,
        Carbon $today,
        ?string $configuredDay,
    ): ?Carbon {
        if (! is_numeric($configuredDay)) {
            return $lastCheckDate->copy()->addMonthNoOverflow();
        }

        $day = (int) $configuredDay;
        if ($day < 1 || $day > 31) {
            return null;
        }

        $scheduledDate = self::getConfiguredDateInMonth($today, $day);
        if ($scheduledDate->lte($lastCheckDate->copy()->startOfDay())) {
            $scheduledDate = self::getConfiguredDateInMonth(
                $today->copy()->addMonthNoOverflow()->startOfMonth(),
                $day,
            );
        }

        return $scheduledDate;
    }

    public static function getScheduledDate(Carbon $date, ?string $configuredDay): ?Carbon
    {
        if (! is_numeric($configuredDay)) {
            return null;
        }

        $day = (int) $configuredDay;
        if ($day < 1 || $day > 31) {
            return null;
        }

        $scheduledDate = $date->copy()->startOfMonth()->day(min($day, $date->daysInMonth));

        return $date->copy()->startOfDay()->gte($scheduledDate)
            ? $scheduledDate
            : null;
    }

    private static function getConfiguredDateInMonth(Carbon $date, int $day): Carbon
    {
        $monthStart = $date->copy()->startOfMonth();

        return $monthStart->day(min($day, $monthStart->daysInMonth));
    }
}
