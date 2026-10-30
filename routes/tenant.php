<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\LeaveApplicationController;
use App\Http\Controllers\Tenant\LeaveGroupController;
use App\Http\Controllers\Tenant\ApprovalFlowController;
use App\Http\Controllers\Tenant\DesignationController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\AreaController;
use App\Http\Controllers\Tenant\MachineController;
use App\Http\Controllers\Tenant\ShiftController;
use App\Http\Controllers\Tenant\ShiftRotationController;
use App\Http\Controllers\Tenant\ManualPunchController;
use App\Http\Controllers\Tenant\ManualPunchApplicationController;
use App\Http\Controllers\Tenant\ManualAttendanceController;
use App\Http\Controllers\Tenant\ManualAttendanceApplicationController;
use App\Http\Controllers\Tenant\ManualShiftChangeController;
use App\Http\Controllers\Tenant\AttendanceLogController;
use App\Http\Controllers\Tenant\ShiftChangeApplicationController;
use App\Http\Controllers\Tenant\LateComingRuleController;
use App\Http\Controllers\Tenant\EarlyGoingRuleController;
use App\Http\Controllers\Tenant\HalfDayRuleController;
use App\Http\Controllers\Tenant\OvertimeRuleController;
use App\Http\Controllers\Tenant\AbsentRuleController;
use App\Http\Controllers\Tenant\DepartmentController;
use App\Http\Controllers\Tenant\FinancialYearController;
use App\Http\Controllers\Tenant\CanteenFacilityController;
use App\Http\Controllers\Tenant\UnitController;
use App\Http\Controllers\Tenant\SubDepartmentController;
use App\Http\Controllers\Tenant\DataPolicyController;
use App\Http\Controllers\Tenant\LocationController;
use App\Http\Controllers\Tenant\LeaveTypeController;
use App\Http\Controllers\Tenant\ManualLeaveBalanceController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\PendingApprovalController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\LicenseController;
use App\Http\Controllers\Tenant\RolePermissionController;
use App\Http\Controllers\Tenant\WeekOffChangeController;
use App\Http\Controllers\Tenant\WeekOffChangeApplicationController;
use App\Http\Controllers\Tenant\WeekOffSwapController;
use App\Http\Controllers\Tenant\WeekOffSwapApplicationController;
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
                Route::prefix('requests/pending')->name('tenant.requests.pending.')->controller(PendingApprovalController::class)->group(function (): void {
                    Route::get('/', 'index')->middleware('tenant.permission:PendingRequest,read')->name('index');
                    Route::post('/bulk-decision', 'bulkDecide')->name('bulk-decision');
                    Route::get('/{detail}', 'show')->middleware('tenant.permission:PendingRequest,read')->whereNumber('detail')->name('show');
                    Route::post('/{detail}/decision', 'decide')->whereNumber('detail')->name('decision');
                });
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

                Route::prefix('time-management')->name('tenant.time-management.')->group(function (): void {
                    Route::get('financial-years/export', [FinancialYearController::class, 'export'])
                        ->middleware('tenant.permission:FinancialYear,export')->name('financial-years.export');
                    Route::get('financial-years/import/sample', [FinancialYearController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:FinancialYear,import')->name('financial-years.import-sample');
                    Route::get('financial-years/export/download/{file}', [FinancialYearController::class, 'downloadExport'])
                        ->middleware('tenant.permission:FinancialYear,export')->name('financial-years.download-export');
                    Route::post('financial-years/import', [FinancialYearController::class, 'import'])
                        ->middleware('tenant.permission:FinancialYear,import')->name('financial-years.import');
                    Route::post('financial-years/filter', [FinancialYearController::class, 'filterStatus'])
                        ->middleware('tenant.permission:FinancialYear,read')->name('financial-years.filter');
                    Route::post('financial-years/{financial_year}/flags', [FinancialYearController::class, 'updateFlags'])
                        ->middleware('tenant.permission:FinancialYear,write')->name('financial-years.flags');
                    Route::get('financial-years/create', [FinancialYearController::class, 'create'])
                        ->middleware('tenant.permission:FinancialYear,create')->name('financial-years.create');
                    Route::resource('financial-years', FinancialYearController::class)
                        ->except(['create'])
                        ->parameters(['financial-years' => 'financial_year'])
                        ->middlewareFor('index', 'tenant.permission:FinancialYear,read')
                        ->middlewareFor('show', 'tenant.permission:FinancialYear,read')
                        ->middlewareFor('store', 'tenant.permission:FinancialYear,create')
                        ->middlewareFor('edit', 'tenant.permission:FinancialYear,write')
                        ->middlewareFor('update', 'tenant.permission:FinancialYear,write')
                        ->middlewareFor('destroy', 'tenant.permission:FinancialYear,delete')
                        ->names('financial-years');
                    Route::get('shifts/export', [ShiftController::class, 'export'])
                        ->middleware('tenant.permission:Shift,export')
                        ->name('shifts.export');
                    Route::get('shifts/import/sample', [ShiftController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Shift,import')
                        ->name('shifts.import-sample');
                    Route::get('shifts/export/download/{file}', [ShiftController::class, 'downloadExport'])
                        ->name('shifts.download-export');
                    Route::post('shifts/import', [ShiftController::class, 'import'])
                        ->middleware('tenant.permission:Shift,import')
                        ->name('shifts.import');
                    Route::post('shifts/filter', [ShiftController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Shift,read')
                        ->name('shifts.filter');
                    Route::get('shifts/create', [ShiftController::class, 'create'])
                        ->middleware('tenant.permission:Shift,create')
                        ->name('shifts.create');
                    Route::resource('shifts', ShiftController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Shift,read')
                        ->middlewareFor('show', 'tenant.permission:Shift,read')
                        ->middlewareFor('store', 'tenant.permission:Shift,create')
                        ->middlewareFor('edit', 'tenant.permission:Shift,write')
                        ->middlewareFor('update', 'tenant.permission:Shift,write')
                        ->middlewareFor('destroy', 'tenant.permission:Shift,delete')
                        ->names('shifts');
                    Route::get('manual-punches/export', [ManualPunchController::class, 'export'])
                        ->middleware('tenant.permission:ManualPunch,export')->name('manual-punches.export');
                    Route::get('manual-punches/import/sample', [ManualPunchController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ManualPunch,import')->name('manual-punches.import-sample');
                    Route::get('manual-punches/export/download/{file}', [ManualPunchController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ManualPunch,export')->name('manual-punches.download-export');
                    Route::post('manual-punches/import', [ManualPunchController::class, 'import'])
                        ->middleware('tenant.permission:ManualPunch,import')->name('manual-punches.import');
                    Route::get('manual-punches/create', [ManualPunchController::class, 'create'])
                        ->middleware('tenant.permission:ManualPunch,create')->name('manual-punches.create');
                    Route::resource('manual-punches', ManualPunchController::class)
                        ->except(['create'])
                        ->parameters(['manual-punches' => 'manual_punch'])
                        ->middlewareFor('index', 'tenant.permission:ManualPunch,read')
                        ->middlewareFor('show', 'tenant.permission:ManualPunch,read')
                        ->middlewareFor('store', 'tenant.permission:ManualPunch,create')
                        ->middlewareFor('edit', 'tenant.permission:ManualPunch,write')
                        ->middlewareFor('update', 'tenant.permission:ManualPunch,write')
                        ->middlewareFor('destroy', 'tenant.permission:ManualPunch,delete')
                        ->names('manual-punches');
                    Route::get('manual-punch-applications/export', [ManualPunchApplicationController::class, 'export'])
                        ->middleware('tenant.permission:ManualPunchApplication,export')->name('manual-punch-applications.export');
                    Route::get('manual-punch-applications/import/sample', [ManualPunchApplicationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ManualPunchApplication,import')->name('manual-punch-applications.import-sample');
                    Route::get('manual-punch-applications/export/download/{file}', [ManualPunchApplicationController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ManualPunchApplication,export')->name('manual-punch-applications.download-export');
                    Route::post('manual-punch-applications/import', [ManualPunchApplicationController::class, 'import'])
                        ->middleware('tenant.permission:ManualPunchApplication,import')->name('manual-punch-applications.import');
                    Route::get('manual-punch-applications/create', [ManualPunchApplicationController::class, 'create'])
                        ->middleware('tenant.permission:ManualPunchApplication,create')->name('manual-punch-applications.create');
                    Route::resource('manual-punch-applications', ManualPunchApplicationController::class)
                        ->except(['create'])
                        ->parameters(['manual-punch-applications' => 'manual_punch_application'])
                        ->middlewareFor('index', 'tenant.permission:ManualPunchApplication,read')
                        ->middlewareFor('show', 'tenant.permission:ManualPunchApplication,read')
                        ->middlewareFor('store', 'tenant.permission:ManualPunchApplication,create')
                        ->middlewareFor('edit', 'tenant.permission:ManualPunchApplication,write')
                        ->middlewareFor('update', 'tenant.permission:ManualPunchApplication,write')
                        ->middlewareFor('destroy', 'tenant.permission:ManualPunchApplication,delete')
                        ->names('manual-punch-applications');
                    Route::get('manual-attendance-applications/export', [ManualAttendanceApplicationController::class, 'export'])
                        ->middleware('tenant.permission:ManualAttendanceApplication,export')->name('manual-attendance-applications.export');
                    Route::get('manual-attendance-applications/import/sample', [ManualAttendanceApplicationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ManualAttendanceApplication,import')->name('manual-attendance-applications.import-sample');
                    Route::get('manual-attendance-applications/export/download/{file}', [ManualAttendanceApplicationController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ManualAttendanceApplication,export')->name('manual-attendance-applications.download-export');
                    Route::post('manual-attendance-applications/import', [ManualAttendanceApplicationController::class, 'import'])
                        ->middleware('tenant.permission:ManualAttendanceApplication,import')->name('manual-attendance-applications.import');
                    Route::get('manual-attendance-applications/create', [ManualAttendanceApplicationController::class, 'create'])
                        ->middleware('tenant.permission:ManualAttendanceApplication,create')->name('manual-attendance-applications.create');
                    Route::resource('manual-attendance-applications', ManualAttendanceApplicationController::class)
                        ->except(['create'])
                        ->parameters(['manual-attendance-applications' => 'manual_attendance_application'])
                        ->middlewareFor('index', 'tenant.permission:ManualAttendanceApplication,read')
                        ->middlewareFor('show', 'tenant.permission:ManualAttendanceApplication,read')
                        ->middlewareFor('store', 'tenant.permission:ManualAttendanceApplication,create')
                        ->middlewareFor('edit', 'tenant.permission:ManualAttendanceApplication,write')
                        ->middlewareFor('update', 'tenant.permission:ManualAttendanceApplication,write')
                        ->middlewareFor('destroy', 'tenant.permission:ManualAttendanceApplication,delete')
                        ->names('manual-attendance-applications');
                    Route::get('manual-attendances/export', [ManualAttendanceController::class, 'export'])
                        ->middleware('tenant.permission:ManualAttendance,export')->name('manual-attendances.export');
                    Route::get('manual-attendances/import/sample', [ManualAttendanceController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ManualAttendance,import')->name('manual-attendances.import-sample');
                    Route::get('manual-attendances/export/download/{file}', [ManualAttendanceController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ManualAttendance,export')->name('manual-attendances.download-export');
                    Route::post('manual-attendances/import', [ManualAttendanceController::class, 'import'])
                        ->middleware('tenant.permission:ManualAttendance,import')->name('manual-attendances.import');
                    Route::get('manual-attendances/create', [ManualAttendanceController::class, 'create'])
                        ->middleware('tenant.permission:ManualAttendance,create')->name('manual-attendances.create');
                    Route::resource('manual-attendances', ManualAttendanceController::class)
                        ->except(['create'])
                        ->parameters(['manual-attendances' => 'manual_attendance'])
                        ->middlewareFor('index', 'tenant.permission:ManualAttendance,read')
                        ->middlewareFor('show', 'tenant.permission:ManualAttendance,read')
                        ->middlewareFor('store', 'tenant.permission:ManualAttendance,create')
                        ->middlewareFor('edit', 'tenant.permission:ManualAttendance,write')
                        ->middlewareFor('update', 'tenant.permission:ManualAttendance,write')
                        ->middlewareFor('destroy', 'tenant.permission:ManualAttendance,delete')
                        ->names('manual-attendances');
                    Route::get('manual-shift-changes/export', [ManualShiftChangeController::class, 'export'])
                        ->middleware('tenant.permission:ShiftChange,export')->name('manual-shift-changes.export');
                    Route::get('manual-shift-changes/export/download/{file}', [ManualShiftChangeController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ShiftChange,export')->name('manual-shift-changes.download-export');
                    Route::get('manual-shift-changes/import/sample', [ManualShiftChangeController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ShiftChange,import')->name('manual-shift-changes.import-sample');
                    Route::post('manual-shift-changes/import', [ManualShiftChangeController::class, 'import'])
                        ->middleware('tenant.permission:ShiftChange,import')->name('manual-shift-changes.import');
                    Route::get('manual-shift-changes/create', [ManualShiftChangeController::class, 'create'])
                        ->middleware('tenant.permission:ShiftChange,create')->name('manual-shift-changes.create');
                    Route::resource('manual-shift-changes', ManualShiftChangeController::class)
                        ->except(['create'])
                        ->parameters(['manual-shift-changes' => 'manual_shift_change'])
                        ->middlewareFor('index', 'tenant.permission:ShiftChange,read')
                        ->middlewareFor('show', 'tenant.permission:ShiftChange,read')
                        ->middlewareFor('store', 'tenant.permission:ShiftChange,create')
                        ->middlewareFor('edit', 'tenant.permission:ShiftChange,write')
                        ->middlewareFor('update', 'tenant.permission:ShiftChange,write')
                        ->middlewareFor('destroy', 'tenant.permission:ShiftChange,delete')
                        ->names('manual-shift-changes');
                    Route::get('attendance-logs', [AttendanceLogController::class, 'index'])
                        ->middleware('tenant.permission:AttendanceLog,read')->name('attendance-logs.index');
                    Route::get('time-card', [AttendanceLogController::class, 'timeCard'])
                        ->middleware('tenant.permission:AttendanceLog,read')->name('time-card.index');
                    Route::post('time-card', [AttendanceLogController::class, 'filterTimeCard'])
                        ->middleware('tenant.permission:AttendanceLog,read')->name('time-card.filter');
                    Route::post('attendance-logs/reprocess-range', [AttendanceLogController::class, 'reprocessRange'])
                        ->middleware('tenant.permission:AttendanceLog,write')->name('attendance-logs.reprocess-range');
                    Route::post('attendance-logs/delete-range', [AttendanceLogController::class, 'deleteRange'])
                        ->middleware('tenant.permission:AttendanceLog,delete')->name('attendance-logs.delete-range');
                    Route::post('attendance-logs/restore-range', [AttendanceLogController::class, 'restoreRange'])
                        ->middleware('tenant.permission:AttendanceLog,delete')->name('attendance-logs.restore-range');
                    Route::get('attendance-logs/{attendance_log}', [AttendanceLogController::class, 'show'])
                        ->middleware('tenant.permission:AttendanceLog,read')->name('attendance-logs.show');
                    Route::post('attendance-logs/{attendance_log}/reprocess', [AttendanceLogController::class, 'reprocess'])
                        ->middleware('tenant.permission:AttendanceLog,write')->name('attendance-logs.reprocess');
                    Route::post('attendance-logs/{attendance_log}/delete', [AttendanceLogController::class, 'destroy'])
                        ->middleware('tenant.permission:AttendanceLog,delete')->name('attendance-logs.delete');
                    Route::post('attendance-logs/{attendance_log}/restore', [AttendanceLogController::class, 'restore'])
                        ->middleware('tenant.permission:AttendanceLog,delete')->name('attendance-logs.restore');
                    Route::get('leave-applications/export', [LeaveApplicationController::class, 'export'])
                        ->middleware('tenant.permission:LeaveApplication,export')->name('leave-applications.export');
                    Route::get('leave-applications/import/sample', [LeaveApplicationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:LeaveApplication,import')->name('leave-applications.import-sample');
                    Route::get('leave-applications/export/download/{file}', [LeaveApplicationController::class, 'downloadExport'])
                        ->middleware('tenant.permission:LeaveApplication,export')->name('leave-applications.download-export');
                    Route::post('leave-applications/import', [LeaveApplicationController::class, 'import'])
                        ->middleware('tenant.permission:LeaveApplication,import')->name('leave-applications.import');
                    Route::get('leave-applications/create', [LeaveApplicationController::class, 'create'])
                        ->middleware('tenant.permission:LeaveApplication,create')->name('leave-applications.create');
                    Route::resource('leave-applications', LeaveApplicationController::class)
                        ->except(['create'])
                        ->parameters(['leave-applications' => 'leave_application'])
                        ->middlewareFor('index', 'tenant.permission:LeaveApplication,read')
                        ->middlewareFor('show', 'tenant.permission:LeaveApplication,read')
                        ->middlewareFor('store', 'tenant.permission:LeaveApplication,create')
                        ->middlewareFor('edit', 'tenant.permission:LeaveApplication,write')
                        ->middlewareFor('update', 'tenant.permission:LeaveApplication,write')
                        ->middlewareFor('destroy', 'tenant.permission:LeaveApplication,delete')
                        ->names('leave-applications');
                    Route::get('shift-change-applications/export', [ShiftChangeApplicationController::class, 'export'])
                        ->middleware('tenant.permission:ShiftChangeApplication,export')->name('shift-change-applications.export');
                    Route::get('shift-change-applications/export/download/{file}', [ShiftChangeApplicationController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ShiftChangeApplication,export')->name('shift-change-applications.download-export');
                    Route::get('shift-change-applications/import/sample', [ShiftChangeApplicationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ShiftChangeApplication,import')->name('shift-change-applications.import-sample');
                    Route::post('shift-change-applications/import', [ShiftChangeApplicationController::class, 'import'])
                        ->middleware('tenant.permission:ShiftChangeApplication,import')->name('shift-change-applications.import');
                    Route::get('shift-change-applications/create', [ShiftChangeApplicationController::class, 'create'])
                        ->middleware('tenant.permission:ShiftChangeApplication,create')->name('shift-change-applications.create');
                    Route::resource('shift-change-applications', ShiftChangeApplicationController::class)
                        ->except(['create'])
                        ->parameters(['shift-change-applications' => 'shift_change_application'])
                        ->middlewareFor('index', 'tenant.permission:ShiftChangeApplication,read')
                        ->middlewareFor('show', 'tenant.permission:ShiftChangeApplication,read')
                        ->middlewareFor('store', 'tenant.permission:ShiftChangeApplication,create')
                        ->middlewareFor('edit', 'tenant.permission:ShiftChangeApplication,write')
                        ->middlewareFor('update', 'tenant.permission:ShiftChangeApplication,write')
                        ->middlewareFor('destroy', 'tenant.permission:ShiftChangeApplication,delete')
                        ->names('shift-change-applications');
                    Route::get('week-off-changes/export', [WeekOffChangeController::class, 'export'])
                        ->middleware('tenant.permission:WeekOffChange,export')->name('week-off-changes.export');
                    Route::get('week-off-changes/export/download/{file}', [WeekOffChangeController::class, 'downloadExport'])
                        ->middleware('tenant.permission:WeekOffChange,export')->name('week-off-changes.download-export');
                    Route::get('week-off-changes/import/sample', [WeekOffChangeController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:WeekOffChange,import')->name('week-off-changes.import-sample');
                    Route::post('week-off-changes/import', [WeekOffChangeController::class, 'import'])
                        ->middleware('tenant.permission:WeekOffChange,import')->name('week-off-changes.import');
                    Route::get('week-off-changes/create', [WeekOffChangeController::class, 'create'])
                        ->middleware('tenant.permission:WeekOffChange,create')->name('week-off-changes.create');
                    Route::resource('week-off-changes', WeekOffChangeController::class)
                        ->except(['create'])
                        ->parameters(['week-off-changes' => 'week_off_change'])
                        ->middlewareFor('index', 'tenant.permission:WeekOffChange,read')
                        ->middlewareFor('show', 'tenant.permission:WeekOffChange,read')
                        ->middlewareFor('store', 'tenant.permission:WeekOffChange,create')
                        ->middlewareFor('edit', 'tenant.permission:WeekOffChange,write')
                        ->middlewareFor('update', 'tenant.permission:WeekOffChange,write')
                        ->middlewareFor('destroy', 'tenant.permission:WeekOffChange,delete')
                        ->names('week-off-changes');
                    Route::get('week-off-change-applications/export', [WeekOffChangeApplicationController::class, 'export'])
                        ->middleware('tenant.permission:WeekOffChangeApplication,export')->name('week-off-change-applications.export');
                    Route::get('week-off-change-applications/import/sample', [WeekOffChangeApplicationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:WeekOffChangeApplication,import')->name('week-off-change-applications.import-sample');
                    Route::get('week-off-change-applications/export/download/{file}', [WeekOffChangeApplicationController::class, 'downloadExport'])
                        ->middleware('tenant.permission:WeekOffChangeApplication,export')->name('week-off-change-applications.download-export');
                    Route::post('week-off-change-applications/import', [WeekOffChangeApplicationController::class, 'import'])
                        ->middleware('tenant.permission:WeekOffChangeApplication,import')->name('week-off-change-applications.import');
                    Route::get('week-off-change-applications/create', [WeekOffChangeApplicationController::class, 'create'])
                        ->middleware('tenant.permission:WeekOffChangeApplication,create')->name('week-off-change-applications.create');
                    Route::resource('week-off-change-applications', WeekOffChangeApplicationController::class)
                        ->except(['create'])
                        ->parameters(['week-off-change-applications' => 'week_off_change_application'])
                        ->middlewareFor('index', 'tenant.permission:WeekOffChangeApplication,read')
                        ->middlewareFor('show', 'tenant.permission:WeekOffChangeApplication,read')
                        ->middlewareFor('store', 'tenant.permission:WeekOffChangeApplication,create')
                        ->middlewareFor('edit', 'tenant.permission:WeekOffChangeApplication,write')
                        ->middlewareFor('update', 'tenant.permission:WeekOffChangeApplication,write')
                        ->middlewareFor('destroy', 'tenant.permission:WeekOffChangeApplication,delete')
                        ->names('week-off-change-applications');
                    Route::get('week-off-swaps/export', [WeekOffSwapController::class, 'export'])
                        ->middleware('tenant.permission:WeekOffSwap,export')->name('week-off-swaps.export');
                    Route::get('week-off-swaps/export/download/{file}', [WeekOffSwapController::class, 'downloadExport'])
                        ->middleware('tenant.permission:WeekOffSwap,export')->name('week-off-swaps.download-export');
                    Route::get('week-off-swaps/import/sample', [WeekOffSwapController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:WeekOffSwap,import')->name('week-off-swaps.import-sample');
                    Route::post('week-off-swaps/import', [WeekOffSwapController::class, 'import'])
                        ->middleware('tenant.permission:WeekOffSwap,import')->name('week-off-swaps.import');
                    Route::post('week-off-swaps/check-week-off', [WeekOffSwapController::class, 'checkWeekOff'])
                        ->name('week-off-swaps.check-week-off');
                    Route::get('week-off-swaps/create', [WeekOffSwapController::class, 'create'])
                        ->middleware('tenant.permission:WeekOffSwap,create')->name('week-off-swaps.create');
                    Route::resource('week-off-swaps', WeekOffSwapController::class)
                        ->except(['create'])
                        ->parameters(['week-off-swaps' => 'week_off_swap'])
                        ->middlewareFor('index', 'tenant.permission:WeekOffSwap,read')
                        ->middlewareFor('show', 'tenant.permission:WeekOffSwap,read')
                        ->middlewareFor('store', 'tenant.permission:WeekOffSwap,create')
                        ->middlewareFor('edit', 'tenant.permission:WeekOffSwap,write')
                        ->middlewareFor('update', 'tenant.permission:WeekOffSwap,write')
                        ->middlewareFor('destroy', 'tenant.permission:WeekOffSwap,delete')
                        ->names('week-off-swaps');
                    Route::get('week-off-swap-applications/export', [WeekOffSwapApplicationController::class, 'export'])
                        ->middleware('tenant.permission:WeekOffSwapApplication,export')->name('week-off-swap-applications.export');
                    Route::get('week-off-swap-applications/import/sample', [WeekOffSwapApplicationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:WeekOffSwapApplication,import')->name('week-off-swap-applications.import-sample');
                    Route::get('week-off-swap-applications/export/download/{file}', [WeekOffSwapApplicationController::class, 'downloadExport'])
                        ->middleware('tenant.permission:WeekOffSwapApplication,export')->name('week-off-swap-applications.download-export');
                    Route::post('week-off-swap-applications/import', [WeekOffSwapApplicationController::class, 'import'])
                        ->middleware('tenant.permission:WeekOffSwapApplication,import')->name('week-off-swap-applications.import');
                    Route::post('week-off-swap-applications/check-week-off', [WeekOffSwapApplicationController::class, 'checkWeekOff'])
                        ->name('week-off-swap-applications.check-week-off');
                    Route::get('week-off-swap-applications/create', [WeekOffSwapApplicationController::class, 'create'])
                        ->middleware('tenant.permission:WeekOffSwapApplication,create')->name('week-off-swap-applications.create');
                    Route::resource('week-off-swap-applications', WeekOffSwapApplicationController::class)
                        ->except(['create'])
                        ->parameters(['week-off-swap-applications' => 'week_off_swap_application'])
                        ->middlewareFor('index', 'tenant.permission:WeekOffSwapApplication,read')
                        ->middlewareFor('show', 'tenant.permission:WeekOffSwapApplication,read')
                        ->middlewareFor('store', 'tenant.permission:WeekOffSwapApplication,create')
                        ->middlewareFor('edit', 'tenant.permission:WeekOffSwapApplication,write')
                        ->middlewareFor('update', 'tenant.permission:WeekOffSwapApplication,write')
                        ->middlewareFor('destroy', 'tenant.permission:WeekOffSwapApplication,delete')
                        ->names('week-off-swap-applications');
                    Route::get('shift-rotations/export', [ShiftRotationController::class, 'export'])
                        ->middleware('tenant.permission:ShiftRotation,export')
                        ->name('shift-rotations.export');
                    Route::get('shift-rotations/import/sample', [ShiftRotationController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ShiftRotation,import')
                        ->name('shift-rotations.import-sample');
                    Route::get('shift-rotations/export/download/{file}', [ShiftRotationController::class, 'downloadExport'])
                        ->name('shift-rotations.download-export');
                    Route::post('shift-rotations/import', [ShiftRotationController::class, 'import'])
                        ->middleware('tenant.permission:ShiftRotation,import')
                        ->name('shift-rotations.import');
                    Route::post('shift-rotations/filter', [ShiftRotationController::class, 'filterStatus'])
                        ->middleware('tenant.permission:ShiftRotation,read')
                        ->name('shift-rotations.filter');
                    Route::get('shift-rotations/create', [ShiftRotationController::class, 'create'])
                        ->middleware('tenant.permission:ShiftRotation,create')
                        ->name('shift-rotations.create');
                    Route::resource('shift-rotations', ShiftRotationController::class)
                        ->except(['create'])
                        ->parameters(['shift-rotations' => 'shift_rotation'])
                        ->middlewareFor('index', 'tenant.permission:ShiftRotation,read')
                        ->middlewareFor('show', 'tenant.permission:ShiftRotation,read')
                        ->middlewareFor('store', 'tenant.permission:ShiftRotation,create')
                        ->middlewareFor('edit', 'tenant.permission:ShiftRotation,write')
                        ->middlewareFor('update', 'tenant.permission:ShiftRotation,write')
                        ->middlewareFor('destroy', 'tenant.permission:ShiftRotation,delete')
                        ->names('shift-rotations');
                    Route::get('late-coming-rules/export', [LateComingRuleController::class, 'export'])
                        ->middleware('tenant.permission:LateComingRule,export')
                        ->name('late-coming-rules.export');
                    Route::get('late-coming-rules/import/sample', [LateComingRuleController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:LateComingRule,import')
                        ->name('late-coming-rules.import-sample');
                    Route::get('late-coming-rules/export/download/{file}', [LateComingRuleController::class, 'downloadExport'])
                        ->middleware('tenant.permission:LateComingRule,export')
                        ->name('late-coming-rules.download-export');
                    Route::post('late-coming-rules/import', [LateComingRuleController::class, 'import'])
                        ->middleware('tenant.permission:LateComingRule,import')
                        ->name('late-coming-rules.import');
                    Route::post('late-coming-rules/filter', [LateComingRuleController::class, 'filterStatus'])
                        ->middleware('tenant.permission:LateComingRule,read')
                        ->name('late-coming-rules.filter');
                    Route::get('late-coming-rules/create', [LateComingRuleController::class, 'create'])
                        ->middleware('tenant.permission:LateComingRule,create')
                        ->name('late-coming-rules.create');
                    Route::resource('late-coming-rules', LateComingRuleController::class)
                        ->except(['create'])
                        ->parameters(['late-coming-rules' => 'late_coming_rule'])
                        ->middlewareFor('index', 'tenant.permission:LateComingRule,read')
                        ->middlewareFor('show', 'tenant.permission:LateComingRule,read')
                        ->middlewareFor('store', 'tenant.permission:LateComingRule,create')
                        ->middlewareFor('edit', 'tenant.permission:LateComingRule,write')
                        ->middlewareFor('update', 'tenant.permission:LateComingRule,write')
                        ->middlewareFor('destroy', 'tenant.permission:LateComingRule,delete')
                        ->names('late-coming-rules');
                    Route::get('early-going-rules/export', [EarlyGoingRuleController::class, 'export'])
                        ->middleware('tenant.permission:EarlyGoingRule,export')
                        ->name('early-going-rules.export');
                    Route::get('early-going-rules/import/sample', [EarlyGoingRuleController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:EarlyGoingRule,import')
                        ->name('early-going-rules.import-sample');
                    Route::get('early-going-rules/export/download/{file}', [EarlyGoingRuleController::class, 'downloadExport'])
                        ->middleware('tenant.permission:EarlyGoingRule,export')
                        ->name('early-going-rules.download-export');
                    Route::post('early-going-rules/import', [EarlyGoingRuleController::class, 'import'])
                        ->middleware('tenant.permission:EarlyGoingRule,import')
                        ->name('early-going-rules.import');
                    Route::post('early-going-rules/filter', [EarlyGoingRuleController::class, 'filterStatus'])
                        ->middleware('tenant.permission:EarlyGoingRule,read')
                        ->name('early-going-rules.filter');
                    Route::get('early-going-rules/create', [EarlyGoingRuleController::class, 'create'])
                        ->middleware('tenant.permission:EarlyGoingRule,create')
                        ->name('early-going-rules.create');
                    Route::resource('early-going-rules', EarlyGoingRuleController::class)
                        ->except(['create'])
                        ->parameters(['early-going-rules' => 'early_going_rule'])
                        ->middlewareFor('index', 'tenant.permission:EarlyGoingRule,read')
                        ->middlewareFor('show', 'tenant.permission:EarlyGoingRule,read')
                        ->middlewareFor('store', 'tenant.permission:EarlyGoingRule,create')
                        ->middlewareFor('edit', 'tenant.permission:EarlyGoingRule,write')
                        ->middlewareFor('update', 'tenant.permission:EarlyGoingRule,write')
                        ->middlewareFor('destroy', 'tenant.permission:EarlyGoingRule,delete')
                        ->names('early-going-rules');
                    Route::get('half-day-rules/export', [HalfDayRuleController::class, 'export'])
                        ->middleware('tenant.permission:HalfDayRule,export')
                        ->name('half-day-rules.export');
                    Route::get('half-day-rules/import/sample', [HalfDayRuleController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:HalfDayRule,import')
                        ->name('half-day-rules.import-sample');
                    Route::get('half-day-rules/export/download/{file}', [HalfDayRuleController::class, 'downloadExport'])
                        ->middleware('tenant.permission:HalfDayRule,export')
                        ->name('half-day-rules.download-export');
                    Route::post('half-day-rules/import', [HalfDayRuleController::class, 'import'])
                        ->middleware('tenant.permission:HalfDayRule,import')
                        ->name('half-day-rules.import');
                    Route::post('half-day-rules/filter', [HalfDayRuleController::class, 'filterStatus'])
                        ->middleware('tenant.permission:HalfDayRule,read')
                        ->name('half-day-rules.filter');
                    Route::get('half-day-rules/create', [HalfDayRuleController::class, 'create'])
                        ->middleware('tenant.permission:HalfDayRule,create')
                        ->name('half-day-rules.create');
                    Route::resource('half-day-rules', HalfDayRuleController::class)
                        ->except(['create'])
                        ->parameters(['half-day-rules' => 'half_day_rule'])
                        ->middlewareFor('index', 'tenant.permission:HalfDayRule,read')
                        ->middlewareFor('show', 'tenant.permission:HalfDayRule,read')
                        ->middlewareFor('store', 'tenant.permission:HalfDayRule,create')
                        ->middlewareFor('edit', 'tenant.permission:HalfDayRule,write')
                        ->middlewareFor('update', 'tenant.permission:HalfDayRule,write')
                        ->middlewareFor('destroy', 'tenant.permission:HalfDayRule,delete')
                        ->names('half-day-rules');
                    Route::get('overtime-rules/export', [OvertimeRuleController::class, 'export'])
                        ->middleware('tenant.permission:OvertimeRule,export')
                        ->name('overtime-rules.export');
                    Route::get('overtime-rules/import/sample', [OvertimeRuleController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:OvertimeRule,import')
                        ->name('overtime-rules.import-sample');
                    Route::get('overtime-rules/export/download/{file}', [OvertimeRuleController::class, 'downloadExport'])
                        ->middleware('tenant.permission:OvertimeRule,export')
                        ->name('overtime-rules.download-export');
                    Route::post('overtime-rules/import', [OvertimeRuleController::class, 'import'])
                        ->middleware('tenant.permission:OvertimeRule,import')
                        ->name('overtime-rules.import');
                    Route::post('overtime-rules/filter', [OvertimeRuleController::class, 'filterStatus'])
                        ->middleware('tenant.permission:OvertimeRule,read')
                        ->name('overtime-rules.filter');
                    Route::get('overtime-rules/create', [OvertimeRuleController::class, 'create'])
                        ->middleware('tenant.permission:OvertimeRule,create')
                        ->name('overtime-rules.create');
                    Route::resource('overtime-rules', OvertimeRuleController::class)
                        ->except(['create'])
                        ->parameters(['overtime-rules' => 'overtime_rule'])
                        ->middlewareFor('index', 'tenant.permission:OvertimeRule,read')
                        ->middlewareFor('show', 'tenant.permission:OvertimeRule,read')
                        ->middlewareFor('store', 'tenant.permission:OvertimeRule,create')
                        ->middlewareFor('edit', 'tenant.permission:OvertimeRule,write')
                        ->middlewareFor('update', 'tenant.permission:OvertimeRule,write')
                        ->middlewareFor('destroy', 'tenant.permission:OvertimeRule,delete')
                        ->names('overtime-rules');
                    Route::get('absent-rules/export', [AbsentRuleController::class, 'export'])
                        ->middleware('tenant.permission:AbsentRule,export')
                        ->name('absent-rules.export');
                    Route::get('absent-rules/import/sample', [AbsentRuleController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:AbsentRule,import')
                        ->name('absent-rules.import-sample');
                    Route::get('absent-rules/export/download/{file}', [AbsentRuleController::class, 'downloadExport'])
                        ->middleware('tenant.permission:AbsentRule,export')
                        ->name('absent-rules.download-export');
                    Route::post('absent-rules/import', [AbsentRuleController::class, 'import'])
                        ->middleware('tenant.permission:AbsentRule,import')
                        ->name('absent-rules.import');
                    Route::post('absent-rules/filter', [AbsentRuleController::class, 'filterStatus'])
                        ->middleware('tenant.permission:AbsentRule,read')
                        ->name('absent-rules.filter');
                    Route::get('absent-rules/create', [AbsentRuleController::class, 'create'])
                        ->middleware('tenant.permission:AbsentRule,create')
                        ->name('absent-rules.create');
                    Route::resource('absent-rules', AbsentRuleController::class)
                        ->except(['create'])
                        ->parameters(['absent-rules' => 'absent_rule'])
                        ->middlewareFor('index', 'tenant.permission:AbsentRule,read')
                        ->middlewareFor('show', 'tenant.permission:AbsentRule,read')
                        ->middlewareFor('store', 'tenant.permission:AbsentRule,create')
                        ->middlewareFor('edit', 'tenant.permission:AbsentRule,write')
                        ->middlewareFor('update', 'tenant.permission:AbsentRule,write')
                        ->middlewareFor('destroy', 'tenant.permission:AbsentRule,delete')
                        ->names('absent-rules');
                });

                Route::prefix('employee-structure')->name('tenant.employee-structure.')->group(function (): void {
                    Route::get('/', function () {
                        return redirect()->route('tenant.employee-structure.categories.index');
                    })->name('index');
                    Route::get('categories', [CategoryController::class, 'index'])
                        ->middleware('tenant.permission:Category,read')
                        ->name('categories.index');
                    Route::get('categories/export', [CategoryController::class, 'export'])->middleware('tenant.permission:Category,export')->name('categories.export');
                    Route::get('categories/import/sample', [CategoryController::class, 'downloadImportSample'])->middleware('tenant.permission:Category,import')->name('categories.import-sample');
                    Route::get('categories/export/download/{file}', [CategoryController::class, 'downloadExport'])->middleware('tenant.permission:Category,export')->name('categories.download-export');
                    Route::post('categories/import', [CategoryController::class, 'import'])->middleware('tenant.permission:Category,import')->name('categories.import');
                    Route::post('categories/filter', [CategoryController::class, 'filterStatus'])->middleware('tenant.permission:Category,read')->name('categories.filter');
                    Route::get('categories/create', [CategoryController::class, 'create'])->middleware('tenant.permission:Category,create')->name('categories.create');
                    Route::resource('categories', CategoryController::class)->except(['create'])->middlewareFor('index', 'tenant.permission:Category,read')->middlewareFor('show', 'tenant.permission:Category,read')->middlewareFor('store', 'tenant.permission:Category,create')->middlewareFor('edit', 'tenant.permission:Category,write')->middlewareFor('update', 'tenant.permission:Category,write')->middlewareFor('destroy', 'tenant.permission:Category,delete')->names('categories');
                    Route::get('leave-groups', [LeaveGroupController::class, 'index'])->middleware('tenant.permission:LeaveGroup,read')->name('leave-groups.index');
                    Route::get('leave-groups/export', [LeaveGroupController::class, 'export'])->middleware('tenant.permission:LeaveGroup,export')->name('leave-groups.export');
                    Route::get('leave-groups/import/sample', [LeaveGroupController::class, 'downloadImportSample'])->middleware('tenant.permission:LeaveGroup,import')->name('leave-groups.import-sample');
                    Route::get('leave-groups/export/download/{file}', [LeaveGroupController::class, 'downloadExport'])->middleware('tenant.permission:LeaveGroup,export')->name('leave-groups.download-export');
                    Route::post('leave-groups/import', [LeaveGroupController::class, 'import'])->middleware('tenant.permission:LeaveGroup,import')->name('leave-groups.import');
                    Route::post('leave-groups/filter', [LeaveGroupController::class, 'filterStatus'])->middleware('tenant.permission:LeaveGroup,read')->name('leave-groups.filter');
                    Route::get('leave-groups/create', [LeaveGroupController::class, 'create'])->middleware('tenant.permission:LeaveGroup,create')->name('leave-groups.create');
                    Route::resource('leave-groups', LeaveGroupController::class)->except(['create'])->middlewareFor('index', 'tenant.permission:LeaveGroup,read')->middlewareFor('show', 'tenant.permission:LeaveGroup,read')->middlewareFor('store', 'tenant.permission:LeaveGroup,create')->middlewareFor('edit', 'tenant.permission:LeaveGroup,write')->middlewareFor('update', 'tenant.permission:LeaveGroup,write')->middlewareFor('destroy', 'tenant.permission:LeaveGroup,delete')->names('leave-groups');
                    Route::get('approval-flows/export', [ApprovalFlowController::class, 'export'])->middleware('tenant.permission:ApprovalFlow,export')->name('approval-flows.export');
                    Route::get('approval-flows/import/sample', [ApprovalFlowController::class, 'downloadImportSample'])->middleware('tenant.permission:ApprovalFlow,import')->name('approval-flows.import-sample');
                    Route::get('approval-flows/export/download/{file}', [ApprovalFlowController::class, 'downloadExport'])->middleware('tenant.permission:ApprovalFlow,export')->name('approval-flows.download-export');
                    Route::post('approval-flows/import', [ApprovalFlowController::class, 'import'])->middleware('tenant.permission:ApprovalFlow,import')->name('approval-flows.import');
                    Route::post('approval-flows/filter', [ApprovalFlowController::class, 'filterStatus'])->middleware('tenant.permission:ApprovalFlow,read')->name('approval-flows.filter');
                    Route::resource('approval-flows', ApprovalFlowController::class)
                        ->middlewareFor('index', 'tenant.permission:ApprovalFlow,read')
                        ->middlewareFor('show', 'tenant.permission:ApprovalFlow,read')
                        ->middlewareFor('create', 'tenant.permission:ApprovalFlow,create')
                        ->middlewareFor('store', 'tenant.permission:ApprovalFlow,create')
                        ->middlewareFor('edit', 'tenant.permission:ApprovalFlow,write')
                        ->middlewareFor('update', 'tenant.permission:ApprovalFlow,write')
                        ->middlewareFor('destroy', 'tenant.permission:ApprovalFlow,delete')
                        ->parameters(['approval-flows' => 'approvalFlow'])
                        ->names('approval-flows');
                    Route::get('employees', [EmployeeController::class, 'index'])->middleware('tenant.permission:User,read')->name('employees.index');
                    Route::get('employees/export', [EmployeeController::class, 'export'])->middleware('tenant.permission:User,export')->name('employees.export');
                    Route::get('employees/import/sample', [EmployeeController::class, 'downloadImportSample'])->middleware('tenant.permission:User,import')->name('employees.import-sample');
                    Route::get('employees/export/download/{file}', [EmployeeController::class, 'downloadExport'])->middleware('tenant.permission:User,export')->name('employees.download-export');
                    Route::post('employees/import', [EmployeeController::class, 'import'])->middleware('tenant.permission:User,import')->name('employees.import');
                    Route::post('employees/filter', [EmployeeController::class, 'filterStatus'])->middleware('tenant.permission:User,read')->name('employees.filter');
                    Route::get('employees/create', [EmployeeController::class, 'create'])->middleware('tenant.permission:User,create')->name('employees.create');
                    Route::resource('employees', EmployeeController::class)->except(['create'])->middlewareFor('index', 'tenant.permission:User,read')->middlewareFor('show', 'tenant.permission:User,read')->middlewareFor('store', 'tenant.permission:User,create')->middlewareFor('edit', 'tenant.permission:User,write')->middlewareFor('update', 'tenant.permission:User,write')->middlewareFor('destroy', 'tenant.permission:User,delete')->names('employees');
                    Route::get('designations', [DesignationController::class, 'index'])->middleware('tenant.permission:Designation,read')->name('designations.index');
                    Route::get('designations/export', [DesignationController::class, 'export'])->middleware('tenant.permission:Designation,export')->name('designations.export');
                    Route::get('designations/import/sample', [DesignationController::class, 'downloadImportSample'])->middleware('tenant.permission:Designation,import')->name('designations.import-sample');
                    Route::get('designations/export/download/{file}', [DesignationController::class, 'downloadExport'])->middleware('tenant.permission:Designation,export')->name('designations.download-export');
                    Route::post('designations/import', [DesignationController::class, 'import'])->middleware('tenant.permission:Designation,import')->name('designations.import');
                    Route::post('designations/filter', [DesignationController::class, 'filterStatus'])->middleware('tenant.permission:Designation,read')->name('designations.filter');
                    Route::get('designations/create', [DesignationController::class, 'create'])->middleware('tenant.permission:Designation,create')->name('designations.create');
                    Route::resource('designations', DesignationController::class)->except(['create'])->middlewareFor('index', 'tenant.permission:Designation,read')->middlewareFor('show', 'tenant.permission:Designation,read')->middlewareFor('store', 'tenant.permission:Designation,create')->middlewareFor('edit', 'tenant.permission:Designation,write')->middlewareFor('update', 'tenant.permission:Designation,write')->middlewareFor('destroy', 'tenant.permission:Designation,delete')->names('designations');
                    Route::get('leave-types/export', [LeaveTypeController::class, 'export'])->middleware('tenant.permission:LeaveType,export')->name('leave-types.export');
                    Route::get('leave-types/import/sample', [LeaveTypeController::class, 'downloadImportSample'])->middleware('tenant.permission:LeaveType,import')->name('leave-types.import-sample');
                    Route::get('leave-types/export/download/{file}', [LeaveTypeController::class, 'downloadExport'])->middleware('tenant.permission:LeaveType,export')->name('leave-types.download-export');
                    Route::post('leave-types/import', [LeaveTypeController::class, 'import'])->middleware('tenant.permission:LeaveType,import')->name('leave-types.import');
                    Route::post('leave-types/filter', [LeaveTypeController::class, 'filterStatus'])->middleware('tenant.permission:LeaveType,read')->name('leave-types.filter');
                    Route::get('leave-types/create', [LeaveTypeController::class, 'create'])->middleware('tenant.permission:LeaveType,create')->name('leave-types.create');
                    Route::resource('leave-types', LeaveTypeController::class)
                        ->except(['create'])
                        ->parameters(['leave-types' => 'leave_type'])
                        ->middlewareFor('index', 'tenant.permission:LeaveType,read')
                        ->middlewareFor('show', 'tenant.permission:LeaveType,read')
                        ->middlewareFor('store', 'tenant.permission:LeaveType,create')
                        ->middlewareFor('edit', 'tenant.permission:LeaveType,write')
                        ->middlewareFor('update', 'tenant.permission:LeaveType,write')
                        ->middlewareFor('destroy', 'tenant.permission:LeaveType,delete')
                        ->names('leave-types');
                    Route::get('manual-leave-balances/export', [ManualLeaveBalanceController::class, 'export'])
                        ->middleware('tenant.permission:ManualLeaveBalance,export')
                        ->name('manual-leave-balances.export');
                    Route::get('manual-leave-balances/import/sample', [ManualLeaveBalanceController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:ManualLeaveBalance,import')
                        ->name('manual-leave-balances.import-sample');
                    Route::get('manual-leave-balances/export/download/{file}', [ManualLeaveBalanceController::class, 'downloadExport'])
                        ->middleware('tenant.permission:ManualLeaveBalance,export')
                        ->name('manual-leave-balances.download-export');
                    Route::post('manual-leave-balances/import', [ManualLeaveBalanceController::class, 'import'])
                        ->middleware('tenant.permission:ManualLeaveBalance,import')
                        ->name('manual-leave-balances.import');
                    Route::get('manual-leave-balances/create', [ManualLeaveBalanceController::class, 'create'])
                        ->middleware('tenant.permission:ManualLeaveBalance,create')
                        ->name('manual-leave-balances.create');
                    Route::resource('manual-leave-balances', ManualLeaveBalanceController::class)
                        ->except(['create'])
                        ->parameters(['manual-leave-balances' => 'manual_leave_balance'])
                        ->middlewareFor('index', 'tenant.permission:ManualLeaveBalance,read')
                        ->middlewareFor('show', 'tenant.permission:ManualLeaveBalance,read')
                        ->middlewareFor('store', 'tenant.permission:ManualLeaveBalance,create')
                        ->middlewareFor('edit', 'tenant.permission:ManualLeaveBalance,write')
                        ->middlewareFor('update', 'tenant.permission:ManualLeaveBalance,write')
                        ->middlewareFor('destroy', 'tenant.permission:ManualLeaveBalance,delete')
                        ->names('manual-leave-balances');
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
                    Route::get('areas/export', [AreaController::class, 'export'])
                        ->middleware('tenant.permission:Area,export')->name('areas.export');
                    Route::get('areas/import/sample', [AreaController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Area,import')->name('areas.import-sample');
                    Route::get('areas/export/download/{file}', [AreaController::class, 'downloadExport'])
                        ->middleware('tenant.permission:Area,export')->name('areas.download-export');
                    Route::post('areas/import', [AreaController::class, 'import'])
                        ->middleware('tenant.permission:Area,import')->name('areas.import');
                    Route::post('areas/filter', [AreaController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Area,read')->name('areas.filter');
                    Route::get('areas/create', [AreaController::class, 'create'])
                        ->middleware('tenant.permission:Area,create')->name('areas.create');
                    Route::resource('areas', AreaController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Area,read')
                        ->middlewareFor('show', 'tenant.permission:Area,read')
                        ->middlewareFor('store', 'tenant.permission:Area,create')
                        ->middlewareFor('edit', 'tenant.permission:Area,write')
                        ->middlewareFor('update', 'tenant.permission:Area,write')
                        ->middlewareFor('destroy', 'tenant.permission:Area,delete')
                        ->names('areas');
                    Route::get('machines/export', [MachineController::class, 'export'])
                        ->middleware('tenant.permission:Machine,export')->name('machines.export');
                    Route::get('machines/import/sample', [MachineController::class, 'downloadImportSample'])
                        ->middleware('tenant.permission:Machine,import')->name('machines.import-sample');
                    Route::get('machines/export/download/{file}', [MachineController::class, 'downloadExport'])
                        ->middleware('tenant.permission:Machine,export')->name('machines.download-export');
                    Route::post('machines/import', [MachineController::class, 'import'])
                        ->middleware('tenant.permission:Machine,import')->name('machines.import');
                    Route::post('machines/filter', [MachineController::class, 'filterStatus'])
                        ->middleware('tenant.permission:Machine,read')->name('machines.filter');
                    Route::get('machines/create', [MachineController::class, 'create'])
                        ->middleware('tenant.permission:Machine,create')->name('machines.create');
                    Route::resource('machines', MachineController::class)
                        ->except(['create'])
                        ->middlewareFor('index', 'tenant.permission:Machine,read')
                        ->middlewareFor('show', 'tenant.permission:Machine,read')
                        ->middlewareFor('store', 'tenant.permission:Machine,create')
                        ->middlewareFor('edit', 'tenant.permission:Machine,write')
                        ->middlewareFor('update', 'tenant.permission:Machine,write')
                        ->middlewareFor('destroy', 'tenant.permission:Machine,delete')
                        ->names('machines');
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
