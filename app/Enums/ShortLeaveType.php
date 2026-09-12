<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ShortLeaveType: int implements HasLabel // , HasIcon
{
    case LateComing = 1;
    case EarlyGoing = 2;
    case BreakBetweenWorkingHours = 3;

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
