<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\FetchLogsFromDms;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\AttendanceLog;
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
            Log::channel('attprocess')->debug('Dispatching jobs for pending logs');
            AttendanceLog::where('is_calculated', false)
                ->where('is_ignored', false)
                ->where('has_error', false)
                ->where('is_staged', false)
                ->where('is_locked', false)
                ->whereNull('deleted_at')->orderBy('datetime')->orderBy('id')->chunk(100, function ($logs) {
                    foreach ($logs as $log) {
                        Log::channel('attprocess')->debug($log->datetime);
                        if ($log->is_staged) {
                            continue;
                        }
                        // if ($log->isVisitor()) {
                        //     // TODO:remove comment if requried visitor log
                        //     // $p = ProcessVisitorLogs::dispatch($log)->onQueue('processing');
                        // } else {
                        //     $p = ProcessPendingLogs::dispatch($log)->onQueue('processing');
                        // }
                        ProcessPendingLogs::dispatch($log)->onQueue('processing');
                        Log::channel('attprocess')->debug('Process dispatched for ##'.$log->id.'|'.$log->user_code.'|'.$log->datetime);
                    }
                    Log::channel('attprocess')->debug('Dispatched '.count($logs).' jobs');
                });

            GeneralConfiguration::updateOrCreate(
                ['key' => 'last_log_process_time'],
                ['value' => date('Y-m-d H:i:s')]
            );
        }
    }
}
