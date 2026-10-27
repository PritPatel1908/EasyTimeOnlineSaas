<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\WeekOffChangeApplicationMusterProcessor;
use App\Models\Tenant\WeekOffChangeApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyWeekOffChangeApplication implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $applicationId) {}

    public function handle(): void
    {
        $application = WeekOffChangeApplication::query()->findOrFail($this->applicationId);

        WeekOffChangeApplicationMusterProcessor::process($application);
    }
}
