<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\Coff;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinalizeApprovedApplication implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const APPROVABLE_TYPES = [
        LeaveApplication::class,
        Coff::class,
        ManualAttendanceApplication::class,
        ManualPunchApplication::class,
        OutDuty::class,
        ShortLeaveApplication::class,
        ShiftChangeApplication::class,
        WeekOffChangeApplication::class,
        WeekOffSwapApplication::class,
    ];

    public function __construct(
        public string $approvableType,
        public int $approvableId,
    ) {}

    public function handle(): void
    {
        if (! in_array($this->approvableType, self::APPROVABLE_TYPES, true)) {
            throw new RuntimeException('The approval request type is not supported.');
        }

        $modelClass = $this->approvableType;
        $application = $modelClass::query()->findOrFail($this->approvableId);
        $statuses = ApprovalStatus::query()
            ->where('approvable_type', $this->approvableType)
            ->where('approvable_id', $this->approvableId)
            ->get();

        if ($statuses->isEmpty() || $statuses->contains(
            static fn (ApprovalStatus $status): bool => $status->approval_status !== 'approved',
        )) {
            return;
        }

        if ($application instanceof ShiftChangeApplication) {
            $application->loadMissing(['Users', 'shifts', 'shift_rotation']);
            DB::transaction(function () use ($application): void {
                foreach ($application->Users as $user) {
                    $user->shifts()->delete();
                    $user->shift_type = $application->shift_type;
                    $user->save();

                    if ($application->shift_type === 'rotational') {
                        $user->shift_rotation_id = $application->shift_rotation?->id;
                        $user->save();
                    } else {
                        $user->shifts()->sync($application->shifts->modelKeys());
                    }
                }

                CreateShiftMuster::dispatch($application)
                    ->onConnection('database_tenant')
                    ->onQueue('processing')
                    ->afterCommit();
            });

            return;
        }

        if ($application instanceof WeekOffChangeApplication) {
            CreateWeekOffMuster::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof WeekOffSwapApplication) {
            CreateWeekOffSwap::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof ManualPunchApplication) {
            ApproveUserManualPunch::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof ManualAttendanceApplication) {
            ApproveUserManualAttendance::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof LeaveApplication) {
            ApproveLeaveApplication::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof ShortLeaveApplication) {
            ApproveShortLeaveApplication::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof Coff) {
            ApproveCoff::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof OutDuty) {
            ApproveOd::dispatch($application)->onConnection('database_tenant')->onQueue('processing');
        }
    }
}
