<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ApprovalProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessApprovalDecision implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $approvalStatusDetailId,
        public int $actorId,
        public string $decision,
        public ?string $rejectionReason = null,
    ) {}

    public function handle(): void
    {
        ApprovalProcessor::processDecision(
            $this->approvalStatusDetailId,
            $this->actorId,
            $this->decision,
            $this->rejectionReason,
        );
    }
}
