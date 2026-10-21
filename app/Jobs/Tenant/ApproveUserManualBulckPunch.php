<?php

namespace App\Jobs\Tenant;

use App\Helpers\ManualPunchProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApproveUserManualBulckPunch implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $manualPunch;

    /**
     * Create a new job instance.
     */
    public function __construct($manualPunch)
    {
        $this->manualPunch = $manualPunch;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ManualPunchProcessor::createAttendanceLogs($this->manualPunch);
    }
}
