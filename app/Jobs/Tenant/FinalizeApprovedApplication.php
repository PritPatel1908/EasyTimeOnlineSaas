<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\Coff;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Services\Tenant\ApprovedApplicationEffectSnapshotService;
use App\Services\Tenant\LeaveAttendanceWriter;
use App\Services\Tenant\CoffAttendanceWriter;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        app(ApprovedApplicationEffectSnapshotService::class)->capture($application);

        if ($application instanceof OvertimeApplication) {
            $date = Carbon::parse($application->date)->toDateString();
            $table = 'attendances'.Carbon::parse($date)->format('Y');
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Attendance table {$table} does not exist.");
            }

            $attendance = DB::table($table)
                ->where('user_id', $application->user_id)
                ->whereDate('date', $date);
            if (! $attendance->exists()) {
                throw new RuntimeException('The attendance record for this overtime application could not be found.');
            }

            $attendance->update(['approved_overtime_minutes' => $application->overtime_minutes]);

            return;
        }

        if ($application instanceof ShiftChangeApplication) {
            $application->loadMissing(['Users', 'shifts', 'shift_rotation']);
            DB::transaction(function () use ($application): void {
                foreach ($application->Users as $user) {
                    $user->shift_type = $application->shift_type;
                    $user->save();

                    if ($application->shift_type === 'rotational') {
                        $user->shifts()->sync([]);
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
            ApplyWeekOffChangeApplication::dispatch((int) $application->id)
                ->onConnection('database_tenant')
                ->onQueue('processing');

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
            ProcessManualAttendanceApplicationPunches::dispatch((int) $application->id)
                ->onConnection('database_tenant')
                ->onQueue('processing');

            return;
        }

        if ($application instanceof LeaveApplication) {
            app(LeaveAttendanceWriter::class)->createForApprovedApplication($application);

            ApproveLeaveApplication::dispatch($application)->onConnection('database_tenant')->onQueue('processing');

            return;
        }

        if ($application instanceof CoffApplication) {
            DB::transaction(function () use ($application): void {
                app(CoffAttendanceWriter::class)->createForApprovedApplication($application);
                $application->update(['status' => 'approved']);
            });

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
