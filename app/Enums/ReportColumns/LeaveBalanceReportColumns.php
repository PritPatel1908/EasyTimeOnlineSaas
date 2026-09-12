<?php

namespace App\Enums\ReportColumns;

use Filament\Support\Contracts\HasLabel;

enum LeaveBalanceReportColumns: string implements HasLabel
{
    case date = 'date';
    case emp_code = 'emp_code';
    case emp_name = 'emp_name';
    case department_name = 'department_name';
    case department_code = 'department_code';
    case category_name = 'category_name';
    case category_code = 'category_code';
    case leave_type = 'leave_type';
    case opening_balance = 'opening_balance';
    case credit = 'credit';
    case debit = 'debit';
    case balance = 'balance';
    case remarks = 'remarks';

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
            self::opening_balance => 'Opening Balance',
            self::credit => 'Credit',
            self::debit => 'Debit',
            self::balance => 'Balance',
            self::remarks => 'Remarks'
        };
    }
}
