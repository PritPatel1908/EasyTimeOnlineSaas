<?php

namespace App\Jobs\Tenant;

use App\Helpers\ManualPunchProcessor;
use App\Models\Tenant\ManualPunchApplication;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ApproveUserManualPunch implements ShouldBeUnique, ShouldQueue
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
        if (! $this->manualPunch instanceof ManualPunchApplication) {
            throw new RuntimeException('The approved manual punch application could not be loaded.');
        }

        $application = ManualPunchApplication::query()
            ->with(['Users', 'approval_statuses'])
            ->findOrFail($this->manualPunch->id);
        if ($application->approval_statuses->isEmpty()) {
            throw new RuntimeException('The manual punch application has no approval requests.');
        }

        if ($application->approval_statuses->contains(
            static fn ($status): bool => $status->approval_status !== 'approved',
        )) {
            return;
        }

        ManualPunchProcessor::createAttendanceLogs($application);
    }
}
