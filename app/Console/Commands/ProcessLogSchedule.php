<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\FetchLogsFromDms;
use App\Jobs\Tenant\ProcessPendingLogsBatch;
use App\Models\Tenant\GeneralConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessLogSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-log-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process log schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('fetch_att_logs', 0) != 1) {
            Log::channel('attprocess')->debug('Fetching New jobs for pending logs');
            FetchLogsFromDms::dispatch()->onQueue('processing');
        } else {
            Log::channel('attprocess')->debug('Dispatching chunked job for pending logs');
            ProcessPendingLogsBatch::dispatch()->onQueue('processing');

            GeneralConfiguration::updateOrCreate(
                ['key' => 'last_log_process_time'],
                ['value' => date('Y-m-d H:i:s')]
            );
        }
    }
}
