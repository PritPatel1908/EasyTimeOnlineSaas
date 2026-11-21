<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Tenant\RunScheduledTenantCron;
use App\Models\Central\Tenant;
use App\Models\Central\TenantCronJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchDueTenantCronJobs extends Command
{
    protected $signature = 'app:dispatch-due-tenant-cronjobs';

    protected $description = 'Queue due tenant cron tasks';

    public function handle(): int
    {
        $dueIds = TenantCronJob::query()
            ->whereIn('task_key', array_keys(TenantCronJob::TASKS))
            ->where('enabled', true)
            ->where('next_run_at', '<=', now())
            ->orderBy('id')
            ->pluck('id');

        foreach ($dueIds as $cronJobId) {
            $cronJob = $this->claimDueCronJob((int) $cronJobId);

            if ($cronJob === null) {
                continue;
            }

            try {
                $tenant = Tenant::query()->find($cronJob->tenant_id);
                if ($tenant === null) {
                    throw new \RuntimeException('The tenant for this scheduled task no longer exists.');
                }

                tenancy()->initialize($tenant);

                try {
                    RunScheduledTenantCron::dispatch($cronJob->id, $cronJob->task_key);
                } finally {
                    tenancy()->end();
                }

                $this->info("Queued {$cronJob->name} task for tenant {$cronJob->tenant_id}.");
            } catch (Throwable $exception) {
                TenantCronJob::query()
                    ->whereKey($cronJob->id)
                    ->update([
                        'last_status' => 'failed',
                        'last_error' => $exception->getMessage(),
                    ]);

                Log::error('Unable to queue a scheduled tenant cron task.', [
                    'cron_job_id' => $cronJob->id,
                    'tenant_id' => $cronJob->tenant_id,
                    'exception' => $exception,
                ]);

                $this->error("Unable to queue cron task {$cronJob->id}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    private function claimDueCronJob(int $cronJobId): ?TenantCronJob
    {
        return DB::connection('central')->transaction(function () use ($cronJobId): ?TenantCronJob {
            $cronJob = TenantCronJob::query()
                ->whereKey($cronJobId)
                ->lockForUpdate()
                ->first();

            if (
                $cronJob === null
                || ! $cronJob->enabled
                || $cronJob->next_run_at === null
                || $cronJob->next_run_at->isFuture()
            ) {
                return null;
            }

            $cronJob->update([
                'next_run_at' => TenantCronJob::nextRunAt(
                    $cronJob->schedule_type,
                    $cronJob->schedule_time,
                    $cronJob->interval_minutes,
                ),
                'last_status' => 'queued',
                'last_error' => null,
            ]);

            return $cronJob;
        });
    }
}
