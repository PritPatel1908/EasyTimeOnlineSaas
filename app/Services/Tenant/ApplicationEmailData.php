<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Enums\WOTypeEnumn;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\EmailTemplate;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class ApplicationEmailData
{
    /**
     * @return class-string<Model>
     */
    public static function applicationClass(string $applicationType): string
    {
        return match ($applicationType) {
            'manual_attendance_application' => ManualAttendanceApplication::class,
            'shift_change_application' => ShiftChangeApplication::class,
            'leave_application' => LeaveApplication::class,
            'short_leave_application' => ShortLeaveApplication::class,
            'coff_application' => CoffApplication::class,
            'week_off_change_application' => WeekOffChangeApplication::class,
            'week_off_swap_application' => WeekOffSwapApplication::class,
            'manual_punch_application' => ManualPunchApplication::class,
            'overtime_application' => OvertimeApplication::class,
            default => throw new RuntimeException('The application type is not supported for email.'),
        };
    }

    public static function applicationType(string $applicationClass): ?string
    {
        return match ($applicationClass) {
            ManualAttendanceApplication::class => 'manual_attendance_application',
            ShiftChangeApplication::class => 'shift_change_application',
            LeaveApplication::class => 'leave_application',
            ShortLeaveApplication::class => 'short_leave_application',
            CoffApplication::class => 'coff_application',
            WeekOffChangeApplication::class => 'week_off_change_application',
            WeekOffSwapApplication::class => 'week_off_swap_application',
            ManualPunchApplication::class => 'manual_punch_application',
            OvertimeApplication::class => 'overtime_application',
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function applicationVariables(Model $application, string $applicationType): array
    {
        if ($application instanceof LeaveApplication) {
            $application->loadMissing(['leave_type', 'leave_reason']);
        }
        if ($application instanceof ShiftChangeApplication) {
            $application->loadMissing(['shifts', 'shift_rotation']);
        }
        if ($application instanceof WeekOffChangeApplication) {
            $application->loadMissing('WeekOffChangeDetails');
        }

        $weekOffDetails = $application instanceof WeekOffChangeApplication
            ? $application->WeekOffChangeDetails
            : collect();

        $variables = [];
        foreach (EmailTemplate::APPLICATION_VARIABLES[$applicationType] as $variable) {
            $key = $variable['key'];
            $value = match ($key) {
                'leave_type_code' => $application instanceof LeaveApplication
                    ? $application->leave_type?->code
                    : null,
                'leave_type' => $application instanceof LeaveApplication
                    ? $application->leave_type?->description
                    : null,
                'leave_reason_code' => $application instanceof LeaveApplication
                    ? $application->leave_reason?->code
                    : null,
                'leave_reason' => $application instanceof LeaveApplication
                    ? $application->leave_reason?->leave_reason
                    : null,
                'reason_explanation' => $application instanceof LeaveApplication
                    ? $application->reason_explanation
                    : null,
                'punch_type' => $application instanceof ManualPunchApplication
                    ? ManualPunch::punchTypeLabel($application->punch_type)
                    : null,
                'shift_names' => $application instanceof ShiftChangeApplication
                    ? $application->shifts->pluck('name')->filter()->implode(', ')
                    : null,
                'shift_rotation_name' => $application instanceof ShiftChangeApplication
                    ? $application->shift_rotation?->code
                    : null,
                'short_leave_type' => $application instanceof ShortLeaveApplication
                    ? match ((int) $application->short_leave_type) {
                        1 => 'Late Coming',
                        2 => 'Early Going',
                        3 => 'Break Between Working Hours',
                        default => '',
                    }
                    : null,
                'week_days' => $weekOffDetails->pluck('week_days')
                    ->map(static fn (mixed $day): string => self::weekDayLabel((int) $day))
                    ->implode(', '),
                'wo_types' => $weekOffDetails->pluck('wo_type')
                    ->map(static fn (mixed $type): string => self::weekOffTypeLabel($type))
                    ->implode(', '),
                'first_week', 'second_week', 'third_week', 'fourth_week', 'fifth_week' => $weekOffDetails
                    ->pluck($key)
                    ->map(static fn (mixed $enabled): string => $enabled ? 'Yes' : 'No')
                    ->implode(', '),
                default => $application->getAttribute($key),
            };
            $variables[$key] = self::formatValue($value, $key);
        }

        return $variables;
    }

    private static function weekDayLabel(int $day): string
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ][$day] ?? (string) $day;
    }

    private static function weekOffTypeLabel(mixed $type): string
    {
        if ($type instanceof WOTypeEnumn) {
            return (string) $type->getLabel();
        }

        return match ((int) $type) {
            WOTypeEnumn::FullDay->value => 'FullDay',
            WOTypeEnumn::FirstHalf->value => 'FirstHalf',
            WOTypeEnumn::SecondHalf->value => 'SecondHalf',
            default => (string) $type,
        };
    }

    private static function formatValue(mixed $value, string $key): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof CarbonInterface) {
            if (in_array($key, ['date', 'punch_date', 'application_date', 'from_date', 'to_date', 'week_date', 'swap_date', 'coff_against_date', 'coff_from_date', 'coff_to_date'], true)) {
                return $value->format('Y-m-d');
            }

            return in_array($key, ['from_time', 'to_time', 'punch_time', 'in_time', 'out_time'], true)
                ? $value->format('H:i:s')
                : $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return implode(', ', array_map(static fn (mixed $item): string => (string) $item, $value));
        }

        return (string) $value;
    }
}
