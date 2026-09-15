<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\SubDepartmentController;
use App\Http\Controllers\Tenant\DataPolicyController;
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
                Route::post('/notifications/mark-read', [\App\Http\Controllers\Central\AdminNotificationController::class, 'markRead'])->name('tenant.notifications.mark-read');
                Route::get('/notifications', [NotificationController::class, 'index'])->name('tenant.notifications.index');
                Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('tenant.notifications.show');
                Route::get('data-policy', [DataPolicyController::class, 'index'])
                    ->middleware('tenant.permission:DataPolicy,read')
                    ->name('tenant.data-policy');
                Route::get('data-policy/create', [DataPolicyController::class, 'create'])
                    ->middleware('tenant.permission:DataPolicy,create')
                    ->name('tenant.data-policy.create');
                Route::resource('data-policy', DataPolicyController::class)
                    ->except(['index', 'create', 'show'])
                    ->middleware([
                        'store' => 'tenant.permission:DataPolicy,create',
                        'edit' => 'tenant.permission:DataPolicy,write',
                        'update' => 'tenant.permission:DataPolicy,write',
                        'destroy' => 'tenant.permission:DataPolicy,delete',
                    ])
                    ->parameters(['data-policy' => 'dataPolicy'])
                    ->names('tenant.data-policy');

                Route::prefix('roles')->name('tenant.roles.')->group(function (): void {
                    Route::get('/', [RolePermissionController::class, 'index'])->middleware('tenant.permission:Role,read')->name('index');
                    Route::post('/', [RolePermissionController::class, 'store'])->middleware('tenant.permission:Role,create')->name('store');
                    Route::put('/{role}', [RolePermissionController::class, 'update'])->middleware('tenant.permission:Role,write')->name('update');
                    Route::delete('/{role}', [RolePermissionController::class, 'destroy'])->middleware('tenant.permission:Role,delete')->name('destroy');
                    Route::get('/{role}/permissions', [RolePermissionController::class, 'permissions'])->middleware('tenant.permission:Role,read')->name('permissions');
                    Route::put('/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])->middleware('tenant.permission:Role,write')->name('permissions.update');
                });

                // Redirect legacy/malformed URLs that have a space instead of a hyphen
                // e.g. /company structure/... → /company-structure/...
                Route::get('company structure/{any}', function (string $any) {
                    return redirect('company-structure/' . $any, 301);
                })->where('any', '.*');

                Route::prefix('company-structure')->name('tenant.company-structure.')->group(function (): void {
                    Route::get('companies/export', [CompanyController::class, 'export'])
                        ->middleware('tenant.permission:Company,export')
                        ->name('companies.export');
                    Route::get('companies/import/sample', [CompanyController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Company,import')
                        ->name('companies.import-sample');
                    Route::get('companies/export/download/{file}', [CompanyController::class, 'downloadExport'])
                        ->name('companies.download-export');
                    Route::post('companies/import', [CompanyController::class, 'import'])
                        ->middleware('tenant.permission:Company,import')
                        ->name('companies.import');
                    Route::post('companies/filter', [CompanyController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Company,read')
                        ->name('companies.filter');
                    Route::get('companies/create', [CompanyController::class, 'create'])
                        ->middleware('tenant.permission:Company,create')
                        ->name('companies.create');
                    Route::resource('companies', CompanyController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Company,read')
                        ->middlewareFor('show', 'tenant.permission:Company,read')
                        ->middlewareFor('store', 'tenant.permission:Company,create')
                        ->middlewareFor('edit', 'tenant.permission:Company,write')
                        ->middlewareFor('update', 'tenant.permission:Company,write')
                        ->middlewareFor('destroy', 'tenant.permission:Company,delete')
                        ->names('companies');
                    Route::get('departments/export', [DepartmentController::class, 'export'])
                        ->middleware('tenant.permission:Department,export')->name('departments.export');
                    Route::get('departments/import/sample', [DepartmentController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Department,import')->name('departments.import-sample');
                    Route::get('departments/export/download/{file}', [DepartmentController::class, 'downloadExport'])
                        ->name('departments.download-export');
                    Route::post('departments/import', [DepartmentController::class, 'import'])
                        ->middleware('tenant.permission:Department,import')->name('departments.import');
                    Route::post('departments/filter', [DepartmentController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Department,read')->name('departments.filter');
                    Route::get('departments/create', [DepartmentController::class, 'create'])
                        ->middleware('tenant.permission:Department,create')->name('departments.create');
                    Route::resource('departments', DepartmentController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Department,read')
                        ->middlewareFor('show', 'tenant.permission:Department,read')
                        ->middlewareFor('store', 'tenant.permission:Department,create')
                        ->middlewareFor('edit', 'tenant.permission:Department,write')
                        ->middlewareFor('update', 'tenant.permission:Department,write')
                        ->middlewareFor('destroy', 'tenant.permission:Department,delete')
                        ->names('departments');
                    Route::get('sub-departments/export', [SubDepartmentController::class, 'export'])
                        ->middleware('tenant.permission:SubDepartment,export')->name('sub-departments.export');
                    Route::get('sub-departments/import/sample', [SubDepartmentController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:SubDepartment,import')->name('sub-departments.import-sample');
                    Route::get('sub-departments/export/download/{file}', [SubDepartmentController::class, 'downloadExport'])
                        ->middleware('tenant.permission:SubDepartment,export')->name('sub-departments.download-export');
                    Route::post('sub-departments/import', [SubDepartmentController::class, 'import'])
                        ->middleware('tenant.permission:SubDepartment,import')->name('sub-departments.import');
                    Route::post('sub-departments/filter', [SubDepartmentController::class, 'filterStatus'])
                        ->middleware('tenant.permission:SubDepartment,read')->name('sub-departments.filter');
                    Route::get('sub-departments/create', [SubDepartmentController::class, 'create'])
                        ->middleware('tenant.permission:SubDepartment,create')->name('sub-departments.create');
                    Route::resource('sub-departments', SubDepartmentController::class)
                        ->except(['create'])
                        ->parameters(['sub-departments' => 'sub_department'])
                        ->middlewareFor('index', 'tenant.permission:SubDepartment,read')
                        ->middlewareFor('show', 'tenant.permission:SubDepartment,read')
                        ->middlewareFor('store', 'tenant.permission:SubDepartment,create')
                        ->middlewareFor('edit', 'tenant.permission:SubDepartment,write')
                        ->middlewareFor('update', 'tenant.permission:SubDepartment,write')
                        ->middlewareFor('destroy', 'tenant.permission:SubDepartment,delete')
                        ->names('sub-departments');
                    Route::get('locations/export', [LocationController::class, 'export'])
                        ->middleware('tenant.permission:Location,export')
                        ->name('locations.export');
                    Route::get('locations/import/sample', [LocationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Location,import')
                        ->name('locations.import-sample');
                    Route::get('locations/export/download/{file}', [LocationController::class, 'downloadExport'])
                        ->name('locations.download-export');
                    Route::post('locations/import', [LocationController::class, 'import'])
                        ->middleware('tenant.permission:Location,import')
                        ->name('locations.import');
                    Route::post('locations/filter', [LocationController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Location,read')
                        ->name('locations.filter');
                    Route::get('locations/create', [LocationController::class, 'create'])
                        ->middleware('tenant.permission:Location,create')
                        ->name('locations.create');
                    Route::resource('locations', LocationController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Location,read')
                        ->middlewareFor('show', 'tenant.permission:Location,read')
                        ->middlewareFor('store', 'tenant.permission:Location,create')
                        ->middlewareFor('edit', 'tenant.permission:Location,write')
                        ->middlewareFor('update', 'tenant.permission:Location,write')
                        ->middlewareFor('destroy', 'tenant.permission:Location,delete')
                        ->names('locations');
                    Route::get('data-policies', function () {
                        return redirect('data-policy');
                    })->name('data-policies.legacy');
                });

                Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
            });

            Route::get('/tenant', function () {
                return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
            });
        });
}
