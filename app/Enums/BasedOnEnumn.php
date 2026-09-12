<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BasedOnEnumn: int implements HasLabel // , HasIcon
{
    case Day = 1;
    case Fortnightly = 2;
    case Monthly = 3;
    case CutoffMid = 4;

    public function getLabel(): ?string
    {
        return $this->name;
    }

    // public function getIcon(): ?string
    // {
    //     return match ($this) {
    //         self::FullDay => 'heroicon-m-check',
    //         self::FirstHalf => 'heroicon-m-x-mark',
    //         self::SecondHalf => 'heroicon-m-x-mark',
    //     };
    // }
}
