<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\RotationMuster;
use App\Models\Tenant\ShiftRotation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRotationMusters implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shiftIndex = 0;

    protected $previousIndex = 0;

    protected $shift_rotation;

    /**
     * Create a new job instance.
     */
    public function __construct(ShiftRotation $shift_rotation)
    {
        $this->shift_rotation = $shift_rotation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startDate = $this->shift_rotation->start_date;
        $endDate = $startDate->copy()->addYear();
        $shift_rotation_values = $this->shift_rotation->rotation_values->toArray();
        if ($this->shift_rotation->skip_days_on_month_end) {
            $this->createMonthWiseMuster($shift_rotation_values, $startDate, $endDate);
        } else {
            $this->createRotationMuster($shift_rotation_values, $startDate, $endDate, false, true);
        }

        $this->shift_rotation->shift_status = true;
        $this->shift_rotation->save();
    }

    protected function createMonthWiseMuster($shift_rotation_values, $startDate, $endDate)
    {
        $currentStartDate = $startDate->copy();

        // dd($currentStartDate);
        for ($month = 1; $month <= 12; $month++) {
            $currentEndDate = $currentStartDate->copy()->endOfMonth()->addDay();
            if ($currentStartDate > $endDate) {
                $currentEndDate = $endDate;
            }

            $currentStartDate = $this->createRotationMuster($shift_rotation_values, $currentStartDate, $currentEndDate, true);
            // if ($month == 12) {
            //     dd($currentStartDate, $currentEndDate);
            // }
        }
    }

    protected function createRotationMuster($shift_rotation_values, $startDate, $endDate, $skipMonthEnd = false, $skipYearEnd = false)
    {
        $currentStartDate = $startDate->copy();
        while ($currentStartDate < $endDate) {
            $shift_rotation_value = $shift_rotation_values[$this->shiftIndex];
            // var_dump($currentStartDate->format("Y-m-d"));
            $obj_end_date = $currentStartDate->copy()->addDays($shift_rotation_value['days']);
            if ($obj_end_date > $endDate) {
                $obj_end_date = $endDate;
            }

            if ($skipMonthEnd && $startDate->format('m') != $obj_end_date->format('m')) {
                $test_days = $currentStartDate->diffInDays($obj_end_date);
                if ($test_days < $shift_rotation_value['days'] / 2) {
                    // dd($currentStartDate, $endDate, $obj_end_date, $this->previousIndex);
                    $this->shiftIndex = $this->previousIndex;
                    $shift_rotation_value = $shift_rotation_values[$this->shiftIndex];
                }
            }

            if ($skipYearEnd && $startDate->format('Y') != $obj_end_date->format('Y')) {
                $test_days = $currentStartDate->diffInDays($obj_end_date);
                if ($test_days < $shift_rotation_value['days'] / 2) {
                    // dd($currentStartDate, $endDate, $obj_end_date, $this->previousIndex);
                    $this->shiftIndex = $this->previousIndex;
                    $shift_rotation_value = $shift_rotation_values[$this->shiftIndex];
                }
            }

            $currentStartDate = $this->createRotationForShift($shift_rotation_value, $currentStartDate, $obj_end_date);
            $this->previousIndex = $this->shiftIndex;
            $this->shiftIndex = ($this->shiftIndex + 1) % count($shift_rotation_values);
        }

        return $currentStartDate;
    }

    protected function createRotationForShift($shift_rotation_value, $startDate, $endDate)
    {
        while ($startDate < $endDate) {
            RotationMuster::updateOrCreate(
                [
                    'rotation_id' => $shift_rotation_value['shift_rotation_id'],
                    'date' => $startDate,
                ],
                [
                    'shift_id' => $shift_rotation_value['shift_id'],
                ]
            );
            $startDate->addDay();
        }

        return $endDate;
    }
}
