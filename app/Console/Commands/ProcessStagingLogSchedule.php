<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\ProcessPendingLogsBatch;
use App\Models\Tenant\AttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessStagingLogSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-staging-log-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process staging_log schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AttendanceLog::whereNull('user_id')->chunkById(100, function ($logs) {
            foreach ($logs as $log) {
                $log->user_id = $log->user?->id;
                $log->save();
            }
        });
        Log::channel('attprocess')->debug('Dispatching chunked staging job');
        ProcessPendingLogsBatch::dispatch(true)->onQueue('low');
    }
}
