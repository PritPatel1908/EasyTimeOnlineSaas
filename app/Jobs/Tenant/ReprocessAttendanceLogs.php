<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\InOutMuster;
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
            ->where('is_ignored', false)
            ->when($this->employeeIds !== [], fn ($query) => $query->whereIn('user_id', $this->employeeIds));
    }
}
