<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\ShortLeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApproveShortLeaveApplication implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shortLeaveApplication;

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
        return $this->shortLeaveApplication->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(ShortLeaveApplication $shortLeaveApplication)
    {
        $this->shortLeaveApplication = $shortLeaveApplication;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->shortLeaveApplication->user;
        if ($this->shortLeaveApplication->short_leave_type == 1) {
            $short_leave_duration = $this->shortLeaveApplication->minutes;
        } elseif ($this->shortLeaveApplication->short_leave_type == 2) {
            $short_leave_duration = $this->shortLeaveApplication->minutes;
        } elseif ($this->shortLeaveApplication->short_leave_type == 3) {
            $diff = $this->shortLeaveApplication->from_time->diffInMinutes($this->shortLeaveApplication->to_time);
            // Convert to integer directly - diffInMinutes already returns an integer
            $short_leave_duration = (int) $diff;
        } else {
            $short_leave_duration = '0';
        }

        $user->short_leave_minutes = $user->short_leave_minutes + $short_leave_duration;
        $user->save();
    }
}
