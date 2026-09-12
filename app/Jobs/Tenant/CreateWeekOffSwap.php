<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\WeekOffMuster;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWeekOffSwap implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;

    protected $weekOffSwap;

    public function __construct($weekOffSwap)
    {
        $this->weekOffSwap = $weekOffSwap;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $week_date = $this->weekOffSwap->week_date->copy();
        $swap_date = $this->weekOffSwap->swap_date->copy();

        foreach ($this->weekOffSwap->Users()->pluck('user_id')->toArray() as $user_id) {
            $weekOffMuster = WeekOffMuster::where('date', $week_date)
                ->where('user_id', $user_id)
                ->where('is_locked', '!=', 1)
                ->first();
            $weekOffMuster->weekoffable_type = $this->weekOffSwap::class;
            $weekOffMuster->weekoffable_id = $this->weekOffSwap->id;
            $weekOffMuster->wo_type = 0;
            $weekOffMuster->save();
        }

        foreach ($this->weekOffSwap->Users()->pluck('user_id')->toArray() as $user_id) {
            $weekOffUpdate = WeekOffMuster::where('date', $swap_date)
                ->where('user_id', $user_id)
                ->where('is_locked', '!=', 1)
                ->first();
            $weekOffUpdate->weekoffable_type = $this->weekOffSwap::class;
            $weekOffUpdate->weekoffable_id = $this->weekOffSwap->id;
            $weekOffUpdate->wo_type = $this->weekOffSwap->wo_type;
            $weekOffUpdate->is_locked = 1;
            $weekOffUpdate->save();
        }
    }
}
