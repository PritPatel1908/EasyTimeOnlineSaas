<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\CanteenFacilityController;
use App\Http\Controllers\Tenant\UnitController;
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

                Route::get('employee structure/{any}', function (string $any) {
                    return redirect('employee-structure/' . $any, 301);
                })->where('any', '.*');

                Route::prefix('employee-structure')->name('tenant.employee-structure.')->group(function (): void {
                    Route::get('/', function () {
                        return redirect()->route('tenant.employee-structure.categories.index');
                    })->name('index');
                    Route::get('categories', [CategoryController::class, 'index'])
                        ->middleware('tenant.permission:Category,read')
                        ->name('categories.index');
                });

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
                    Route::get('canteen-facilities/export', [CanteenFacilityController::class, 'export'])
                        ->middleware('tenant.permission:CanteenFacility,export')->name('canteen-facilities.export');
                    Route::get('canteen-facilities/import/sample', [CanteenFacilityController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:CanteenFacility,import')->name('canteen-facilities.import-sample');
                    Route::get('canteen-facilities/export/download/{file}', [CanteenFacilityController::class, 'downloadExport'])
                        ->middleware('tenant.permission:CanteenFacility,export')->name('canteen-facilities.download-export');
                    Route::post('canteen-facilities/import', [CanteenFacilityController::class, 'import'])
                        ->middleware('tenant.permission:CanteenFacility,import')->name('canteen-facilities.import');
                    Route::post('canteen-facilities/filter', [CanteenFacilityController::class, 'filterStatus'])
                        ->middleware('tenant.permission:CanteenFacility,read')->name('canteen-facilities.filter');
                    Route::post('canteen-facilities/{canteen_facility}/status', [CanteenFacilityController::class, 'updateStatus'])
                        ->middleware('tenant.permission:CanteenFacility,write')->name('canteen-facilities.status');
                    Route::get('canteen-facilities/create', [CanteenFacilityController::class, 'create'])
                        ->middleware('tenant.permission:CanteenFacility,create')->name('canteen-facilities.create');
                    Route::resource('canteen-facilities', CanteenFacilityController::class)
                        ->except(['create'])
                        ->parameters(['canteen-facilities' => 'canteen_facility'])
                        ->middlewareFor('index', 'tenant.permission:CanteenFacility,read')
                        ->middlewareFor('show', 'tenant.permission:CanteenFacility,read')
                        ->middlewareFor('store', 'tenant.permission:CanteenFacility,create')
                        ->middlewareFor('edit', 'tenant.permission:CanteenFacility,write')
                        ->middlewareFor('update', 'tenant.permission:CanteenFacility,write')
                        ->middlewareFor('destroy', 'tenant.permission:CanteenFacility,delete')
                        ->names('canteen-facilities');
                    Route::get('teams/export', [\App\Http\Controllers\Tenant\TeamController::class, 'export'])
                        ->middleware('tenant.permission:Team,export')->name('teams.export');
                    Route::get('teams/import/sample', [\App\Http\Controllers\Tenant\TeamController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Team,import')->name('teams.import-sample');
                    Route::get('teams/export/download/{file}', [\App\Http\Controllers\Tenant\TeamController::class, 'downloadExport'])
                        ->name('teams.download-export');
                    Route::post('teams/import', [\App\Http\Controllers\Tenant\TeamController::class, 'import'])
                        ->middleware('tenant.permission:Team,import')->name('teams.import');
                    Route::post('teams/filter', [\App\Http\Controllers\Tenant\TeamController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Team,read')->name('teams.filter');
                    Route::get('teams/create', [\App\Http\Controllers\Tenant\TeamController::class, 'create'])
                        ->middleware('tenant.permission:Team,create')->name('teams.create');
                    Route::resource('teams', \App\Http\Controllers\Tenant\TeamController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Team,read')
                        ->middlewareFor('show', 'tenant.permission:Team,read')
                        ->middlewareFor('store', 'tenant.permission:Team,create')
                        ->middlewareFor('edit', 'tenant.permission:Team,write')
                        ->middlewareFor('update', 'tenant.permission:Team,write')
                        ->middlewareFor('destroy', 'tenant.permission:Team,delete')
                        ->names('teams');
                    Route::get('units/export', [UnitController::class, 'export'])
                        ->middleware('tenant.permission:Unit,export')->name('units.export');
                    Route::get('units/import/sample', [UnitController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Unit,import')->name('units.import-sample');
                    Route::get('units/export/download/{file}', [UnitController::class, 'downloadExport'])
                        ->middleware('tenant.permission:Unit,export')->name('units.download-export');
                    Route::post('units/import', [UnitController::class, 'import'])
                        ->middleware('tenant.permission:Unit,import')->name('units.import');
                    Route::post('units/filter', [UnitController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Unit,read')->name('units.filter');
                    Route::post('units/{unit}/status', [UnitController::class, 'updateStatus'])
                        ->middleware('tenant.permission:Unit,write')->name('units.status');
                    Route::get('units/create', [UnitController::class, 'create'])
                        ->middleware('tenant.permission:Unit,create')->name('units.create');
                    Route::resource('units', UnitController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Unit,read')
                        ->middlewareFor('show', 'tenant.permission:Unit,read')
                        ->middlewareFor('store', 'tenant.permission:Unit,create')
                        ->middlewareFor('edit', 'tenant.permission:Unit,write')
                        ->middlewareFor('update', 'tenant.permission:Unit,write')
                        ->middlewareFor('destroy', 'tenant.permission:Unit,delete')
                        ->names('units');
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
