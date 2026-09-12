<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\Coff;
use App\Models\Tenant\StatusMaster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApproveCoff implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coffApplication;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->coffApplication->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(Coff $coffApplication)
    {
        $this->coffApplication = $coffApplication;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->coffApplication->user;
        $fromDate = Carbon::parse($this->coffApplication->from_date);
        $toDate = Carbon::parse($this->coffApplication->to_date);
        $period = Carbon::parse($fromDate)->daysUntil($toDate);
        $againsts = Carbon::parse($this->coffApplication->coff_against_date)->format('d-m-Y');

        if ($this->coffApplication->only_a_half_day) {
            if ($this->coffApplication->is_this_second_half) {
                Attendance::year($fromDate->format('Y'))
                    ->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $fromDate->format('Y-m-d'),
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                        'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                        'department_id' => $user->department_id ? $user->department_id : null,
                        'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        'category_id' => $user->category_id ? $user->category_id : null,
                        'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                        'status_master_id' => StatusMaster::where('code', 'ACO')->first()->id,
                        'remarks' => "CO Against {$againsts}",
                    ]);
            } else {
                Attendance::year($fromDate->format('Y'))
                    ->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $fromDate->format('Y-m-d'),
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                        'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                        'department_id' => $user->department_id ? $user->department_id : null,
                        'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        'category_id' => $user->category_id ? $user->category_id : null,
                        'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                        'status_master_id' => StatusMaster::where('code', 'COA')->first()->id,
                        'remarks' => "CO Against {$againsts}",
                    ]);
            }
        } else {
            if ($this->coffApplication->is_second_half && $this->coffApplication->is_first_half) {
                // First date gets ACO status
                Attendance::year($fromDate->format('Y'))
                    ->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $fromDate->format('Y-m-d'),
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                        'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                        'department_id' => $user->department_id ? $user->department_id : null,
                        'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        'category_id' => $user->category_id ? $user->category_id : null,
                        'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                        'status_master_id' => StatusMaster::where('code', 'ACO')->first()->id,
                        'remarks' => "CO Against {$againsts}",
                    ]);

                // Last date gets COA status
                Attendance::year($toDate->format('Y'))
                    ->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $toDate->format('Y-m-d'),
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                        'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                        'department_id' => $user->department_id ? $user->department_id : null,
                        'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        'category_id' => $user->category_id ? $user->category_id : null,
                        'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                        'status_master_id' => StatusMaster::where('code', 'COA')->first()->id,
                        'remarks' => "CO Against {$againsts}",
                    ]);

                // For dates in between, apply CO status
                if (! $fromDate->isSameDay($toDate)) {
                    $middleDates = Carbon::parse($fromDate)->addDay()->daysUntil($toDate);
                    foreach ($middleDates as $date) {
                        Attendance::year($date->format('Y'))
                            ->updateOrCreate([
                                'user_id' => $user->id,
                                'date' => $date->format('Y-m-d'),
                                'is_locked' => false,
                            ], [
                                'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                                'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                                'department_id' => $user->department_id ? $user->department_id : null,
                                'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                                'category_id' => $user->category_id ? $user->category_id : null,
                                'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                                'status_master_id' => StatusMaster::where('code', 'CO')->first()->id,
                                'remarks' => "CO Against {$againsts}",
                            ]);
                    }
                }
            } elseif ($this->coffApplication->is_second_half) {
                // First date gets ACO status
                Attendance::year($fromDate->format('Y'))
                    ->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $fromDate->format('Y-m-d'),
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                        'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                        'department_id' => $user->department_id ? $user->department_id : null,
                        'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        'category_id' => $user->category_id ? $user->category_id : null,
                        'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                        'status_master_id' => StatusMaster::where('code', 'ACO')->first()->id,
                        'remarks' => "CO Against {$againsts}",
                    ]);

                // All other dates get CO status
                if (! $fromDate->isSameDay($toDate)) {
                    $otherDates = Carbon::parse($fromDate)->addDay()->daysUntil($toDate->addDay());
                    foreach ($otherDates as $date) {
                        Attendance::year($date->format('Y'))
                            ->updateOrCreate([
                                'user_id' => $user->id,
                                'date' => $date->format('Y-m-d'),
                                'is_locked' => false,
                            ], [
                                'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                                'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                                'department_id' => $user->department_id ? $user->department_id : null,
                                'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                                'category_id' => $user->category_id ? $user->category_id : null,
                                'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                                'status_master_id' => StatusMaster::where('code', 'CO')->first()->id,
                                'remarks' => "CO Against {$againsts}",
                            ]);
                    }
                }
            } elseif ($this->coffApplication->is_first_half) {
                // Last date gets COA status
                Attendance::year($toDate->format('Y'))
                    ->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $toDate->format('Y-m-d'),
                        'is_locked' => false,
                    ], [
                        'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                        'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                        'department_id' => $user->department_id ? $user->department_id : null,
                        'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        'category_id' => $user->category_id ? $user->category_id : null,
                        'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                        'status_master_id' => StatusMaster::where('code', 'COA')->first()->id,
                        'remarks' => "CO Against {$againsts}",
                    ]);

                // All other dates get CO status
                if (! $fromDate->isSameDay($toDate)) {
                    $otherDates = Carbon::parse($fromDate)->daysUntil($toDate);
                    foreach ($otherDates as $date) {
                        if (! $date->isSameDay($toDate)) {
                            Attendance::year($date->format('Y'))
                                ->updateOrCreate([
                                    'user_id' => $user->id,
                                    'date' => $date->format('Y-m-d'),
                                    'is_locked' => false,
                                ], [
                                    'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                                    'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                                    'department_id' => $user->department_id ? $user->department_id : null,
                                    'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                                    'category_id' => $user->category_id ? $user->category_id : null,
                                    'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                                    'status_master_id' => StatusMaster::where('code', 'CO')->first()->id,
                                    'remarks' => "CO Against {$againsts}",
                                ]);
                        }
                    }
                }
            } else {
                // All dates get CO status
                foreach ($period as $date) {
                    Attendance::year($date->format('Y'))
                        ->updateOrCreate([
                            'user_id' => $user->id,
                            'date' => $date->format('Y-m-d'),
                            'is_locked' => false,
                        ], [
                            'location_id' => $this->coffApplication->location_id ? $this->coffApplication->location_id : null,
                            'company_id' => $this->coffApplication->company_id ? $this->coffApplication->company_id : null,
                            'department_id' => $user->department_id ? $user->department_id : null,
                            'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                            'category_id' => $user->category_id ? $user->category_id : null,
                            'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                            'status_master_id' => StatusMaster::where('code', 'CO')->first()->id,
                            'remarks' => "CO Against {$againsts}",
                        ]);
                }
            }
        }
    }
}
