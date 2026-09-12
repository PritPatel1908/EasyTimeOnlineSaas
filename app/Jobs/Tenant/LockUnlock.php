<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\InOutMuster;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LockUnlock implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $from;

    protected $to;

    protected $userId;

    protected $action;

    /**
     * Create a new job instance.
     */
    public function __construct($fromDate, $toDate, $users, $action)
    {
        $this->from = $fromDate;
        $this->to = $toDate;
        $this->userId = $users;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->action == 'lock') {
            $this->lockunlock(false, true);
        } else {
            $this->lockunlock(true, false);
        }
    }

    public function lockunlock($checkValue, $updateValue)
    {
        if (! $this->userId) {
            AttendanceLog::where('datetime', '>=', Carbon::parse($this->from)->format('Y-m-d 00:00:00'))
                ->where('datetime', '<=', Carbon::parse($this->to)->format('Y-m-d 23:59:59'))
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        } else {
            AttendanceLog::where('datetime', '>=', Carbon::parse($this->from)->format('Y-m-d 00:00:00'))
                ->where('datetime', '<=', Carbon::parse($this->to)->format('Y-m-d 23:59:59'))
                ->where('user_id', $this->userId)
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        }

        $attendance = (new Attendance(Carbon::parse($this->from)->format('Y')));
        if (! $this->userId) {
            $attendance
                ->whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        } else {
            $attendance
                ->where('user_id', $this->userId)
                ->whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        }

        $in_out_muster = (new InOutMuster(Carbon::parse($this->from)->format('Y')));
        if (! $this->userId) {
            $in_out_muster
                ->whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        } else {
            $in_out_muster
                ->where('user_id', $this->userId)
                ->whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        }

        if (! $this->userId) {
            ShiftMuster::whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        } else {
            ShiftMuster::whereBetween('date', [$this->from, $this->to])
                ->where('user_id', $this->userId)
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        }

        if (! $this->userId) {
            StatusMuster::whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        } else {
            StatusMuster::whereBetween('date', [$this->from, $this->to])
                ->where('user_id', $this->userId)
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        }

        if (! $this->userId) {
            WeekOffMuster::whereBetween('date', [$this->from, $this->to])
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        } else {
            WeekOffMuster::whereBetween('date', [$this->from, $this->to])
                ->where('user_id', $this->userId)
                ->where('is_locked', $checkValue)
                ->update(['is_locked' => $updateValue]);
        }
    }
}
