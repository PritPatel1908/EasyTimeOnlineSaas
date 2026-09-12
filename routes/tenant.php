<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\LicenseController;
use App\Http\Middleware\EnsureActiveDomain;
use App\Http\Middleware\EnsureValidTenantLicense;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

$tenantBaseDomains = array_values(array_unique(array_filter([
    config('tenancy.base_domains.local'),
    config('tenancy.base_domains.prod'),
])));

foreach ($tenantBaseDomains as $tenantBaseDomain) {
    Route::domain('{tenant}.'.trim((string) $tenantBaseDomain, '.'))->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureActiveDomain::class,
        EnsureValidTenantLicense::class,
    ])->group(function () {
        Route::get('/', function () {
            return redirect('/dashboard');
        });

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        Route::post('/license', [LicenseController::class, 'update'])->name('tenant.license.update');

        Route::prefix('company-structure')->name('tenant.company-structure.')->group(function (): void {
            Route::resource('companies', CompanyController::class)
                ->except(['create', 'show'])
                ->names('companies');
        });

        Route::get('/tenant', function () {
            return 'This is your multi-tenant application. The id of the current tenant is '.tenant('id');
        });
    });
}
