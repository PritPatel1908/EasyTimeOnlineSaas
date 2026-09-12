<?php

namespace App\Traits;

use Filament\Tables;

trait CUResource
{
    public static function tablecudby(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_by.name')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_by.name')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
