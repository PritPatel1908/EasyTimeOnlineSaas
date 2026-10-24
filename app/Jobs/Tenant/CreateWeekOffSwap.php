<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\WeekOffSwap;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Support\WeekOffSwapMusterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWeekOffSwap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected WeekOffSwap|WeekOffSwapApplication $weekOffSwap)
    {
    }

    public function handle(): void
    {
        WeekOffSwapMusterService::applySwap($this->weekOffSwap);
    }
}
