<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWeekOffMuster implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;

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
        $from_date = $this->weekOffChange->from_date->copy();
        if ($this->weekOffChange->is_forever) {
            $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($financial_year) {
                $to_date = $financial_year->end_date;
            } else {
                $to_date = Carbon::today()->copy()->endOfYear();
            }
        } else {
            $to_date = $this->weekOffChange->to_date;
        }

        $users = $this->weekOffChange->Users()->pluck('user_id')->toArray();
        foreach ($users as $user_id) {
            $fromDate = $from_date->copy();
            while ($fromDate->lte($to_date)) {
                $weekOffMuster = WeekOffMuster::where('date', $fromDate)
                    ->where('user_id', $user_id)
                    ->where('is_locked', '!=', 1)
                    ->first();
                if ($weekOffMuster != null) {
                    $weekOffMuster->weekoffable_type = $this->weekOffChange::class;
                    $weekOffMuster->weekoffable_id = $this->weekOffChange->id;
                    $weekOffMuster->wo_type = 0;
                    $weekOffMuster->save();
                }
                $fromDate->addDay();
            }
        }

        if ($this->weekOffChange->is_forever) {
            $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($financial_year) {
                $toDate = $financial_year->end_date;
            } else {
                $toDate = $this->weekOffChange->from_date->copy()->endOfYear();
            }
        } else {
            $toDate = $this->weekOffChange->to_date;
        }

        $currentDate = $this->weekOffChange->from_date->copy();
        while ($currentDate->lte($toDate)) {
            $this->updateMuster($currentDate, $toDate, $users);
            $currentDate->addDays(6);
        }
    }

    public function updateMuster($date, $toDate, $users)
    {
        foreach ($this->weekOffChange->WeekOffChangeDetails as $detail) {
            $wd = $detail->week_days;
            if ($date->dayOfWeek == $wd) {
                $wo = $date;
            } else {
                $wo = $date->next($wd);
            }
            if ($wo->gt($toDate)) {
                continue; // skip if date is greater than to_date
            }
            $weekOfMonth = $wo->weekOfMonth;
            $valid = false;
            switch ($weekOfMonth) {
                case 1:
                    $valid = $detail->first_week;
                    break;
                case 2:
                    $valid = $detail->second_week;
                    break;
                case 3:
                    $valid = $detail->third_week;
                    break;
                case 4:
                    $valid = $detail->fourth_week;
                    break;
                case 5:
                    $valid = $detail->fifth_week;
                    break;
                default:
                    $valid = false;
                    break;
            }

            if (! $valid) {
                continue; // Skip this day
            }
            foreach ($users as $user_id) {
                $weekOffUpdate = WeekOffMuster::where('date', $wo)
                    ->where('user_id', $user_id)
                    ->where('is_locked', '!=', 1)
                    // ->where('weekoffable_type', $this->weekOffChange::class)
                    // ->where('weekoffable_id', $this->weekOffChange->id)
                    ->first();
                if ($weekOffUpdate != null) {
                    $weekOffUpdate->wo_type = $detail->wo_type;
                    $weekOffUpdate->weekoffable_type = $this->weekOffChange::class;
                    $weekOffUpdate->weekoffable_id = $this->weekOffChange->id;
                    $weekOffUpdate->save();
                }
            }
        }
    }
}
