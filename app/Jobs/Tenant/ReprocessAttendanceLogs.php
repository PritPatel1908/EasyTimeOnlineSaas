<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\InOutMuster;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ReprocessAttendanceLogs implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CHUNK_SIZE = 100;

    public int $uniqueFor = 3600;

    /**
     * @param  array<int, int>  $employeeIds
     */
    public function __construct(
        public string $startDateTime,
        public string $endDateTime,
        public array $employeeIds = [],
    ) {}

    public function uniqueId(): string
    {
        return hash('sha256', json_encode([
            tenant('id'),
            $this->startDateTime,
            $this->endDateTime,
            $this->employeeIds,
        ], JSON_THROW_ON_ERROR));
    }

    public function handle(): void
    {
        $this->preparePunchesForReprocessing();
        $this->processPunches();
        $this->createMissingWeekOffHolidayAndLeaveAttendances();
    }

    private function preparePunchesForReprocessing(): void
    {
        $this->matchingLogs()
            ->select(['id', 'datetime'])
            ->chunkById(self::CHUNK_SIZE, function ($logs): void {
                $logIds = $logs->modelKeys();
                $affectedYears = [];

                foreach ($logs as $log) {
                    $punchDate = Carbon::parse($log->datetime);
                    $affectedYears[$punchDate->year] = true;
                    $affectedYears[$punchDate->copy()->subDay()->year] = true;
                }

                DB::transaction(function () use ($affectedYears, $logIds): void {
                    foreach (array_keys($affectedYears) as $year) {
                        $musterTable = 'in_out_musters'.$year;
                        if (Schema::hasTable($musterTable)) {
                            InOutMuster::year($year)
                                ->where('is_locked', false)
                                ->where(function ($query) use ($logIds): void {
                                    $query->whereIn('in_log_id', $logIds)
                                        ->orWhereIn('out_log_id', $logIds);
                                })
                                ->delete();
                        }
                    }

                    foreach (array_keys($affectedYears) as $year) {
                        $attendanceTable = 'attendances'.$year;
                        if (Schema::hasTable($attendanceTable)) {
                            Attendance::year($year)
                                ->where('is_locked', false)
                                ->where(function ($query) use ($logIds): void {
                                    $query->whereIn('in_log_id', $logIds)
                                        ->orWhereIn('out_log_id', $logIds);
                                })
                                ->delete();
                        }
                    }

                    AttendanceLog::query()->whereIn('id', $logIds)->update([
                        'is_calculated' => false,
                        'is_staged' => true,
                        'is_ignored' => false,
                        'has_error' => false,
                    ]);
                });
            }, 'id');
    }

    private function processPunches(): void
    {
        $cursorDate = null;
        $cursorId = 0;
        $hasCursor = false;
        $processed = 0;
        $failed = 0;
        $firstFailure = null;

        while (true) {
            $query = $this->matchingLogs()
                ->where('is_calculated', false)
                ->where('is_staged', true)
                ->orderBy('datetime')
                ->orderBy('id');

            if ($hasCursor) {
                $query->where(function ($query) use ($cursorDate, $cursorId): void {
                    $query->where('datetime', '>', $cursorDate)
                        ->orWhere(function ($query) use ($cursorDate, $cursorId): void {
                            $query->where('datetime', '=', $cursorDate)
                                ->where('id', '>', $cursorId);
                        });
                });
            }

            $logs = $query->limit(self::CHUNK_SIZE)->get(['id', 'datetime']);

            if ($logs->isEmpty()) {
                break;
            }

            foreach ($logs as $log) {
                $cursorDate = $log->getRawOriginal('datetime');
                $cursorId = (int) $log->id;
                $hasCursor = true;

                try {
                    (new ProcessPendingLogs($cursorId))->handle();
                    $processed++;
                } catch (Throwable $exception) {
                    $failed++;
                    $firstFailure ??= $exception;
                    Log::channel('attprocess')->error(
                        'Failed to reprocess attendance log #'.$cursorId,
                        ['exception' => $exception],
                    );
                }
            }
        }

        Log::channel('attprocess')->info(
            'Reprocessed '.$processed.' attendance logs from '.$this->startDateTime.' to '.$this->endDateTime.'; failed: '.$failed,
        );

        if ($firstFailure !== null) {
            throw $firstFailure;
        }
    }

    private function matchingLogs()
    {
        return AttendanceLog::query()
            ->whereBetween('datetime', [$this->startDateTime, $this->endDateTime])
            ->where('is_locked', false)
            ->where('status', '!=', 0)
            ->when($this->employeeIds !== [], fn ($query) => $query->whereIn('user_id', $this->employeeIds));
    }

    private function createMissingWeekOffHolidayAndLeaveAttendances(): void
    {
        $usersQuery = User::query()->where('user_type', 'employee');
        if ($this->employeeIds !== []) {
            $usersQuery->whereIn('id', $this->employeeIds);
        }

        $users = $usersQuery->get(['id', 'location_id', 'category_id']);
        if ($users->isEmpty()) {
            return;
        }

        $startDate = Carbon::parse($this->startDateTime)->startOfDay();
        $endDate = Carbon::parse($this->endDateTime)->startOfDay();
        $attendanceTypes = [];

        if (Schema::hasTable('week_off_musters')) {
            $weekOffMusters = WeekOffMuster::query()
                ->whereIn('user_id', $users->modelKeys())
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('is_locked', false)
                ->whereIn('wo_type', [1, 2, 3])
                ->get(['user_id', 'date', 'wo_type']);

            foreach ($weekOffMusters as $weekOffMuster) {
                $attendanceTypes[$weekOffMuster->user_id][$weekOffMuster->date->toDateString()]['weekoff_type'] = (int) $weekOffMuster->wo_type;
            }
        }

        if (Schema::hasTable('holidays')) {
            $holidays = Holiday::query()
                ->with(['locations:id', 'categories:id'])
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            foreach ($users as $user) {
                $locationIds = array_map('intval', (array) $user->location_id);
                $categoryIds = array_map('intval', (array) $user->category_id);

                if ($locationIds === [] || $categoryIds === []) {
                    continue;
                }

                foreach ($holidays as $holiday) {
                    $hasLocation = $holiday->locations->contains(
                        fn ($location): bool => in_array((int) $location->id, $locationIds, true),
                    );
                    $hasCategory = $holiday->categories->contains(
                        fn ($category): bool => in_array((int) $category->id, $categoryIds, true),
                    );

                    if ($hasLocation && $hasCategory) {
                        $attendanceTypes[$user->id][$holiday->date->toDateString()]['is_holiday'] = true;
                    }
                }
            }
        }

        if (Schema::hasTable('leave_applications') && Schema::hasTable('approval_statuses')) {
            $userIds = $users->modelKeys();
            $approvedLeaveApplications = LeaveApplication::query()
                ->whereHas('users', fn ($query) => $query->whereIn('users.id', $userIds))
                ->whereDate('from_date', '<=', $endDate->toDateString())
                ->whereDate('to_date', '>=', $startDate->toDateString())
                ->whereHas('approval_statuses', fn ($query) => $query->where('approval_status', 'approved'))
                ->whereDoesntHave('approval_statuses', fn ($query) => $query->where('approval_status', '!=', 'approved'))
                ->with(['users', 'leave_type'])
                ->get();

            foreach ($approvedLeaveApplications as $application) {
                if ($application->leave_type === null) {
                    throw new RuntimeException('Approved leave application #'.$application->id.' has no leave type.');
                }

                foreach ($application->users->whereIn('id', $userIds) as $user) {
                    $leaveStart = Carbon::parse($application->from_date)->max($startDate);
                    $leaveEnd = Carbon::parse($application->to_date)->min($endDate);

                    for ($date = $leaveStart->copy(); $date->lte($leaveEnd); $date->addDay()) {
                        $statusCode = $application->leave_type->code;
                        if (
                            $application->is_only_half
                            || ($date->isSameDay($application->from_date) && $application->is_second_half)
                        ) {
                            $statusCode = 'A'.$statusCode;
                        } elseif ($date->isSameDay($application->to_date) && $application->is_first_half) {
                            $statusCode .= 'A';
                        }

                        $attendanceTypes[$user->id][$date->toDateString()]['leave_status_code'] = $statusCode;
                    }
                }
            }
        }

        if ($attendanceTypes === []) {
            return;
        }

        $statusMasterIds = StatusMaster::query()
            ->pluck('id', 'code');

        foreach ($attendanceTypes as $userId => $dates) {
            foreach ($dates as $date => $attendanceType) {
                $year = Carbon::parse($date)->year;
                $attendanceQuery = Attendance::year($year)
                    ->where('user_id', $userId)
                    ->whereDate('date', $date)
                    ->where('is_locked', false);
                $attendance = $attendanceQuery->first();

                if ($attendance !== null && ($attendance->in_log_id !== null || $attendance->out_log_id !== null)) {
                    continue;
                }

                $isHoliday = (bool) ($attendanceType['is_holiday'] ?? false);
                $weekOffType = $attendanceType['weekoff_type'] ?? null;
                $isWeekOff = $weekOffType !== null;
                $isHalfWeekOff = ! $isHoliday && in_array($weekOffType, [2, 3], true);
                $statusCode = $isHoliday
                    ? 'HL'
                    : ($isHalfWeekOff ? ($weekOffType === 3 ? 'AWO' : 'WOA') : 'WO');
                $statusMasterId = $statusMasterIds->get($statusCode);

                if (isset($attendanceType['leave_status_code'])) {
                    $leaveStatusMasterId = $statusMasterIds->get($attendanceType['leave_status_code']);
                    if ($leaveStatusMasterId === null) {
                        throw new RuntimeException(
                            'Status master '.$attendanceType['leave_status_code'].' is missing while creating attendance.',
                        );
                    }

                    $hasWeekOffOrHoliday = $isHoliday || $isWeekOff;
                    $statusMuster = $hasWeekOffOrHoliday && Schema::hasTable('status_musters')
                        ? StatusMuster::query()
                            ->where('user_id', $userId)
                            ->whereDate('date', $date)
                            ->where('is_locked', false)
                            ->first()
                        : null;
                    $musterStatusCode = $statusMuster?->status_master_id
                        ? $statusMasterIds->search($statusMuster->status_master_id, true)
                        : null;

                    if (! $hasWeekOffOrHoliday) {
                        $statusMasterId = $leaveStatusMasterId;
                    } elseif (
                        $statusMuster?->status_master_id !== null
                        && $musterStatusCode !== false
                        && $musterStatusCode !== 'AA'
                    ) {
                        $statusMasterId = $statusMuster->status_master_id;
                    } else {
                        $statusMasterId = $leaveStatusMasterId;
                    }
                }

                if ($statusMasterId === null) {
                    throw new RuntimeException('Status master '.$statusCode.' is missing while creating attendance.');
                }

                Attendance::year($year)->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                        'is_locked' => false,
                    ],
                    [
                        'is_holiday' => $isHoliday,
                        'is_weekoff' => $isWeekOff,
                        'is_half_day' => $isHalfWeekOff,
                        'status_master_id' => $statusMasterId,
                    ],
                );
            }
        }
    }
}
