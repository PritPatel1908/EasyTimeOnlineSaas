<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\AfterShiftRotationProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRotationMusters implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $shiftRotationId)
    {
        $this->onConnection('database_tenant');
    }

    public function handle(): void
    {
        AfterShiftRotationProcessor::processAfterSave($this->shiftRotationId);
    }
}
