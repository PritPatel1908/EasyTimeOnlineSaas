<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatusEnumn: int implements HasIcon, HasLabel
{
    case Active = 1;
    case Inactive = 2;
    case Rejected = 3;
    case Pending = 4;
    case Approved = 5;
    case Running = 6;
    case Close = 7;

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-m-check',
            self::Inactive => 'heroicon-m-x-mark',
        };
    }
}
