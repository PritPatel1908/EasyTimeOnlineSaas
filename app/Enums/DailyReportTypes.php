<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DailyReportTypes: string implements HasLabel
{
    case performanceReport = 'performance_report';
    case inOutReport = 'in_out_report';
    case arrivalReport = 'arrival_report';
    case lateComingReport = 'late_coming_report';
    case earlyGoingReport = 'early_going_report';
    case absentReport = 'absent_report';
    case irregularReport = 'irregular_report';
    case overtimeReport = 'overtime_report';
    case presentReport = 'present_report';
    case singlepunchReport = 'singlepunch_report';
    case workhrsReport = 'workhrs_report';
    case summaryReport = 'summary_report';
    case leaveBalanceReport = 'leave_balance_report';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::performanceReport => 'Performance Report',
            self::inOutReport => 'In Out Report',
            self::arrivalReport => 'Arrival Report',
            self::lateComingReport => 'Late Coming Report',
            self::earlyGoingReport => 'Early Going Report',
            self::absentReport => 'Absent Report',
            self::irregularReport => 'Irregular Report',
            self::overtimeReport => 'Overtime Report',
            self::presentReport => 'Present Report',
            self::singlepunchReport => 'Single Punch Report',
            self::workhrsReport => 'Work Hours Report',
            self::summaryReport => 'Summary Report',
            self::leaveBalanceReport => 'Leave Balance Report',
        };
    }
}
