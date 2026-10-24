<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\WOTypeEnumn;

class WeekOffChangeCsv
{
    public static function formatWeekDay(int $day): string
    {
        return match ($day) {
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            default => (string) $day,
        };
    }

    public static function parseWeekDay(string $day): ?int
    {
        $day = trim($day);
        if (ctype_digit($day)) {
            $dayNumber = (int) $day;

            return $dayNumber <= 6 ? $dayNumber : null;
        }

        $dayNumber = array_search(strtolower($day), array_map('strtolower', [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
        ]), true);

        return $dayNumber === false ? null : $dayNumber;
    }

    public static function formatWeekOffType(int $type): string
    {
        return match ($type) {
            WOTypeEnumn::FullDay->value => 'Full Day',
            WOTypeEnumn::FirstHalf->value => 'First Half',
            WOTypeEnumn::SecondHalf->value => 'Second Half',
            default => (string) $type,
        };
    }

    public static function parseWeekOffType(string $type): ?int
    {
        $normalized = strtolower(trim($type));
        $validTypes = array_map(static fn (WOTypeEnumn $case): int => $case->value, WOTypeEnumn::cases());
        if (ctype_digit($normalized) && in_array((int) $normalized, $validTypes, true)) {
            return (int) $normalized;
        }

        return match (str_replace([' ', '-', '_'], '', $normalized)) {
            'fullday' => WOTypeEnumn::FullDay->value,
            'firsthalf' => WOTypeEnumn::FirstHalf->value,
            'secondhalf' => WOTypeEnumn::SecondHalf->value,
            default => null,
        };
    }

    public static function formatWeekFlag(bool $value): string
    {
        return $value ? 'Yes' : 'No';
    }

    public static function parseWeekFlag(string $value): ?bool
    {
        return match (strtolower(trim($value))) {
            '1', 'true', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }
}
