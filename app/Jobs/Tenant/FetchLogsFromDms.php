<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\DmsSetting;
use App\Models\Tenant\GeneralConfiguration;
// use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// use Log;

class FetchLogsFromDms implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lastId;

    protected $newLastId;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->lastId = DmsSetting::where('key', 'dms_last_sync_id')->first()->value;
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->lastId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $config = config('database.connections.dmssql');
        $config['driver'] = config('dms_db_driver'); // $config['dms_db_driver'];
        $config['host'] = config('dms_db_host'); // $config['dms_db_host'];
        $config['port'] = config('dms_db_port'); // $config['dms_db_port'];
        $config['database'] = config('dms_db_database'); // $config['dms_db_database'];
        $config['username'] = config('dms_db_username'); // $config['dms_db_username'];
        $config['password'] = config('dms_db_password'); // $config['dms_db_password'];
        config(['database.connections.dmssql' => $config]);
        $this->newLastId = $this->lastId;
        DB::connection('dmssql')
            ->table('iclock_transaction')
            ->select(
                'iclock_transaction.id',
                'iclock_transaction.emp_id',
                'iclock_transaction.emp_code',
                'iclock_transaction.punch_time',
                'iclock_transaction.verify_type',
                'iclock_transaction.terminal_id',
                'iclock_terminal.device_direction as punch_type',
                DB::raw('CASE WHEN personnel_area.id IS NULL THEN iclock_terminal.area_id ELSE personnel_area.id END as dms_area_id')
                // 'iclock_terminal.area_id as dms_area_id',
            )
            // ->addSelect()
            ->join('iclock_terminal', 'iclock_transaction.terminal_id', '=', 'iclock_terminal.id')
            ->leftJoin('personnel_area', 'personnel_area.area_name', '=', 'iclock_transaction.area_alias')
            ->where('iclock_transaction.id', '>', $this->lastId)
            ->orderBy('iclock_transaction.id')
            ->chunk(100, function ($logs) {
                foreach ($logs as $log) {
                    $this->newLastId = $log->id;
                    $attendance_log = AttendanceLog::createOrFirst(
                        [
                            'dms_log_id' => $log->id,
                            'is_locked' => false,
                        ],
                        [
                            'user_code' => $log->emp_code,
                            'dms_log_id' => $log->id,
                            'dms_user_id' => $log->emp_id,
                            'dms_area_id' => $log->dms_area_id,
                            'dms_device_id' => $log->terminal_id,
                            'datetime' => $log->punch_time,
                            'punch_type' => $log->punch_type,
                            'verify_type' => $log->verify_type,

                        ]
                    );

                    // if ($attendance_log->isVisitor()) {
                    //     // ProcessVisitorLogs::dispatch($attendance_log)->onQueue('processing');
                    //     // Log::channel('attprocess')->debug('ProcessVisitorLogs dispatched for ##' . $attendance_log->id . '|' . $attendance_log->user_code . '|' . $attendance_log->datetime);
                    // } else {
                    //     ProcessPendingLogs::dispatch($attendance_log)->onQueue('processing');
                    //     Log::channel('attprocess')->debug('ProcessPendingLogs dispatched for ##' . $attendance_log->id . '|' . $attendance_log->user_code . '|' . $attendance_log->datetime);
                    // }
                    ProcessPendingLogs::dispatch($attendance_log)->onQueue('processing');
                    Log::channel('attprocess')->debug('ProcessPendingLogs dispatched for ##'.$attendance_log->id.'|'.$attendance_log->user_code.'|'.$attendance_log->datetime);

                    DmsSetting::where('key', 'dms_last_sync_id')->update(['value' => $log->id]);
                }
            });
        if ($this->lastId == $this->newLastId) {
            Log::channel('attprocess')->debug('No new logs found');
        } else {
            Log::channel('attprocess')->debug('New Log Added to Job Queue');
            GeneralConfiguration::updateOrCreate(
                ['key' => 'last_log_process_time'],
                ['value' => date('Y-m-d H:i:s')]
            );
        }
    }
}
