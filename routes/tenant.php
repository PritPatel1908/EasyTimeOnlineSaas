<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
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
    Route::domain('{tenant}.' . trim((string) $tenantBaseDomain, '.'))->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureActiveDomain::class,
        EnsureValidTenantLicense::class,
    ])->group(function () {
        Route::post('/license', [LicenseController::class, 'update'])->name('tenant.license.update');

        Route::middleware('guest:tenant')->group(function (): void {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('tenant.login');
            Route::post('/login', [AuthController::class, 'login'])->name('tenant.login.submit');
        });

        Route::middleware('auth:tenant')->group(function () {
            Route::get('/', function () {
                return redirect('/dashboard');
            });

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
            Route::get('/notifications/poll', [\App\Http\Controllers\Central\AdminNotificationController::class, 'poll'])->name('tenant.notifications.poll');

            // Redirect legacy/malformed URLs that have a space instead of a hyphen
            // e.g. /company structure/... → /company-structure/...
            Route::get('company structure/{any}', function (string $any) {
                return redirect('company-structure/' . $any, 301);
            })->where('any', '.*');

            Route::prefix('company-structure')->name('tenant.company-structure.')->group(function (): void {
                Route::get('companies/export', [CompanyController::class, 'export'])
                    ->name('companies.export');
                Route::get('companies/import/sample', [CompanyController::class, 'downloadImportSample'])
                    ->name('companies.import-sample');
                Route::get('companies/export/download/{file}', [CompanyController::class, 'downloadExport'])
                    ->name('companies.download-export');
                Route::post('companies/import', [CompanyController::class, 'import'])
                    ->name('companies.import');
                Route::post('companies/filter', [CompanyController::class, 'filterStatus'])
                    ->name('companies.filter');
                Route::get('companies/create', [CompanyController::class, 'create'])
                    ->name('companies.create');
                Route::resource('companies', CompanyController::class)
                    ->except(['create', 'show'])
                    ->names('companies');
            });

            Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
        });

        Route::get('/tenant', function () {
            return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
        });
    });
}
