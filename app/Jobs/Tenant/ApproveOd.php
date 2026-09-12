<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\OutDuty;
use App\Models\Tenant\StatusMaster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApproveOd implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $odApplication;

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
        return $this->odApplication->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(OutDuty $odApplication)
    {
        $this->odApplication = $odApplication;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->odApplication->user;
        $fromDateTime = Carbon::parse($this->odApplication->from_date_time);
        $toDateTime = Carbon::parse($this->odApplication->to_date_time);
        $period = Carbon::parse($fromDateTime)->daysUntil($toDateTime);

        // All dates get CO status
        foreach ($period as $date) {
            Attendance::year($date->format('Y'))
                ->updateOrCreate([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                ], [
                    'location_id' => $this->odApplication->location_id ? $this->odApplication->location_id : null,
                    'company_id' => $this->odApplication->company_id ? $this->odApplication->company_id : null,
                    'department_id' => $user->department_id ? $user->department_id : null,
                    'sub_department_id' => $user->sub_department_id ? $user->sub_department_id : null,
                    'category_id' => $user->category_id ? $user->category_id : null,
                    'sub_category_id' => $user->sub_category_id ? $user->sub_category_id : null,
                    'status_master_id' => StatusMaster::where('code', 'OD')->first()->id,
                    'remarks' => $this->odApplication->reason ? $this->odApplication->reason : null,
                ]);
        }
    }
}
