<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReportFormat: string implements HasLabel
{
    case csv = 'csv';
    case pdf = 'pdf';
    case xlsx = 'xlsx';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::csv => 'CSV',
            self::pdf => 'PDF',
            self::xlsx => 'XLSX',
        };
    }
}
