<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\AttendanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestoreAttendanceLogs implements ShouldBeUnique, ShouldQueue
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
        AttendanceLog::onlyTrashed()
            ->whereBetween('datetime', [$this->startDateTime, $this->endDateTime])
            ->when($this->employeeIds !== [], fn ($query) => $query->whereIn('user_id', $this->employeeIds))
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($logs): void {
                foreach ($logs as $log) {
                    $log->status = 1;
                    $log->is_calculated = false;
                    $log->is_ignored = false;
                    $log->restore();
                }
            });
    }
}
