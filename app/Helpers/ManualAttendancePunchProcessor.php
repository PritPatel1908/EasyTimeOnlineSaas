<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Jobs\Tenant\ProcessPendingLogsBatch;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\ManualAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManualAttendancePunchProcessor
{
    public static function process(ManualAttendance $attendance): void
    {
        DB::transaction(function () use ($attendance): void {
            $users = $attendance->Users()->get();
            $userIds = $users->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
            $referenceTypes = ['manual_attendance_start', 'manual_attendance_end'];

            $staleLogs = AttendanceLog::query()
                ->whereIn('referance_type', $referenceTypes)
                ->where('referance_id', $attendance->id)
                ->where('is_locked', false);

            if ($userIds !== []) {
                $staleLogs->whereNotIn('user_id', $userIds);
            }

            $logsChanged = $staleLogs->delete() > 0;

            foreach ($users as $user) {
                $logsChanged = self::updateAttendanceLog(
                    $attendance,
                    (int) $user->id,
                    $user->code,
                    'manual_attendance_start',
                    $attendance->in_time,
                    1,
                    false,
                ) || $logsChanged;
                $logsChanged = self::updateAttendanceLog(
                    $attendance,
                    (int) $user->id,
                    $user->code,
                    'manual_attendance_end',
                    $attendance->out_time,
                    2,
                    true,
                ) || $logsChanged;
            }

            if ($logsChanged) {
                ProcessPendingLogsBatch::dispatch()
                    ->onConnection('database_tenant')
                    ->onQueue('processing')
                    ->afterCommit();
                Log::channel('attprocess')->debug('Manual attendance punches queued for chunked processing', [
                    'manual_attendance_id' => $attendance->id,
                ]);
            }
        });
    }

    private static function updateAttendanceLog(
        ManualAttendance $attendance,
        int $userId,
        ?string $userCode,
        string $referenceType,
        mixed $datetime,
        int $punchType,
        bool $isOut,
    ): bool {
        return AttendanceLogWriter::updateUnlessLocked(
            [
                'user_id' => $userId,
                'referance_type' => $referenceType,
                'referance_id' => $attendance->id,
            ],
            [
                'user_code' => $userCode,
                'datetime' => $datetime,
                'punch_type' => $punchType,
                'is_out' => $isOut,
                'is_calculated' => false,
                'is_ignored' => false,
                'is_staged' => false,
                'is_manual' => true,
            ],
        );
    }
}
