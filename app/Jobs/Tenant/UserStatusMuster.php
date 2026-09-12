<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserStatusMuster implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $joinDate;

    // protected $action;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $joinDate)
    {
        $this->user = $user;
        $this->joinDate = $joinDate;
        // $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $joinDate = Carbon::parse($this->joinDate);
        $year = $joinDate->copy()->format('Y');
        $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
        if ($financial_year) {
            $endDate = Carbon::parse($financial_year->end_date);
        } else {
            $endDate = Carbon::parse($joinDate?->copy()->endOfYear());
        }

        while ($joinDate <= $endDate) {
            StatusMuster::updateOrCreate(
                [
                    'date' => $joinDate->format('Y-m-d'),
                    'user_id' => $this->user->id,
                    'is_locked' => false,
                ],
                [
                    'status_master_id' => StatusMaster::where('code', 'AA')->first()->id,
                    'day_count' => 0.0,
                    'year' => $year,
                ]
            );
            $joinDate->addDay();
        }
        // }

        $week_offs = WeekOffMuster::where('user_id', $this->user->id)->where('is_locked', false)->where('wo_type', 1)->orWhere('wo_type', 2)->orWhere('wo_type', 3)->get();
        if ($week_offs) {
            foreach ($week_offs as $week_off) {
                if ($week_off->wo_type == 1) {
                    if ($week_off->date < $endDate) {
                        StatusMuster::updateOrCreate(
                            [
                                'date' => $week_off->date,
                                'user_id' => $this->user->id,
                                'is_locked' => false,
                            ],
                            [
                                'status_master_id' => StatusMaster::where('code', 'WO')->first()->id,
                                'day_count' => $this->user?->category?->is_week_off_paid ? 1.0 : 0.0,
                                'year' => $year,
                            ]
                        );
                    }
                } elseif ($week_off->wo_type == 2) {
                    if ($week_off->date < $endDate) {
                        StatusMuster::updateOrCreate(
                            [
                                'date' => $week_off->date,
                                'user_id' => $this->user->id,
                                'is_locked' => false,
                            ],
                            [
                                'status_master_id' => StatusMaster::where('code', 'WO')->first()->id,
                                'day_count' => $this->user?->category?->is_week_off_paid ? 1.0 : 0.0,
                                'year' => $year,
                            ]
                        );
                    }
                } else {
                    if ($week_off->date < $endDate) {
                        StatusMuster::updateOrCreate(
                            [
                                'date' => $week_off->date,
                                'user_id' => $this->user->id,
                                'is_locked' => false,
                            ],
                            [
                                'status_master_id' => StatusMaster::where('code', 'WO')->first()->id,
                                'day_count' => $this->user?->category?->is_week_off_paid ? 1.0 : 0.0,
                                'year' => $year,
                            ]
                        );
                    }
                }
            }
        }

        $holidays = Holiday::with('locations')->with('categories')->get();
        if ($holidays) {
            foreach ($holidays as $holiday) {
                if ($holiday->locations->contains($this->user?->location_id) && $holiday->categories->contains($this->user?->category_id)) {
                    if ($holiday->date < $endDate) {
                        StatusMuster::updateOrCreate(
                            [
                                'date' => $holiday->date,
                                'user_id' => $this->user->id,
                                'is_locked' => false,
                            ],
                            [
                                'status_master_id' => $this->user?->category?->is_holiday_paid ? StatusMaster::where('code', 'PHL')->first()->id : StatusMaster::where('code', 'PH')->first()->id,
                                'day_count' => $this->user?->category?->is_holiday_paid ? 1.0 : 0.0,
                                'year' => $year,
                            ]
                        );
                    }
                }
            }
        }
        // TODO:After leave calculation
        // Leave Panding
    }
}
