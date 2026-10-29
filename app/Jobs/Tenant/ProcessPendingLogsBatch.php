<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\AttendanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPendingLogsBatch implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CHUNK_SIZE = 100;

    public int $uniqueFor = 3600;

    public function __construct(public bool $staged = false)
    {
    }

    public function uniqueId(): string
    {
        return (string) tenant('id') . ':' . ($this->staged ? 'staged' : 'pending');
    }

    public function handle(): void
    {
        $cursorDate = null;
        $cursorId = 0;
        $hasCursor = false;
        $processed = 0;
        $failed = 0;
        $firstFailure = null;

        while (true) {
            $query = AttendanceLog::query()
                ->where('is_calculated', false)
                ->where('is_ignored', false)
                ->where('has_error', false)
                ->where('is_staged', $this->staged)
                ->where('is_locked', false)
                ->where('status', '!=', 0);

            if ($hasCursor) {
                $query->where(function ($query) use ($cursorDate, $cursorId): void {
                    if ($cursorDate === null) {
                        $query->where(function ($query) use ($cursorId): void {
                            $query->whereNull('datetime')->where('id', '>', $cursorId);
                        })->orWhereNotNull('datetime');

                        return;
                    }

                    $query->where('datetime', '>', $cursorDate)
                        ->orWhere(function ($query) use ($cursorDate, $cursorId): void {
                            $query->where('datetime', '=', $cursorDate)
                                ->where('id', '>', $cursorId);
                        });
                });
            }

            $logs = $query->orderBy('datetime')->orderBy('id')->limit(self::CHUNK_SIZE)->get();

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
                } catch (\Throwable $exception) {
                    $failed++;
                    $firstFailure ??= $exception;
                    Log::channel('attprocess')->error(
                        'Failed to process attendance log #'.$cursorId,
                        ['exception' => $exception]
                    );
                }
            }
        }

        Log::channel('attprocess')->debug(
            'Processed '.$processed.' '.($this->staged ? 'staged ' : '').'attendance logs in chunks; failed: '.$failed
        );

        if ($firstFailure !== null) {
            throw $firstFailure;
        }
    }
}
