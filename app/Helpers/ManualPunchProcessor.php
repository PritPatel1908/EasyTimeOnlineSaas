<?php

namespace App\Helpers;

use App\Exceptions\ManualPunchProcessingException;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\AttendanceLog;
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

            self::createAttendanceLogs($manualPunch, $users);
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
            $attendanceLog = AttendanceLog::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'referance_type' => 'manual_punch',
                    'referance_id' => $referenceId,
                ],
                [
                    'user_code' => $user->code,
                    'datetime' => $datetime,
                    'punch_type' => $punchType,
                    'is_out' => ManualPunch::isOutDirection($punchType),
                    'is_calculated' => false,
                    'is_ignored' => false,
                    'is_staged' => false,
                    'is_manual' => true,
                ],
            );

            ProcessPendingLogs::dispatch($attendanceLog)->onQueue('processing')->afterCommit();
            Log::channel('attprocess')->debug(
                'ProcessManualPunch dispatched for ##' . $attendanceLog->id . '|' . $attendanceLog->user_code . '|' . $attendanceLog->datetime
            );
        }
    }
}
