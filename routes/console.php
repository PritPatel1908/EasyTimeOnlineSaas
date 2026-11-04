<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('tenants:run app:create-leave-muster')
    ->daily()
    ->withoutOverlapping();

Schedule::command('tenants:run app:check-leave-balance')
    ->daily()
    ->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
