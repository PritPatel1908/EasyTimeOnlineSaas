<?php

namespace App\Enums\ReportColumns;

use Filament\Support\Contracts\HasLabel;

enum AbsentReportColumns: string implements HasLabel
{
    case date = 'date';
    case code = 'code';
    case name = 'name';
    case location_name = 'location_name';
    case location_code = 'location_code';
    case company_name = 'company_name';
    case company_code = 'company_code';
    case department_name = 'department_name';
    case department_code = 'department_code';
    case sub_department_name = 'sub_department_name';
    case sub_department_code = 'sub_department_code';
    case category_name = 'category_name';
    case category_code = 'category_code';
    case sub_category_name = 'sub_category_name';
    case sub_category_code = 'sub_category_code';
    case status = 'status';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::date => 'Date',
            self::code => 'User Code',
            self::name => 'User Name',
            self::location_name => 'Location Name',
            self::location_code => 'Location Code',
            self::company_name => 'Company Name',
            self::company_code => 'Company Code',
            self::department_name => 'Department Name',
            self::department_code => 'Department Code',
            self::sub_department_name => 'Sub Department Name',
            self::sub_department_code => 'Sub Department Code',
            self::category_name => 'Category Name',
            self::category_code => 'Category Code',
            self::sub_category_name => 'Sub Category Name',
            self::sub_category_code => 'Sub Category Code',
            self::status => 'Status'
        };
    }
}
