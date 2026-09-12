<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GroupableColumns: string implements HasLabel
{
    // case canteen = 'canteen';
    case company = 'company';
    case department = 'department';
    case sub_department = 'sub_department';
    case category = 'category';
    case sub_category = 'sub_category';
    // case user = 'user';

    public function getLabel(): ?string
    {
        return match ($this) {
            // self::canteen => 'Canteen',
            self::company => 'Company',
            self::department => 'Department',
            self::sub_department => 'Sub Department',
            self::category => 'Category',
            self::sub_category => 'Sub Category',
            // self::user => 'User',
        };
    }
}
