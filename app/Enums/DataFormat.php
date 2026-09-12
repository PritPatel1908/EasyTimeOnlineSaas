<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DataFormat: string implements HasLabel
{
    case vertical = 'vertical';
    case horizontal = 'horizontal';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::vertical => 'Vertical',
            self::horizontal => 'Horizontal',
        };
    }
}
