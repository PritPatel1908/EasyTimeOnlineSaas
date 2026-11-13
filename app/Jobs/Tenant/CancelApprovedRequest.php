<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Services\Tenant\ApprovedRequestCancellationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CancelApprovedRequest implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $approvalStatusDetailId)
    {
        $this->onConnection('database_tenant')->onQueue('processing');
    }

    public function handle(ApprovedRequestCancellationService $cancellations): void
    {
        $cancellations->cancel($this->approvalStatusDetailId);
    }

    public function failed(Throwable $exception): void
    {
        app(ApprovedRequestCancellationService::class)
            ->markFailed($this->approvalStatusDetailId, $exception);
    }
}
