<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\DmsSetting;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApproveUserManualPunch implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $manualPunch;

    /**
     * Create a new job instance.
     */
    public function __construct($manualPunch)
    {
        $this->manualPunch = $manualPunch;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lastId = DmsSetting::where('key', 'dms_last_sync_id')->first()->value;
        $dms_log_id = $lastId + 1;
        $datetime = $this->manualPunch->punch_date->format('Y-m-d').' '.$this->manualPunch->punch_time->format('H:i:s.u');
        foreach ($this->manualPunch->Users as $user) {
            $attendance_log = AttendanceLog::createOrFirst(
                [
                    'dms_log_id' => $dms_log_id,
                    'is_locked' => false,
                ],
                [
                    'user_code' => $user->code,
                    'dms_log_id' => $dms_log_id,
                    'dms_user_id' => $user->dms_user_id,
                    'dms_area_id' => $user->areas->first()->dms_area_id,
                    'dms_device_id' => $user->areas->first()->machines->first()->dms_device_id,
                    'datetime' => $datetime,
                    'punch_type' => $this->manualPunch->punch_type,
                    'is_manual' => true,
                ]
            );

            if ($this->manualPunch->punch_type == '2') {
                $attendance_log->is_out = true;
                $attendance_log->save();
            }

            ProcessPendingLogs::dispatch($attendance_log)->onQueue('processing');
            Log::channel('attprocess')->debug('ProcessManualPunch dispatched for ##'.$attendance_log->id.'|'.$attendance_log->user_code.'|'.$attendance_log->datetime);

            DmsSetting::where('key', 'dms_last_sync_id')->update(['value' => $dms_log_id]);
        }
    }
}
