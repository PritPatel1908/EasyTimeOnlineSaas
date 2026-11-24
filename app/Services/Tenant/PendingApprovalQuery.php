<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\Coff;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use Illuminate\Database\Eloquent\Builder;

final class PendingApprovalQuery
{
    public const APPROVABLE_TYPES = [
        LeaveApplication::class,
        Coff::class,
        CoffApplication::class,
        ManualAttendanceApplication::class,
        ManualPunchApplication::class,
        OvertimeApplication::class,
        OutDuty::class,
        ShortLeaveApplication::class,
        ShiftChangeApplication::class,
        WeekOffChangeApplication::class,
        WeekOffSwapApplication::class,
    ];

    public static function forUser(int $userId): Builder
    {
        return ApprovalStatusDetails::query()
            ->where('approval_status', 'pending')
            ->whereHas('main_status', function (Builder $query): void {
                $query->where('approval_status', 'pending')
                    ->whereIn('approvable_type', self::APPROVABLE_TYPES);
            })
            ->whereHas('approval_flow_detail.users', fn (Builder $query) => $query->whereKey($userId))
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('approval_status_details as previous_details')
                    ->whereColumn('previous_details.approval_status_id', 'approval_status_details.approval_status_id')
                    ->whereColumn('previous_details.level', '<', 'approval_status_details.level')
                    ->where('previous_details.approval_status', '!=', 'approved');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('approval_statuses as current_status')
                    ->join('approval_statuses as rejected_statuses', function ($join): void {
                        $join->on('rejected_statuses.approvable_id', '=', 'current_status.approvable_id')
                            ->on('rejected_statuses.approvable_type', '=', 'current_status.approvable_type');
                    })
                    ->whereColumn('current_status.id', 'approval_status_details.approval_status_id')
                    ->where('rejected_statuses.approval_status', 'rejected');
            });
    }
}
