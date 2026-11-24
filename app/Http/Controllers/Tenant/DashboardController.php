<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Services\Tenant\PendingApprovalQuery;
use App\Support\TenantPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantId = (string) tenant('id');
        $user = Auth::guard('tenant')->user();
        $userName = $user?->name ?: trim(implode(' ', array_filter([
            $user?->fname,
            $user?->mname,
            $user?->lname,
        ])));
        $canApprovePendingRequests = TenantPermissions::userCan('PendingRequest', 'read')
            && TenantPermissions::userCan('PendingRequest', 'approve');
        $applicationModules = array_values(array_filter([
            [
                'label' => 'Leave Application',
                'permission' => 'LeaveApplication',
                'path' => 'time-management/leave-applications/create',
            ],
            [
                'label' => 'COFF Application',
                'permission' => 'CoffApplication',
                'path' => 'time-management/coff-applications/create',
            ],
            [
                'label' => 'Manual Attendance Application',
                'permission' => 'ManualAttendanceApplication',
                'path' => 'time-management/manual-attendance-applications/create',
            ],
            [
                'label' => 'Manual Punch Application',
                'permission' => 'ManualPunchApplication',
                'path' => 'time-management/manual-punch-applications/create',
            ],
            [
                'label' => 'Overtime Application',
                'permission' => 'OvertimeApplication',
                'path' => 'time-management/overtime-applications/create',
            ],
            [
                'label' => 'Short Leave Application',
                'permission' => 'ShortLeaveApplication',
                'path' => 'time-management/short-leave-applications/create',
            ],
            [
                'label' => 'Shift Change Application',
                'permission' => 'ShiftChangeApplication',
                'path' => 'time-management/shift-change-applications/create',
            ],
            [
                'label' => 'Week Off Change Application',
                'permission' => 'WeekOffChangeApplication',
                'path' => 'time-management/week-off-change-applications/create',
            ],
            [
                'label' => 'Week Off Swap Application',
                'permission' => 'WeekOffSwapApplication',
                'path' => 'time-management/week-off-swap-applications/create',
            ],
        ], static fn (array $module): bool => TenantPermissions::userCan($module['permission'], 'create')));
        $userCount = User::query()->count();
        $attendanceCount = Attendance::year((int) now()->year)
            ->whereDate('date', today())
            ->whereNotNull('in_time')
            ->whereNotNull('out_time')
            ->whereIn('user_id', User::query()->select('id'))
            ->distinct()
            ->count('user_id');

        return view('index', [
            'tenantId' => $tenantId,
            'domain' => request()->getHost(),
            'currentUserName' => $userName !== '' ? $userName : 'User',
            'currentUserProfilePic' => $user?->profile_pic,
            'canApprovePendingRequests' => $canApprovePendingRequests,
            'pendingApprovalCount' => $canApprovePendingRequests
                ? PendingApprovalQuery::forUser((int) Auth::guard('tenant')->id())->count()
                : null,
            'applicationModules' => $applicationModules,
            'companyCount' => Company::query()->count(),
            'activeCompanyCount' => Company::query()->where('status', 1)->count(),
            'userCount' => $userCount,
            'attendanceCount' => $attendanceCount,
            'locationCount' => Location::query()->count(),
            'recentUsers' => User::query()->latest('id')->limit(5)->get(),
        ]);
    }
}
