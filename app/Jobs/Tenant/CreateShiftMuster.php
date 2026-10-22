<?php

namespace App\Jobs\Tenant;

use App\Helpers\ShiftChangeMusterProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateShiftMuster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shift_change;

    /**
     * Create a new job instance.
     */
    public function __construct($shift_change)
    {
        $this->shift_change = $shift_change;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ShiftChangeMusterProcessor::process($this->shift_change);
    }
}
