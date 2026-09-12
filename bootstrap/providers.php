<?php

use App\Providers\AppServiceProvider;
use App\Providers\FilamentPanelServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    FilamentPanelServiceProvider::class,
    TenancyServiceProvider::class,
];
