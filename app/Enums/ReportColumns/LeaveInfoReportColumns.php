<?php

namespace App\Enums\ReportColumns;

use Filament\Support\Contracts\HasLabel;

enum LeaveInfoReportColumns: string implements HasLabel
{
    case date = 'date';
    case emp_code = 'emp_code';
    case emp_name = 'emp_name';
    case department_name = 'department_name';
    case department_code = 'department_code';
    case category_name = 'category_name';
    case category_code = 'category_code';
    case leave_type = 'leave_type';
    case from_date = 'from_date';
    case to_date = 'to_date';
    case leave_count = 'leave_count';
    case status = 'status';
    case leave_reason = 'leave_reason';
    case reason_explanation = 'reason_explanation';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::date => 'Date',
            self::emp_code => 'Emp Code',
            self::emp_name => 'Emp Name',
            self::department_name => 'Department Name',
            self::department_code => 'Department Code',
            self::category_name => 'Category Name',
            self::category_code => 'Category Code',
            self::leave_type => 'Leave Type',
            self::from_date => 'From Date',
            self::to_date => 'To Date',
            self::leave_count => 'Leave Count',
            self::status => 'Status',
            self::leave_reason => 'Leave Reason',
            self::reason_explanation => 'Leave Explanation',
        };
    }
}
