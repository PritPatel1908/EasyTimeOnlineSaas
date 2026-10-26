<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ManualAttendancePunchProcessor;
use App\Models\Tenant\ManualAttendanceApplication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessManualAttendanceApplicationPunches implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $manualAttendanceApplicationId)
    {
        $this->onConnection('database_tenant')->onQueue('processing');
    }

    public function handle(): void
    {
        $application = ManualAttendanceApplication::query()->findOrFail($this->manualAttendanceApplicationId);

        ManualAttendancePunchProcessor::process($application);
    }
}
