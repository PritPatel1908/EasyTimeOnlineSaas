<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReportStatus: string implements HasColor, HasIcon, HasLabel
{
    case generating = 'generating';
    case error = 'error';
    case generated = 'generated';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::generating => 'Generating',
            self::error => 'Error',
            self::generated => 'Generated',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::generating => 'gold',
            self::error => 'red',
            self::generated => 'green',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::generating => 'heroicon-o-clock',
            self::error => 'heroicon-o-information-circle',
            self::generated => 'heroicon-m-check',
        };
    }
}
