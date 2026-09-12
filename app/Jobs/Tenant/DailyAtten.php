<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\WeekOffMuster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class DailyAtten implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;

    protected $date;

    protected $authUser;

    /**
     * Create a new job instance.
     */
    public function __construct($users, $date, $authUser)
    {
        $this->users = $users;
        $this->date = $date;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->users as $user) {
            $status_muster = StatusMuster::where('user_id', $user->id)
                ->where('is_locked', false)
                ->where('date', $this->date->format('Y-m-d'))
                ->first();

            $week_off = WeekOffMuster::where('user_id', $user->id)
                ->where('is_locked', false)
                ->whereIn('wo_type', [1, 2, 3])
                ->where('date', $this->date->format('Y-m-d'))
                ->first();
            $holiday = Holiday::where('date', $this->date->format('Y-m-d'))
                ->first();
            $curr_att = Attendance::year($this->date->format('Y'))
                ->where('user_id', $user->id)
                ->where('is_locked', false)
                ->where('date', $this->date->format('Y-m-d'))
                ->first();
            if (! $curr_att) {
                if ($week_off !== null && $holiday !== null) {
                    $att = Attendance::year($this->date->format('Y'))->create([
                        'user_id' => $user->id,
                        'date' => $this->date->format('Y-m-d'),
                        // 'location_id' => $user->location_id ? $user->location_id : null,
                        // 'company_id' => $user->company_id ? $user->company_id : null,
                        // 'department_id' => $user->department_id ? $user->department_id : null,
                        // 'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        // 'category_id' => $user->category_id ? $user->category_id : null,
                        // 'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                    ]);

                    $att->is_holiday = true;
                    $att->status_master_id = StatusMaster::where('code', 'HL')->first()->id;
                    $att->save();

                    $status_muster->status_master_id = StatusMaster::where('code', 'HL')->first()->id;
                    $status_muster->day_count = $user->category->is_holiday_paid ? 1 : 0;
                    $status_muster->save();
                } elseif ($week_off !== null) {
                    $att = Attendance::year($this->date->format('Y'))->create([
                        'user_id' => $user->id,
                        'date' => $this->date->format('Y-m-d'),
                        // 'location_id' => $user->location_id ? $user->location_id : null,
                        // 'company_id' => $user->company_id ? $user->company_id : null,
                        // 'department_id' => $user->department_id ? $user->department_id : null,
                        // 'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        // 'category_id' => $user->category_id ? $user->category_id : null,
                        // 'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                    ]);

                    $att->is_weekoff = true;
                    $att->status_master_id = StatusMaster::where('code', 'WO')->first()->id;
                    $att->save();

                    $status_muster->status_master_id = StatusMaster::where('code', 'WO')->first()->id;
                    $status_muster->day_count = $user->category->is_week_off_paid ? 1 : 0;
                    $status_muster->save();
                } elseif ($holiday !== null) {
                    $att = Attendance::year($this->date->format('Y'))->create([
                        'user_id' => $user->id,
                        'date' => $this->date->format('Y-m-d'),
                        // 'location_id' => $user->location_id ? $user->location_id : null,
                        // 'company_id' => $user->company_id ? $user->company_id : null,
                        // 'department_id' => $user->department_id ? $user->department_id : null,
                        // 'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        // 'category_id' => $user->category_id ? $user->category_id : null,
                        // 'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                    ]);

                    $att->is_holiday = true;
                    $att->status_master_id = StatusMaster::where('code', 'HL')->first()->id;
                    $att->save();

                    $status_muster->status_master_id = StatusMaster::where('code', 'HL')->first()->id;
                    $status_muster->day_count = $user->category->is_holiday_paid ? 1 : 0;
                    $status_muster->save();
                } else {
                    $att = Attendance::year($this->date->format('Y'))->create([
                        'user_id' => $user->id,
                        'date' => $this->date->format('Y-m-d'),
                        // 'location_id' => $user->location_id ? $user->location_id : null,
                        // 'company_id' => $user->company_id ? $user->company_id : null,
                        // 'department_id' => $user->department_id ? $user->department_id : null,
                        // 'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                        // 'category_id' => $user->category_id ? $user->category_id : null,
                        // 'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                    ]);

                    $att->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                    $att->save();

                    $status_muster->status_master_id = StatusMaster::where('code', 'AA')->first()->id;
                    $status_muster->day_count = 0;
                    $status_muster->save();
                }

                $old_record = [];
                $agent = new Agent;
                $browser = $agent->browser().' '.$agent->version($agent->browser());
                $os = $agent->platform();

                $new_record = [
                    'ip' => request()->ip(),
                    'browser' => $os.' > '.$browser,
                    'user' => $user->name.'('.$user->code.')',
                    'date' => $att->date->format('Y-m-d'),
                    'status' => StatusMaster::where('id', $att->status_master_id)->first()->code,
                ];

                ActivityLog::dispatch($this->authUser, $att, $old_record, $new_record, 'created')->onQueue('processing');
            }
        }
    }
}
