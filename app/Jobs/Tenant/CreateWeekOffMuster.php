<?php

namespace App\Jobs\Tenant;

use App\Helpers\WeekOffChangeMusterProcessor;
use App\Models\Tenant\WeekOffChange;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWeekOffMuster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $weekOffChange;

    public function __construct($weekOffChange)
    {
        $this->weekOffChange = $weekOffChange;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        WeekOffChangeMusterProcessor::process($this->weekOffChange);
    }
}
