<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\ShortLeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RejectShortLeaveApplication implements ShouldBeUnique, ShouldQueue
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
        // $user = $this->shortLeaveApplication->user;
        // if ($this->shortLeaveApplication->short_leave_type == 1) {
        //     $short_leave_duration = $this->shortLeaveApplication->minutes;
        // } elseif ($this->shortLeaveApplication->short_leave_type == 2) {
        //     $short_leave_duration = $this->shortLeaveApplication->minutes;
        // } elseif ($this->shortLeaveApplication->short_leave_type == 3) {
        //     $diff = $this->shortLeaveApplication->from_time->diffInMinutes($this->shortLeaveApplication->from_time);
        //     $short_leave_duration = sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
        // } else {
        //     $short_leave_duration = '00:00';
        // }

        // $existing_parts = explode(':', $user->short_leave_hours);
        // $existing_minutes = (int)$existing_parts[0] * 60;
        // if (isset($existing_parts[1])) {
        //     $existing_minutes += (int)$existing_parts[1];
        // }

        // // Convert short_leave_duration to minutes if it's a string format
        // if (is_string($short_leave_duration) && strpos($short_leave_duration, ':') !== false) {
        //     $new_parts = explode(':', $short_leave_duration);
        //     $new_minutes = (int)$new_parts[0] * 60 + (int)$new_parts[1];
        // } else {
        //     $new_minutes = (int)$short_leave_duration;
        // }

        // // Add them together
        // $total_minutes = $existing_minutes + $new_minutes;

        // // Convert back to hours:minutes format
        // $user->short_leave_hours = sprintf('%02d:%02d', floor($total_minutes / 60), $total_minutes % 60);
        // $user->save();
    }
}
