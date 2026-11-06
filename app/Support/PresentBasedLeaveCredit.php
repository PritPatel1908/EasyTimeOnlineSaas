<?php

namespace App\Support;

use Carbon\Carbon;

class PresentBasedLeaveCredit
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function getAttendancePeriod(
        string $presentCountPer,
        Carbon $creditDate,
        Carbon $financialYearStart,
    ): array {
        if ($presentCountPer === 'yearly') {
            return [
                $financialYearStart->copy()->startOfDay(),
                $creditDate->copy()->startOfDay(),
            ];
        }

        $previousMonth = $creditDate->copy()->subMonthNoOverflow();

        return [
            $previousMonth->copy()->startOfMonth(),
            $previousMonth->copy()->endOfMonth(),
        ];
    }

    public static function countPresentAttendanceDays(
        iterable $attendanceRows,
        array $includedLeaveTypeCodes,
        bool $countWeekOff,
        bool $countHoliday,
    ): float {
        $presentCodes = ['PP', 'PA', 'AP'];
        if ($countWeekOff) {
            $presentCodes = array_merge($presentCodes, ['WOP', 'PWO', 'WHP']);
        }
        if ($countHoliday) {
            $presentCodes[] = 'WHP';
        }

        $leaveCodes = [];
        foreach (array_unique($includedLeaveTypeCodes) as $leaveTypeCode) {
            $leaveCodes[$leaveTypeCode] = 1.0;
            $leaveCodes['A'.$leaveTypeCode] = 0.5;
            $leaveCodes[$leaveTypeCode.'A'] = 0.5;
            $leaveCodes['P'.$leaveTypeCode] = 1.0;
            $leaveCodes[$leaveTypeCode.'P'] = 1.0;
        }

        $presentDays = 0.0;
        foreach ($attendanceRows as $attendance) {
            $code = $attendance->code;
            if (in_array($code, ['PA', 'AP'], true)) {
                $presentDays += 0.5;

                continue;
            }

            if (in_array($code, $presentCodes, true)) {
                $presentDays += $attendance->is_half_day ? 0.5 : 1.0;

                continue;
            }

            if (! array_key_exists($code, $leaveCodes)) {
                continue;
            }

            $presentDays += $leaveCodes[$code];
        }

        return $presentDays;
    }

    public static function calculate(
        float $monthlyCredit,
        float $presentDays,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?int $presentCountThreshold = null,
        ?float $thresholdCreditDays = null,
    ): float {
        if ($presentCountThreshold !== null && $presentCountThreshold > 0 && $thresholdCreditDays !== null) {
            if ($presentDays < $presentCountThreshold) {
                return 0;
            }

            return $presentDays === (float) $presentCountThreshold
                ? round($thresholdCreditDays, 2)
                : round($monthlyCredit, 2);
        }

        $periodDays = $periodStart->diffInDays($periodEnd) + 1;
        if ($periodDays <= 0) {
            return 0;
        }

        return round(($monthlyCredit / $periodDays) * $presentDays, 2);
    }
}
