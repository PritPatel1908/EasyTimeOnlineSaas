<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Panel;
use Filament\PanelProvider;

class FilamentPanelServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('filament')
            ->authGuard('tenant');
    }
}
