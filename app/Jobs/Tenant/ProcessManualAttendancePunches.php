<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ManualAttendancePunchProcessor;
use App\Models\Tenant\ManualAttendance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessManualAttendancePunches implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $manualAttendanceId)
    {
        $this->onConnection('database_tenant')->onQueue('processing');
    }

    public function handle(): void
    {
        $attendance = ManualAttendance::query()->findOrFail($this->manualAttendanceId);

        ManualAttendancePunchProcessor::process($attendance);
    }
}
