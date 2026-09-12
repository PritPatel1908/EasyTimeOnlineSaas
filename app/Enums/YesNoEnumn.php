<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum YesNoEnumn: int implements HasLabel // , HasIcon
{
    case Yes = 1;
    case No = 2;

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
