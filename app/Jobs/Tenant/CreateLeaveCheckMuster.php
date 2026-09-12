<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateLeaveCheckMuster implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

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
        return $this->user->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            if ($this->user?->last_check_date != null && $this->user?->last_check_id != null) {
                if (LeaveCheckMuster::where('user_id', $this->user?->id)->where('is_checked', false)->orderBy('check_date', 'desc')->first() == null) {
                    $user_leave_check_muster = LeaveCheckMuster::where('user_id', $this->user?->id)
                        ->where('is_checked', true)
                        ->orderBy('check_date', 'desc')
                        ->first();
                    if ($this->user?->grade_wise_leave?->is_monthly) {
                        if ($user_leave_check_muster) {
                            $leave_check_date = GeneralConfiguration::where('key', 'monthly_leave_check_date')?->first();
                            if ($leave_check_date?->value != null) {
                                if (Carbon::today()->format('d') == $leave_check_date?->value && Carbon::today()->format('Y-m-d') != $user_leave_check_muster->check_date) {
                                    LeaveCheckMuster::create([
                                        'user_id' => $this->user?->id,
                                        'check_date' => Carbon::today()->format('Y-m-d'),
                                    ]);
                                    Log::channel('attprocess')->debug('Crete Leave Muster For | '.$this->user?->name.'('.$this->user?->code.')');
                                }
                            } else {
                                $new_check_date = $user_leave_check_muster->check_date->modify('+1 month')->format('Y-m-d');
                                LeaveCheckMuster::create([
                                    'user_id' => $this->user?->id,
                                    'check_date' => $new_check_date,
                                ]);
                                Log::channel('attprocess')->debug('Crete Leave Muster For | '.$this->user?->name.'('.$this->user?->code.')');
                            }
                        }
                    } else {
                        if ($user_leave_check_muster) {
                            $leave_check_date = GeneralConfiguration::where('key', 'yearly_leave_check_date')?->first();
                            if ($leave_check_date?->value !== null) {
                                if (Carbon::today()->format('d') == $leave_check_date?->value && Carbon::today()->format('Y-m-d') != $user_leave_check_muster->check_date) {
                                    LeaveCheckMuster::create([
                                        'user_id' => $this->user?->id,
                                        'check_date' => Carbon::today()->format('Y-m-d'),
                                    ]);
                                    Log::channel('attprocess')->debug('Crete Leave Muster For | '.$this->user?->name.'('.$this->user?->code.')');
                                }
                            } else {
                                $new_check_date = $user_leave_check_muster->check_date->modify('+1 year')->format('Y-m-d');
                                LeaveCheckMuster::create([
                                    'user_id' => $this->user?->id,
                                    'check_date' => $new_check_date,
                                ]);
                                Log::channel('attprocess')->debug('Crete Leave Muster For | '.$this->user?->name.'('.$this->user?->code.')');
                            }
                        }
                    }
                }
            } else {
                if (LeaveCheckMuster::where('user_id', $this->user?->id)->where('is_checked', false)->orderBy('check_date', 'desc')->first() == null) {
                    LeaveCheckMuster::create([
                        'user_id' => $this->user?->id,
                        'check_date' => $this->user?->join_date,
                    ]);
                    Log::channel('attprocess')->debug('Crete Leave Muster For | '.$this->user?->name.'('.$this->user?->code.')');
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Credit Leave Balance. '.Str::limit($e->getMessage(), 200));
        }
    }
}
