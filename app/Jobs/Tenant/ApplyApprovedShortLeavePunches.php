<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Services\Tenant\ShortLeaveAttendancePunchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyApprovedShortLeavePunches implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 3600;

    public function __construct(public int $applicationId) {}

    public function uniqueId(): string
    {
        return (string) tenant('id').':'.$this->applicationId;
    }

    public function handle(ShortLeaveAttendancePunchService $service): void
    {
        $service->createForApprovedApplication($this->applicationId);
    }
}
