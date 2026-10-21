<?php

namespace App\Helpers;

use App\Exceptions\ManualPunchProcessingException;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManualPunchProcessor
{
    public static function process(ManualPunch $manualPunch): void
    {
        DB::transaction(function () use ($manualPunch): void {
            $users = $manualPunch->Users()->get();
            if ($users->isEmpty()) {
                $users = User::query()->get();
            }
            if ($users->isEmpty()) {
                throw new ManualPunchProcessingException('No employees are available for this manual punch.');
            }

            $requiresApproval = (string) GeneralConfiguration::query()
                ->where('key', 'manual_punch_requires_approval')
                ->value('value') === '1';

            if (! $requiresApproval) {
                self::createAttendanceLogs($manualPunch, $users);

                return;
            }

            foreach ($users as $user) {
                self::createApprovalApplication($manualPunch, $user);
            }
        });
    }

    /**
     * @param iterable<User>|null $users
     */
    public static function createAttendanceLogs(ManualPunch|ManualPunchApplication $punch, ?iterable $users = null): void
    {
        $users ??= $punch->Users()->get();
        $referenceId = $punch instanceof ManualPunch
            ? $punch->id
            : ($punch->manual_punch_id ?? $punch->id);
        $datetime = $punch->punch_date->format('Y-m-d') . ' ' . $punch->punch_time->format('H:i:s');
        $punchType = ManualPunch::normalizePunchType($punch->punch_type);

        foreach ($users as $user) {
            $attendanceLog = AttendanceLog::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'referance_type' => 'manual_punch',
                    'referance_id' => $referenceId,
                    'datetime' => $datetime,
                    'punch_type' => $punchType,
                    'is_manual' => true,
                ],
                [
                    'user_code' => $user->code,
                ],
            );

            if (ManualPunch::isOutDirection($punchType) && ! $attendanceLog->is_out) {
                $attendanceLog->is_out = true;
                $attendanceLog->save();
            }

            ProcessPendingLogs::dispatch($attendanceLog)->onQueue('processing')->afterCommit();
            Log::channel('attprocess')->debug(
                'ProcessManualPunch dispatched for ##' . $attendanceLog->id . '|' . $attendanceLog->user_code . '|' . $attendanceLog->datetime
            );
        }
    }

    private static function createApprovalApplication(ManualPunch $manualPunch, User $user): void
    {
        $user->loadMissing('manual_punch_approval_flow.approval_flow_details.users');
        $approvalFlow = $user->manual_punch_approval_flow;
        if ($approvalFlow === null) {
            throw new ManualPunchProcessingException('Employee ' . $user->code . ' does not have a manual punch approval flow.');
        }

        $flowDetail = $approvalFlow->approval_flow_details->sortBy('level')->first();
        if ($flowDetail === null || $flowDetail->users->isEmpty()) {
            throw new ManualPunchProcessingException('The manual punch approval flow for employee ' . $user->code . ' has no approvers.');
        }

        $application = ManualPunchApplication::query()
            ->where('manual_punch_id', $manualPunch->id)
            ->whereHas('Users', static fn ($query) => $query->where('users.id', $user->id))
            ->first();

        if ($application === null) {
            $application = ManualPunchApplication::query()->create([
                'manual_punch_id' => $manualPunch->id,
                'punch_type' => ManualPunch::normalizePunchType($manualPunch->punch_type),
                'punch_date' => $manualPunch->punch_date->format('Y-m-d'),
                'punch_time' => $manualPunch->punch_time->format('H:i:s'),
                'reason' => $manualPunch->reason,
                'created_by' => $manualPunch->created_by,
                'updated_by' => $manualPunch->updated_by,
            ]);
            $application->Users()->sync([$user->id]);
        }

        if ($application->approval_status()->exists()) {
            return;
        }

        $status = $application->approval_status()->create([
            'approval_status' => 'pending',
            'approval_flow_id' => $approvalFlow->id,
        ]);
        $status->forceFill([
            'created_by' => $manualPunch->created_by,
            'updated_by' => $manualPunch->updated_by,
        ])->save();

        foreach ($flowDetail->users as $approver) {
            $detail = $status->approval_status_details()->create([
                'level' => $flowDetail->level,
                'approval_flow_detail_id' => $flowDetail->id,
                'user_id' => $approver->id,
                'approval_status' => 'pending',
            ]);
            $detail->forceFill([
                'created_by' => $manualPunch->created_by,
                'updated_by' => $manualPunch->updated_by,
            ])->save();
        }
    }
}
