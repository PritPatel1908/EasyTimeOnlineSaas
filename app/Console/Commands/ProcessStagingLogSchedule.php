<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\ProcessPendingLogs;
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
        AttendanceLog::whereNull('user_id')->get()->each(function ($log) {
            $log->user_id = $log->user?->id;
            $log->save();
        });
        Log::channel('attprocess')->debug('Dispatching Staging jobs');
        AttendanceLog::where('is_calculated', false)
            ->where('is_ignored', false)
            ->where('has_error', false)
            ->where('is_staged', true)
            ->where('is_locked', false)
            ->whereNull('deleted_at')->orderBy('datetime')->orderBy('id')->chunk(100, function ($logs) {
                foreach ($logs as $log) {
                    $p = ProcessPendingLogs::dispatch($log)->onQueue('low');
                    Log::channel('attprocess')->debug('Stage Process dispatched for ##'.$log->id.'|'.$log->user_code.'|'.$log->datetime);
                }
                Log::channel('attprocess')->debug('Stage Log Dispatched '.count($logs).' jobs');
            });
    }
}
