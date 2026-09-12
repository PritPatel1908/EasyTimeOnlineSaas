<?php

namespace App\Jobs\Tenant;

use App\Mail\ApproveRejectMail;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ApproveRejectApplicationMail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $emailRecord;

    protected $record;

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
        return $this->emailRecord->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct($emailRecord, $record)
    {
        $this->emailRecord = $emailRecord;
        $this->record = $record;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (get_class($this->emailRecord) === "App\Models\Tenant\ShiftChangeApplication") {
            $users = $this->emailRecord->Users()->pluck('user_id')->toArray();
            foreach ($users as $user_id) {
                $user = User::find($user_id);
                Mail::to($user->email)->send(new ApproveRejectMail($this->emailRecord, $user, $this->record->main_status->approval_status));
            }
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\WeekOffChangeApplication") {
            $users = $this->emailRecord->Users()->pluck('user_id')->toArray();
            foreach ($users as $user_id) {
                $user = User::find($user_id);
                Mail::to($user->email)->send(new ApproveRejectMail($this->emailRecord, $user, $this->record->main_status->approval_status));
            }
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\WeekOffSwapApplication") {
            $users = $this->emailRecord->Users()->pluck('user_id')->toArray();
            foreach ($users as $user_id) {
                $user = User::find($user_id);
                Mail::to($user->email)->send(new ApproveRejectMail($this->emailRecord, $user, $this->record->main_status->approval_status));
            }
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\ManualPunch") {
            foreach ($this->emailRecord->Users as $user) {
                Mail::to($user->email)->send(new ApproveRejectMail($this->emailRecord, $user, $this->record->main_status->approval_status));
            }
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\ManualAttendance") {
            Mail::to($this->emailRecord->user->email)->send(new ApproveRejectMail($this->emailRecord, $this->emailRecord->user, $this->record->main_status->approval_status));
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\LeaveApplication") {
            $user = User::find($this->emailRecord->user_id);
            Mail::to($user->email)->send(new ApproveRejectMail($this->emailRecord, $user, $this->record->main_status->approval_status));
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\ShortLeaveApplication") {
            Mail::to($this->emailRecord->user->email)->send(new ApproveRejectMail($this->emailRecord, $this->emailRecord->user, $this->record->main_status->approval_status));
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\Coff") {
            Mail::to($this->emailRecord->user->email)->send(new ApproveRejectMail($this->emailRecord, $this->emailRecord->user, $this->record->main_status->approval_status));
        } elseif (get_class($this->emailRecord) === "App\Models\Tenant\OutDuty") {
            Mail::to($this->emailRecord->user->email)->send(new ApproveRejectMail($this->emailRecord, $this->emailRecord->user, $this->record->main_status->approval_status));
        }
    }
}
