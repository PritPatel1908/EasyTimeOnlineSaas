<?php

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\LeaveAccount;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUserManualBalance implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $manualLeaveBalance;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($manualLeaveBalance)
    {
        $this->manualLeaveBalance = $manualLeaveBalance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->manualLeaveBalance->Users as $user) {
            $this->user = null;
            $this->user = $user;

            $userLeaveAccount = LeaveAccount::where('user_id', $user->id)
                ->first();

            if (! $userLeaveAccount) {
                $userLeaveAccount = LeaveAccount::create([
                    'user_id' => $user->id,
                    'code' => $user->code,
                ]);
            }

            GeneralHelper::createOrUpdateTransaction(
                Carbon::parse($this->manualLeaveBalance?->date)->format('Y'),
                get_class($userLeaveAccount),
                $userLeaveAccount->id,
                get_class($this->manualLeaveBalance),
                $this->manualLeaveBalance->id,
                $this->manualLeaveBalance->leave_type_id,
                get_class($this->user),
                $this->user?->id,
                Carbon::parse($this->manualLeaveBalance?->date),
                $this->user?->id,
                $this->manualLeaveBalance->balance,
                0,
                'Credit #'.$this->manualLeaveBalance->leave_type->code.' '.$this->manualLeaveBalance?->date?->copy(),
                $this->user?->id,
                $this->user?->id,
                false
            );
        }
    }
}
