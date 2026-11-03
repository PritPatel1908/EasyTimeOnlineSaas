<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Helpers\ManualPunchProcessor;
use App\Jobs\Tenant\ReprocessAttendanceLogs;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\InOutMuster;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ShortLeaveAttendancePunchService
{
    public function removeGeneratedPunches(ShortLeaveApplication $application): void
    {
        DB::transaction(function () use ($application): void {
            $reason = $this->reasonFor($application);
            $punches = ManualPunch::query()
                ->where('reason', $reason)
                ->whereHas('Users', static fn ($query) => $query->whereKey($application->user_id))
                ->get();

            if ($punches->isEmpty()) {
                return;
            }

            $logs = AttendanceLog::query()
                ->where('referance_type', ManualPunch::class)
                ->whereIn('referance_id', $punches->modelKeys())
                ->get();
            if ($logs->contains(static fn (AttendanceLog $log): bool => (bool) $log->is_locked)) {
                throw ValidationException::withMessages([
                    'date' => 'This short leave cannot be changed because its generated punch is locked.',
                ]);
            }

            $logIds = $logs->modelKeys();
            if ($logIds !== []) {
                foreach (range($application->date->year - 1, $application->date->year + 1) as $year) {
                    $attendanceTable = 'attendances'.$year;
                    if (Schema::hasTable($attendanceTable)) {
                        $attendances = Attendance::year($year)
                            ->where(function ($query) use ($logIds): void {
                                $query->whereIn('in_log_id', $logIds)
                                    ->orWhereIn('out_log_id', $logIds);
                            })
                            ->get();
                        if ($attendances->contains(static fn (Attendance $attendance): bool => $attendance->is_locked)) {
                            throw ValidationException::withMessages([
                                'date' => 'This short leave cannot be changed because its attendance is locked.',
                            ]);
                        }
                        $attendances->each->delete();
                    }

                    $musterTable = 'in_out_musters'.$year;
                    if (Schema::hasTable($musterTable)) {
                        $musters = InOutMuster::year($year)
                            ->where(function ($query) use ($logIds): void {
                                $query->whereIn('in_log_id', $logIds)
                                    ->orWhereIn('out_log_id', $logIds);
                            })
                            ->get();
                        if ($musters->contains(static fn (InOutMuster $muster): bool => $muster->is_locked)) {
                            throw ValidationException::withMessages([
                                'date' => 'This short leave cannot be changed because its in/out muster is locked.',
                            ]);
                        }
                        $musters->each->delete();
                    }
                }

                $logs->each->delete();
                $startDateTime = Carbon::parse($logs->min('datetime'))->subDay()->startOfDay();
                $endDateTime = Carbon::parse($logs->max('datetime'))->addDay()->endOfDay();
                ReprocessAttendanceLogs::dispatch(
                    $startDateTime->toDateTimeString(),
                    $endDateTime->toDateTimeString(),
                    [(int) $application->user_id],
                )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
            }

            foreach ($punches as $punch) {
                $punch->Users()->detach();
                $punch->delete();
            }
        });
    }

    public function createForApprovedApplication(int $applicationId): void
    {
        [$punchIds, $userId] = DB::transaction(function () use ($applicationId): array {
            $application = ShortLeaveApplication::query()
                ->with('approval_statuses')
                ->lockForUpdate()
                ->findOrFail($applicationId);

            if (
                (int) $application->status !== 1
                || $application->overallApprovalStatus() !== 'approved'
            ) {
                return [[], null];
            }

            $user = User::query()->findOrFail($application->user_id);
            $reason = $this->reasonFor($application);
            if (ManualPunch::query()->where('reason', $reason)->whereHas(
                'Users',
                static fn ($query) => $query->whereKey($user->id),
            )->exists()) {
                return [[], null];
            }

            $attendance = null;
            if (in_array((int) $application->short_leave_type, [1, 2], true)) {
                $attendance = Attendance::year($application->date->year)
                    ->with(['inLog', 'outLog'])
                    ->where('user_id', $user->id)
                    ->whereDate('date', $application->date->toDateString())
                    ->where('is_locked', false)
                    ->first();
            }

            $punches = $this->punchesFor($application, $attendance);
            if ($punches === []) {
                return [[], null];
            }

            $createdPunchIds = [];
            foreach ($punches as [$type, $datetime]) {
                $punch = ManualPunch::query()->create([
                    'punch_type' => $type,
                    'punch_date' => $datetime->toDateString(),
                    'punch_time' => $datetime->format('H:i:s'),
                    'reason' => $reason,
                    'created_by' => $application->created_by,
                    'updated_by' => $application->updated_by,
                ]);
                $punch->Users()->attach($user->id);
                $createdPunchIds[] = (int) $punch->id;
            }

            return [$createdPunchIds, (int) $user->id];
        });

        if ($punchIds === []) {
            return;
        }

        $user = User::query()->findOrFail($userId);

        foreach (ManualPunch::query()->whereKey($punchIds)->get() as $punch) {
            ManualPunchProcessor::createAttendanceLogs($punch, [$user]);
        }
    }

    /**
     * @return array<int, array{0: string, 1: Carbon}>
     */
    private function punchesFor(ShortLeaveApplication $application, ?Attendance $attendance): array
    {
        $type = (int) $application->short_leave_type;
        if ($type === 3) {
            if ($application->from_time === null || $application->to_time === null) {
                return [];
            }

            return [
                ['2', $this->applicationDateTime($application, $application->from_time)],
                ['1', $this->applicationDateTime($application, $application->to_time)],
            ];
        }

        if ($attendance === null) {
            return [];
        }

        $minutes = max(0, (int) $application->minutes);
        if ($minutes === 0) {
            return [];
        }

        if ($type === 1) {
            if ($attendance->in_time === null || $attendance->shift_in_time === null) {
                return [];
            }

            $firstIn = $attendance->inLog?->datetime
                ? Carbon::parse($attendance->inLog->datetime)
                : Carbon::parse($attendance->in_time);
            $shiftStart = Carbon::parse($attendance->shift_in_time);
            $punchTime = $firstIn->copy()->subMinutes($minutes);

            if ($punchTime->lessThan($shiftStart)) {
                $punchTime = $shiftStart;
            }

            if (! $punchTime->lessThan($firstIn)) {
                return [];
            }

            return [['1', $punchTime]];
        }

        if ($type === 2) {
            if ($attendance->out_time === null || $attendance->shift_out_time === null) {
                return [];
            }

            $lastOut = $attendance->outLog?->datetime
                ? Carbon::parse($attendance->outLog->datetime)
                : Carbon::parse($attendance->out_time);
            $shiftEnd = Carbon::parse($attendance->shift_out_time);
            $punchTime = $lastOut->copy()->addMinutes($minutes);

            if ($punchTime->greaterThan($shiftEnd)) {
                $punchTime = $shiftEnd;
            }

            if (! $punchTime->greaterThan($lastOut)) {
                return [];
            }

            return [['2', $punchTime]];
        }

        return [];
    }

    private function applicationDateTime(ShortLeaveApplication $application, Carbon $time): Carbon
    {
        return Carbon::parse($application->date->toDateString().' '.$time->format('H:i:s'));
    }

    private function reasonFor(ShortLeaveApplication $application): string
    {
        return 'Approved short leave application #'.$application->id;
    }
}
