<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ApprovalProcessor;
use App\Models\Tenant\ShortLeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateShortLeaveApplicationApprovals implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public function __construct(
        public int $applicationId,
        public array $approvalFlowIds,
        public int $actorId,
    ) {}

    public function handle(): void
    {
        $application = ShortLeaveApplication::query()->findOrFail($this->applicationId);

        ApprovalProcessor::CreateShortLeaveApplicationApprovals(
            $application,
            $this->approvalFlowIds,
            $this->actorId,
        );

        SendApplicationStatusEmails::dispatch(
            ShortLeaveApplication::class,
            (int) $application->id,
            'initial',
        )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
    }
}
