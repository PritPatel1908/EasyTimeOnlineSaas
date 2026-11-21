<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Central\TenantCronJob;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Throwable;

class RunScheduledTenantCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $cronJobId,
        public ?string $taskKey = null,
    ) {
        $this->onConnection('database_tenant')->onQueue('processing');
    }

    public function handle(): void
    {
        $cronJob = TenantCronJob::query()->find($this->cronJobId);

        if ($cronJob === null) {
            return;
        }

        if (! $cronJob->enabled) {
            $cronJob->update(['last_status' => 'paused']);

            return;
        }

        $cronJob->update([
            'last_run_at' => now(),
            'last_status' => 'running',
            'last_error' => null,
        ]);

        $taskKey = $this->taskKey ?? $cronJob->task_key;
        $command = match ($taskKey) {
            TenantCronJob::TASK_LEAVE_MUSTER => 'app:create-leave-muster',
            TenantCronJob::TASK_PUNCH_PROCESS => 'app:process-log-schedule',
            default => throw new RuntimeException('The scheduled task is not allowlisted.'),
        };
        $parameters = $taskKey === TenantCronJob::TASK_PUNCH_PROCESS
            ? ['--local-only' => true]
            : [];
        $exitCode = Artisan::call($command, $parameters);

        if ($exitCode !== Command::SUCCESS) {
            throw new RuntimeException(
                $command.' exited with code '.$exitCode.'. '.trim(Artisan::output()),
            );
        }

        $cronJob->update([
            'last_status' => 'dispatched',
            'last_error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        TenantCronJob::query()
            ->whereKey($this->cronJobId)
            ->update([
                'last_status' => 'failed',
                'last_error' => $exception?->getMessage() ?? 'The scheduled task failed without an exception message.',
            ]);
    }
}
