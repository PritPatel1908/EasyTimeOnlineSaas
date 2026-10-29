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
use Illuminate\Support\Facades\DB;

class DeleteAttendanceLogs implements ShouldBeUnique, ShouldQueue
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
        AttendanceLog::query()
            ->whereBetween('datetime', [$this->startDateTime, $this->endDateTime])
            ->where('is_locked', false)
            ->where('status', 1)
            ->when($this->employeeIds !== [], fn ($query) => $query->whereIn('user_id', $this->employeeIds))
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($logs): void {
                DB::transaction(function () use ($logs): void {
                    foreach ($logs as $log) {
                        $log->status = 0;
                        $log->is_calculated = false;
                        $log->is_ignored = true;
                        $log->save();
                        $log->delete();
                    }
                });
            });
    }
}
