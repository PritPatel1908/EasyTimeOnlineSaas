<?php

declare(strict_types=1);

use App\Http\Controllers\Central\AdminCompanyController;
use App\Http\Controllers\Central\AdminDashboardController;
use App\Http\Controllers\Central\AdminDomainController;
use App\Http\Controllers\Central\AdminNotificationController;
use App\Http\Controllers\Central\AdminSectionController;
use App\Http\Controllers\Central\AdminTenantController;
use App\Http\Controllers\Central\AdminUserController;
use App\Http\Controllers\Central\AuthController;
use Illuminate\Support\Facades\Route;

$centralDomains = array_values(array_unique(array_filter([
    env('CENTRAL_DOMAIN_LOCAL', 'admin.saas.test'),
    env('CENTRAL_DOMAIN_PROD'),
    'localhost',
])));

foreach ($centralDomains as $centralDomain) {
    Route::domain($centralDomain)->get('/', function () {
        return view('welcome');
    });
}

$adminRoutes = function (): void {
    Route::middleware('auth')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/notifications/poll', [AdminNotificationController::class, 'poll'])->name('admin.notifications.poll');
        Route::post('/tenants/pagination', [AdminTenantController::class, 'pagination'])->name('admin.tenants.pagination');
        Route::post('/tenants/{tenant}/database-action', [AdminTenantController::class, 'databaseAction'])->name('admin.tenants.database-action');
        Route::post('/tenants/{tenant}/migrate', [AdminTenantController::class, 'migrate'])->name('admin.tenants.migrate');
        Route::post('/tenants/{tenant}/seed', [AdminTenantController::class, 'seed'])->name('admin.tenants.seed');
        Route::resource('tenants', AdminTenantController::class)->except(['show'])->names('admin.tenants');
        Route::post('/companies/pagination', [AdminCompanyController::class, 'pagination'])->name('admin.companies.pagination');
        Route::resource('companies', AdminCompanyController::class)->except(['show'])->names('admin.companies');
        Route::post('/domains/pagination', [AdminDomainController::class, 'pagination'])->name('admin.domains.pagination');
        Route::resource('domains', AdminDomainController::class)->except(['show'])->names('admin.domains');
        Route::post('/users/pagination', [AdminUserController::class, 'pagination'])->name('admin.users.pagination');
        Route::resource('users', AdminUserController::class)->except(['show'])->names('admin.users');
        Route::get('/reports', [AdminSectionController::class, 'reports'])->name('admin.reports');
        Route::get('/settings', [AdminSectionController::class, 'settings'])->name('admin.settings');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
};

$authRoutes = function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/login/otp', [AuthController::class, 'showOtp'])->name('auth.otp');
        Route::post('/login/otp', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');
        Route::post('/login/otp/resend', [AuthController::class, 'resendOtp'])->name('auth.otp.resend');
    });
};

foreach ($centralDomains as $centralDomain) {
    Route::domain($centralDomain)->group($authRoutes);
    Route::domain($centralDomain)->group($adminRoutes);
}
