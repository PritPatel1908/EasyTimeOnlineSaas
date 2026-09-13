<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\LocationController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\LicenseController;
use App\Http\Controllers\Tenant\RolePermissionController;
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
    $tenantRouteNamePrefix = $tenantBaseDomain === $tenantBaseDomains[0]
        ? ''
        : 'tenant.' . str_replace(['.', '-'], '_', trim((string) $tenantBaseDomain, '.')) . '.';

    Route::domain('{tenant}.' . trim((string) $tenantBaseDomain, '.'))
        ->name($tenantRouteNamePrefix)
        ->middleware([
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
                Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('tenant.notifications.show');

                Route::prefix('roles')->name('tenant.roles.')->group(function (): void {
                    Route::get('/', [RolePermissionController::class, 'index'])->name('index');
                    Route::post('/', [RolePermissionController::class, 'store'])->name('store');
                    Route::put('/{role}', [RolePermissionController::class, 'update'])->name('update');
                    Route::delete('/{role}', [RolePermissionController::class, 'destroy'])->name('destroy');
                    Route::get('/{role}/permissions', [RolePermissionController::class, 'permissions'])->name('permissions');
                    Route::put('/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])->name('permissions.update');
                });

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
                    Route::get('locations/export', [LocationController::class, 'export'])
                        ->name('locations.export');
                    Route::get('locations/import/sample', [LocationController::class, 'downloadImportSample'])
                        ->name('locations.import-sample');
                    Route::get('locations/export/download/{file}', [LocationController::class, 'downloadExport'])
                        ->name('locations.download-export');
                    Route::post('locations/import', [LocationController::class, 'import'])
                        ->name('locations.import');
                    Route::post('locations/filter', [LocationController::class, 'filterStatus'])
                        ->name('locations.filter');
                    Route::get('locations/create', [LocationController::class, 'create'])
                        ->name('locations.create');
                    Route::resource('locations', LocationController::class)
                        ->except(['create', 'show'])
                        ->names('locations');
                });

                Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
            });

            Route::get('/tenant', function () {
                return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
            });
        });
}
