<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function (): void {
            $application = ShortLeaveApplication::query()
                ->with('approval_statuses')
                ->lockForUpdate()
                ->findOrFail($this->shortLeaveApplication->id);
            if ((int) $application->status !== 1 || $application->overallApprovalStatus() !== 'approved') {
                return;
            }

            $user = User::query()->lockForUpdate()->findOrFail($application->user_id);
            $user->short_leave_minutes = (int) $user->short_leave_minutes + $application->creditMinutes();
            $user->save();
        });
    }
}
