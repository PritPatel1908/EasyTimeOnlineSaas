<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReverseLeaveApplicationAccountBalance implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, int>  $userIds
     */
    public function __construct(
        public int $applicationId,
        public array $userIds,
        public ?int $actorId,
        public bool $onlyIfRejected = true,
    ) {}

    public function handle(): void
    {
        $application = LeaveApplication::withTrashed()->findOrFail($this->applicationId);
        if ($this->onlyIfRejected
            && ! $application->trashed()
            && ! $application->approval_statuses()->where('approval_status', 'rejected')->exists()) {
            return;
        }

        GeneralHelper::reverseLeaveApplicationBalance(
            $application,
            $this->userIds,
            $this->actorId,
        );
    }
}
