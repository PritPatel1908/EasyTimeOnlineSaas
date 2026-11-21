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
        $this->createMissingAbsentAttendances();
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
                    (new ProcessPendingLogs($cursorId, suppressChronologicalReprocessing: true))->handle();
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

        $categoryIdsByUser = [];
        $categoryIds = [];
        foreach ($users as $user) {
            $userCategoryIds = array_values(array_unique(array_filter(
                array_map(static fn (mixed $categoryId): int => (int) $categoryId, (array) $user->category_id),
                static fn (int $categoryId): bool => $categoryId > 0,
            )));
            $categoryIdsByUser[$user->id] = $userCategoryIds;
            $categoryIds = array_merge($categoryIds, $userCategoryIds);
        }

        $categoryPaidFlags = [];
        foreach (DB::table('categories')
            ->whereNull('deleted_at')
            ->whereIn('id', array_values(array_unique($categoryIds)))
            ->get(['id', 'is_week_off_paid', 'is_holiday_paid']) as $category) {
            $categoryPaidFlags[(int) $category->id] = [
                'week_off' => (bool) $category->is_week_off_paid,
                'holiday' => (bool) $category->is_holiday_paid,
            ];
        }

        $paidFlagsByUser = [];
        foreach ($users as $user) {
            $userCategoryIds = $categoryIdsByUser[$user->id];
            $paidFlagsByUser[$user->id] = [
                'week_off' => $this->allCategoriesHavePaidFlag($userCategoryIds, $categoryPaidFlags, 'week_off'),
                'holiday' => $this->allCategoriesHavePaidFlag($userCategoryIds, $categoryPaidFlags, 'holiday'),
            ];
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
                $isPaidSpecialDay = (
                    $isHoliday
                    && ($paidFlagsByUser[$userId]['holiday'] ?? false)
                ) || (
                    $isWeekOff
                    && ! $isHalfWeekOff
                    && ($paidFlagsByUser[$userId]['week_off'] ?? false)
                );

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

                $attendanceValues = [
                    'is_holiday' => $isHoliday,
                    'is_weekoff' => $isWeekOff,
                    'is_half_day' => $isHalfWeekOff,
                    'status_master_id' => $statusMasterId,
                ];
                if ($isPaidSpecialDay) {
                    $attendanceValues['is_absent'] = false;
                }

                Attendance::year($year)->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'date' => $date,
                        'is_locked' => false,
                    ],
                    $attendanceValues,
                );
            }
        }
    }

    private function allCategoriesHavePaidFlag(array $categoryIds, array $categoryPaidFlags, string $flag): bool
    {
        if ($categoryIds === []) {
            return false;
        }

        foreach ($categoryIds as $categoryId) {
            if (! ($categoryPaidFlags[$categoryId][$flag] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function createMissingAbsentAttendances(): void
    {
        $usersQuery = User::query()->where('user_type', 'employee');
        if ($this->employeeIds !== []) {
            $usersQuery->whereIn('id', $this->employeeIds);
        }

        $users = $usersQuery->get(['id']);
        if ($users->isEmpty()) {
            return;
        }

        $absentStatusId = StatusMaster::query()->where('code', 'AA')->value('id');
        if ($absentStatusId === null) {
            throw new RuntimeException('Status master AA is missing while creating absent attendance.');
        }

        $startDate = Carbon::parse($this->startDateTime)->startOfDay();
        $endDate = Carbon::parse($this->endDateTime)->startOfDay();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();
            $attendanceTable = Attendance::year($date->year);

            foreach ($users->chunk(self::CHUNK_SIZE) as $userChunk) {
                $userIds = $userChunk->modelKeys();
                $existingUserIds = $attendanceTable->newQuery()
                    ->whereDate('date', $dateString)
                    ->whereIn('user_id', $userIds)
                    ->pluck('user_id')
                    ->map(static fn (mixed $userId): int => (int) $userId)
                    ->all();
                $existingUsers = array_fill_keys($existingUserIds, true);
                $missingUserIds = array_values(array_filter(
                    $userIds,
                    static fn (int $userId): bool => ! isset($existingUsers[$userId]),
                ));

                if ($missingUserIds === []) {
                    continue;
                }

                $timestamp = now();
                $attendanceRows = array_map(
                    static fn (int $userId): array => [
                        'user_id' => $userId,
                        'date' => $dateString,
                        'is_locked' => false,
                        'is_absent' => true,
                        'status_master_id' => $absentStatusId,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ],
                    $missingUserIds,
                );
                $attendanceTable->newQuery()->insert($attendanceRows);

                if (Schema::hasTable('status_musters')) {
                    StatusMuster::query()
                        ->whereIn('user_id', $missingUserIds)
                        ->whereDate('date', $dateString)
                        ->where('is_locked', false)
                        ->update([
                            'status_master_id' => $absentStatusId,
                            'day_count' => 0,
                        ]);
                }
            }
        }
    }
}
