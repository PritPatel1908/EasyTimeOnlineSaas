<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\RotationMuster;
use App\Models\Tenant\ShiftMuster;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateShiftMuster implements ShouldBeUnique, ShouldQueue
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
        $users = $this->shift_change->Users()->pluck('user_id')->toArray();

        $from_date = $this->shift_change->from_date;
        $fromDate = $from_date->copy();
        if ($this->shift_change->is_forever) {
            $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($financial_year) {
                $toDate = $financial_year->end_date;
            } else {
                $toDate = Carbon::today()->copy()->endOfYear();
            }
        } else {
            $toDate = $this->shift_change->to_date;
        }

        while ($fromDate <= $toDate) {
            foreach ($users as $user_id) {
                $user = User::find($user_id);
                if ($this->shift_change->shift_type == 'auto') {
                    ShiftMuster::updateOrCreate(
                        [
                            'date' => $fromDate,
                            'user_id' => $user_id,
                            'is_locked' => false,
                        ],
                        [
                            'is_auto' => $this->shift_change->shift_type == 'auto' ? true : false,
                            'shift' => $this->shift_change->shifts()->pluck('shift_id')->map(function ($id) {
                                return (int) $id;
                            })->toArray(),
                            'shiftchangeable_type' => $this->shift_change::class,
                            'shiftchangeable_id' => $this->shift_change->id,
                        ]
                    );
                    // $fromDate->addDay();
                } elseif ($this->shift_change->shift_type == 'rotational') {
                    // $user_rotational_shift = $this->shift_change->shift_rotation_id;
                    ShiftMuster::updateOrCreate(
                        [
                            'date' => $fromDate,
                            'user_id' => $user_id,
                            'is_locked' => false,
                        ],
                        [
                            'shift' => RotationMuster::where('rotation_id', $this->shift_change->shift_rotation_id)->where('date', $fromDate)->first()->shift_id,
                            'shiftchangeable_type' => $this->shift_change::class,
                            'shiftchangeable_id' => $this->shift_change->id,
                        ]
                    );
                    // $fromDate->addDay();
                } else {
                    ShiftMuster::updateOrCreate(
                        [
                            'date' => $fromDate,
                            'user_id' => $user_id,
                            'is_locked' => false,
                        ],
                        [
                            'shift' => $this->shift_change->shifts()->pluck('shift_id')->map(function ($id) {
                                return (int) $id;
                            })->toArray(),
                            'shiftchangeable_type' => $this->shift_change::class,
                            'shiftchangeable_id' => $this->shift_change->id,
                        ]
                    );
                }

                $user->shift_status = true;
                $user->save();
            }
            $fromDate->addDay();
        }
    }
}
