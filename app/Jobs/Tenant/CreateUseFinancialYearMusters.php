<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffMuster;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUseFinancialYearMusters implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record;

    protected $authUser;

    /**
     * Create a new job instance.
     */
    public function __construct($record, $authuser)
    {
        $this->record = $record;
        $this->authUser = $authuser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::where('id', '!=', 1)->where('left_date', null)->where('is_inactive', false)->get();
        $joinDate = GeneralConfiguration::where('key', 'first_time_muster_date')->first()->value;
        foreach ($users as $user) {
            CreateShiftChange::dispatch($user, $this->authUser, Carbon::parse($joinDate))->onQueue('processing');

            $currentDate = $this->record->start_date->copy();
            // $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
            if ($this->record) {
                $toDate = $this->record->end_date;
            } else {
                $toDate = $currentDate->copy()->endOfYear();
            }

            while ($currentDate->lte($toDate)) {
                WeekOffMuster::create([
                    'user_id' => $user->id,
                    'date' => $currentDate,
                    'is_locked' => 0,
                    'wo_type' => 0,
                ]);
                $currentDate->addDay();
            }

            CreateWeekOffChange::dispatch($user, Carbon::parse($joinDate))->onQueue('processing');

            UserStatusMuster::dispatch($this->record, Carbon::parse($joinDate))->onQueue('low');
        }
    }
}
