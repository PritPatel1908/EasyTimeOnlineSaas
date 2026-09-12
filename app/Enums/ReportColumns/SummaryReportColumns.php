<?php

namespace App\Enums\ReportColumns;

use Filament\Support\Contracts\HasLabel;

enum SummaryReportColumns: string implements HasLabel
{
    case department_name = 'department_name';
    case department_code = 'department_code';
    case present_count = 'present_count';
    case absent_count = 'absent_count';
    case week_off_count = 'week_off_count';
    case holiday_count = 'holiday_count';
    case full_day_leave_count = 'full_day_leave_count';
    case first_half_leave_count = 'first_half_leave_count';
    case second_half_leave_count = 'second_half_leave_count';
    case total = 'total';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::department_name => 'Department Name',
            self::department_code => 'Department Code',
            self::present_count => 'Present Count',
            self::absent_count => 'Absent Count',
            self::week_off_count => 'Week Off Count',
            self::holiday_count => 'Holiday Count',
            self::full_day_leave_count => 'Full Day Leave Count',
            self::first_half_leave_count => 'First Half Leave Count',
            self::second_half_leave_count => 'Second Half Leave Count',
            self::total => 'Total',
        };
    }
}
