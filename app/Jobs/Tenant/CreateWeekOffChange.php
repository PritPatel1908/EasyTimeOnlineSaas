<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffChangeDetail;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CreateWeekOffChange implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;

    protected $user;

    protected $action;

    protected $fromDate;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $fromDate)
    {
        $this->user = $user;
        $this->fromDate = $fromDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fromDate = Carbon::parse($this->fromDate);
        $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
        if ($financial_year) {
            $toDate = $financial_year->end_date;
        } else {
            $toDate = Carbon::today()->copy()->endOfYear();
        }

        if (GeneralConfiguration::where('key', 'create_new_week_of_change_in_user_edit') && GeneralConfiguration::where('key', 'create_new_week_of_change_in_user_edit')->first()->value == 1) {
            $week_off_change = WeekOffChange::create([
                'from_date' => $fromDate->copy(),
                'is_forever' => true,
                'to_date' => $toDate->copy(),
                'location_id' => $this->user?->location_id,
                'company_id' => $this->user?->company_id,
            ]);

            foreach ($this->user->user_week_offs as $user_week_off) {
                WeekOffChangeDetail::create([
                    'week_off_change_id' => $week_off_change->id,
                    'week_days' => $user_week_off->week_days,
                    'wo_type' => $user_week_off->wo_type,
                    'first_week' => $user_week_off->first_week,
                    'second_week' => $user_week_off->second_week,
                    'third_week' => $user_week_off->third_week,
                    'fourth_week' => $user_week_off->fourth_week,
                    'fifth_week' => $user_week_off->fifth_week,
                ]);
            }
        } else {
            $week_off_change = WeekOffChange::where('id', $this->user?->week_off_change_id)->first();
            if (! $week_off_change) {
                $week_off_change = WeekOffChange::create([
                    'from_date' => $fromDate->copy(),
                    'is_forever' => true,
                    'to_date' => $toDate->copy(),
                    'location_id' => $this->user?->location_id,
                    'company_id' => $this->user?->company_id,
                ]);

                foreach ($this->user->user_week_offs as $user_week_off) {
                    WeekOffChangeDetail::create([
                        'week_off_change_id' => $week_off_change->id,
                        'week_days' => $user_week_off->week_days,
                        'wo_type' => $user_week_off->wo_type,
                        'first_week' => $user_week_off->first_week,
                        'second_week' => $user_week_off->second_week,
                        'third_week' => $user_week_off->third_week,
                        'fourth_week' => $user_week_off->fourth_week,
                        'fifth_week' => $user_week_off->fifth_week,
                    ]);
                }
            } else {
                $week_off_change->location_id = $this->user?->location_id;
                $week_off_change->company_id = $this->user?->company_id;
                $week_off_change->save();

                $week_off_change->WeekOffChangeDetails()->delete();
                foreach ($this->user->user_week_offs as $user_week_off) {
                    $week_off_change->WeekOffChangeDetails()->create([
                        'week_off_change_id' => $week_off_change->id,
                        'week_days' => $user_week_off->week_days,
                        'wo_type' => $user_week_off->wo_type,
                        'first_week' => $user_week_off->first_week,
                        'second_week' => $user_week_off->second_week,
                        'third_week' => $user_week_off->third_week,
                        'fourth_week' => $user_week_off->fourth_week,
                        'fifth_week' => $user_week_off->fifth_week,
                    ]);
                }
            }
        }

        $week_off_change->Users()->sync($this->user->id);
        $week_off_change->departments()->sync($this->user?->department_id);
        $week_off_change->sub_departments()->sync($this->user?->sub_department_id);
        $week_off_change->categories()->sync($this->user?->category_id);
        $week_off_change->sub_categories()->sync($this->user?->sub_category_id);
        $week_off_change->designations()->sync($this->user?->designation_id);
        $week_off_change->areas()->sync($this->user?->areas()->pluck('area_id')->toArray());

        $this->user->week_off_change_id = $week_off_change->id;
        $this->user->save();

        CreateWeekOffMuster::dispatch($week_off_change)->onQueue('processing');
    }
}
