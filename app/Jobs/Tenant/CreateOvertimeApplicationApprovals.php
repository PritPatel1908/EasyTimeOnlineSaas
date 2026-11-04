<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ApprovalProcessor;
use App\Models\Tenant\OvertimeApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOvertimeApplicationApprovals implements ShouldQueue
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
        $application = OvertimeApplication::query()->findOrFail($this->applicationId);

        ApprovalProcessor::CreateOvertimeApplicationApprovals(
            $application,
            $this->approvalFlowIds,
            $this->actorId,
        );
    }
}
