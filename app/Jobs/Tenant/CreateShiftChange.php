<?php

namespace App\Jobs\Tenant;

use App\Support\ActivityLogger;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class CreateShiftChange implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    protected $is_created = false;

    protected $auth_user;

    protected $joinDate;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $auth_user, $joinDate)
    {
        $this->user = $user;
        $this->auth_user = $auth_user;
        $this->joinDate = $joinDate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $joinDate = $this->joinDate;
        $financial_year = FinancialYear::where('is_started', true)->orderBy('id', 'desc')->first();
        if ($financial_year) {
            $endDate = $financial_year->end_date;
        } else {
            $endDate = $joinDate?->copy()->endOfYear();
        }
        // For create shift change
        if (GeneralConfiguration::where('key', 'create_new_shift_change_in_user_edit') && GeneralConfiguration::where('key', 'create_new_shift_change_in_user_edit')->first()->value == 1) {
            $shift_change = ShiftChange::create(
                [
                    'from_date' => $joinDate->copy(),
                    'shift_rotation_id' => $this->user?->shift_rotation_id,
                    'to_date' => $endDate->copy(),
                    'shift_type' => $this->user?->shift_type,
                    'is_forever' => true,
                ]
            );
            $this->is_created = true;
        } else {
            $shift_change = ShiftChange::where('id', $this->user?->shift_change_id)->first();
            if (! $shift_change) {
                $shift_change = ShiftChange::create(
                    [
                        'from_date' => $joinDate->copy(),
                        'shift_rotation_id' => $this->user?->shift_rotation_id,
                        'to_date' => $endDate->copy(),
                        'shift_type' => $this->user?->shift_type,
                        'is_forever' => true,
                    ]
                );
                $this->is_created = true;
            } else {
                $shift_change_old_record = [
                    'ip' => '-',
                    'browser' => '-',
                    'from_date' => $shift_change->from_date,
                    'is_forever' => $shift_change->is_forever,
                    'to_date' => $shift_change->to_date,
                    'reason' => $shift_change->reason,
                    'shift_type' => $shift_change->shift_type,
                ];
                if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
                    $shift_change_old_record['shifts'] = $shift_change->shifts()?->pluck('name')->implode(',');
                } else {
                    $shift_change_old_record['shift_rotation'] = $shift_change->shift_rotation?->code;
                }
                if ($shift_change->Users()?->count() > 0) {
                    $shift_change_old_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
                }

                $shift_change->shift_type = $this->user?->shift_type;
                $shift_change->shift_rotation_id = $this->user?->shift_rotation_id;
                $shift_change->save();
                $this->is_created = false;
            }
        }

        if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
            $shift_change->shifts()->sync($this->user->shifts()->pluck('shift_id'));
        }

        $user_old_record = [
            'ip' => '-',
            'browser' => '-',
            'shift_change_id' => $this->user->shift_change_id ? $this->user->shift_change_id : '-',
        ];

        $this->user->shift_change_id = $shift_change->id;
        $this->user->save();

        $agent = new Agent;
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $os = $agent->platform();

        $user_new_record = [
            'ip' => request()->ip(),
            'browser' => $os . ' > ' . $browser,
            'shift_change_id' => $this->user->shift_change_id ? $this->user->shift_change_id : '-',
        ];
        ActivityLogger::log($this->auth_user, $this->user, $user_old_record, $user_new_record, 'updated');

        $shift_change->Users()->sync($this->user->id);

        if ($this->is_created == true) {
            $shift_change_old_record = [];

            $agent = new Agent;
            $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            $os = $agent->platform();

            $shift_change_new_record = [
                'ip' => request()->ip(),
                'browser' => $os . ' > ' . $browser,
                'from_date' => $shift_change->from_date,
                'is_forever' => $shift_change->is_forever,
                'to_date' => $shift_change->to_date,
                'reason' => $shift_change->reason,
                'shift_type' => $shift_change->shift_type,
            ];
            if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
                $shift_change_new_record['shifts'] = $shift_change->shifts()?->pluck('name')->implode(',');
            } else {
                $shift_change_new_record['shift_rotation'] = $shift_change->shift_rotation?->code;
            }
            if ($shift_change->Users()?->count() > 0) {
                $shift_change_new_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
            }
            ActivityLogger::log($this->auth_user, $shift_change, $shift_change_old_record, $shift_change_new_record, 'created');
        } else {
            $agent = new Agent;
            $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            $os = $agent->platform();

            $shift_change_new_record = [
                'ip' => request()->ip(),
                'browser' => $os . ' > ' . $browser,
                'from_date' => $shift_change->from_date,
                'is_forever' => $shift_change->is_forever,
                'to_date' => $shift_change->to_date,
                'reason' => $shift_change->reason,
                'shift_type' => $shift_change->shift_type,
            ];
            if ($shift_change->shift_type == 'auto' || $shift_change->shift_type == 'fixed') {
                $shift_change_new_record['shifts'] = $shift_change->shifts()?->pluck('name')->implode(',');
            } else {
                $shift_change_new_record['shift_rotation'] = $shift_change->shift_rotation?->code;
            }
            if ($shift_change->Users()?->count() > 0) {
                $shift_change_new_record['users'] = $shift_change->Users()?->pluck('code')->implode(',');
            }
            ActivityLogger::log($this->auth_user, $shift_change, $shift_change_old_record, $shift_change_new_record, 'updated');
        }

        CreateShiftMuster::dispatch($shift_change)->onQueue('processing');
    }
}
