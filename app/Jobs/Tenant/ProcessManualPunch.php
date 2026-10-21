<?php

namespace App\Jobs\Tenant;

use App\Helpers\ManualPunchProcessor;
use App\Models\Tenant\ManualPunch as ManualPunchRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessManualPunch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $manualPunchId)
    {
    }

    public function handle(): void
    {
        $manualPunch = ManualPunchRecord::query()->findOrFail($this->manualPunchId);

        ManualPunchProcessor::process($manualPunch);
    }
}
