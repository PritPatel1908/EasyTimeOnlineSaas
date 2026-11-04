<?php

namespace App\Support;

use Carbon\Carbon;

class MonthlyLeaveCheckDate
{
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
}
