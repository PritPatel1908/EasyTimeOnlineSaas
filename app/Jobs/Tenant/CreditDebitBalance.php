<?php

namespace App\Jobs\Tenant;

use App\Enums\BasedOnEnumn;
use App\Enums\YesNoEnumn;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveAccountDetail;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
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
use Jenssegers\Agent\Agent;

class CreditDebitBalance implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $is_credited = false;

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
            // $userLeaveAccount = $this->user?->leave_account()->create(['user_id' => $this->user?->id]);
            $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $this->user?->id]);
            $user_leave_check_muster = LeaveCheckMuster::where('user_id', $this->user?->id)
                ->where('is_checked', false)
                ->orderBy('check_date', 'desc')
                ->first();
            $year = $user_leave_check_muster?->check_date?->format('Y');

            if ($user_leave_check_muster !== null) {
                if ($this->user?->grade_wise_leave?->is_monthly) {
                    if ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details != null && count($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details) > 0) {
                        foreach ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details as $grade_wise_detail) {
                            $joinDate = Carbon::parse($this->user?->join_date);
                            $currentDate = Carbon::now();
                            $dayDiff = $joinDate->diffInDays($currentDate);
                            if ($dayDiff > $grade_wise_detail->credit_after_days || $grade_wise_detail->credit_after_days == 0) {
                                if ($grade_wise_detail->proportionate == YesNoEnumn::Yes->value) {
                                    $eligible = $grade_wise_detail->increment;
                                    $previousMonth = Carbon::today()->subMonth();
                                    $totalDaysInPreviousMonth = $previousMonth->daysInMonth;

                                    $statuses = [];
                                    if ($grade_wise_detail->count_weekly_off_as_present) {
                                        $statuses[] = StatusMaster::where('code', 'WO')->first()->id;
                                    }
                                    if ($grade_wise_detail->count_holiday_as_present) {
                                        $statuses[] = StatusMaster::where('code', 'HL')->first()->id;
                                    }

                                    $day_count = StatusMuster::where('user_id', $this->user?->id)
                                        ->where('status_master_id', StatusMaster::where('code', 'PP')->first()->id)
                                        ->orWhereIn('status_master_id', $statuses)
                                        ->whereBetween('date', [$previousMonth->startOfMonth()->format('Y-m-d'), $previousMonth->endOfMonth()->format('Y-m-d')])
                                        ->count();

                                    $total_leave_balance = ($eligible) / ($totalDaysInPreviousMonth) * ($day_count);
                                    // $total_leave_balance = ($eligible) / ($totalDaysInPreviousMonth) * (20);
                                } else {
                                    $eligible = $grade_wise_detail->increment;
                                    $total_leave_balance = $eligible;
                                }

                                GeneralHelper::createOrUpdateTransaction(
                                    $year ? $year : $this->user?->join_date?->format('Y'),
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    $grade_wise_detail->leave_type_id,
                                    get_class($this->user),
                                    $this->user?->id,
                                    $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
                                    $this->user?->id,
                                    $total_leave_balance,
                                    0,
                                    'Credit #'.$grade_wise_detail->leave_type->code.' '.$user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
                                    $this->user?->id,
                                    $this->user?->id,
                                    false
                                );

                                if (LeaveAccountDetail::where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()->balance > $grade_wise_detail->max_accumulation) {
                                    GeneralHelper::createOrUpdateTransaction(
                                        $year ? $year : $this->user?->join_date?->format('Y'),
                                        get_class($userLeaveAccount),
                                        $userLeaveAccount->id,
                                        get_class($grade_wise_detail),
                                        $grade_wise_detail->id,
                                        $grade_wise_detail->leave_type_id,
                                        get_class($this->user),
                                        $this->user?->id,
                                        $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 03, 000000),
                                        $this->user?->id,
                                        0,
                                        LeaveAccountDetail::where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()->balance - $grade_wise_detail->max_accumulation,
                                        'Debit #'.$grade_wise_detail->leave_type->code.' '.$user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 03, 000000),
                                        $this->user?->id,
                                        $this->user?->id,
                                        false
                                    );
                                }
                            }
                        }
                    }
                } else {
                    if ($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details != null && count($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details) > 0) {
                        foreach ($this->user?->grade_wise_leave?->grade_wise_yearly_leave_details as $grade_wise_detail) {
                            $joinDate = Carbon::parse($this->user?->join_date);
                            $currentDate = Carbon::now();
                            $dayDiff = $joinDate->diffInDays($currentDate);
                            if ($dayDiff > $grade_wise_detail->credit_after_days || $grade_wise_detail->credit_after_days == 0) {
                                if ($grade_wise_detail->based_on == BasedOnEnumn::Day->value) {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_days = Carbon::parse($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date)->isLeapYear() ? 366 : 365;
                                    $remaining = round(Carbon::parse($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date)->diffInDays($this->user?->grade_wise_leave?->financial_year?->end_date->endOfYear()));
                                    $total_leave_balance = ($eligible) / ($total_days) * ($remaining);
                                } elseif ($grade_wise_detail->based_on == BasedOnEnumn::Fortnightly->value) {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_months = 12;
                                    $remaining_month = round(Carbon::parse($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date)->diffInMonths($this->user?->grade_wise_leave?->financial_year?->end_date->endOfYear()));
                                    $total_leave_balance = ($eligible) / ($total_months) * ($remaining_month);

                                    if ($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date->format('d') : $this->user?->join_date->format('d') <= 15) {
                                        $total_leave_balance = $total_leave_balance;
                                    } else {
                                        $total_leave_balance = $total_leave_balance - 1;
                                    }
                                } elseif ($grade_wise_detail->based_on == BasedOnEnumn::Monthly->value) {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_months = 12;
                                    $remaining_month = round(Carbon::parse($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date)->diffInMonths($this->user?->grade_wise_leave?->financial_year?->end_date->endOfYear()));
                                    $total_leave_balance = ($eligible) / ($total_months) * ($remaining_month);
                                } else {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_days = Carbon::parse($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date)->isLeapYear() ? 366 : 365;
                                    $remaining = round(Carbon::parse($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date : $this->user?->join_date)->diffInDays($this->user?->grade_wise_leave?->financial_year?->end_date->endOfYear()));
                                    $total_leave_balance = ($eligible) / ($total_days) * ($remaining);
                                }

                                GeneralHelper::createOrUpdateTransaction(
                                    $year ? $year : $this->user?->join_date?->format('Y'),
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    $grade_wise_detail->leave_type_id,
                                    get_class($this->user),
                                    $this->user?->id,
                                    $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
                                    $this->user?->id,
                                    $total_leave_balance,
                                    0,
                                    'Credit #'.$grade_wise_detail->leave_type->code.' '.$user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
                                    $this->user?->id,
                                    $this->user?->id,
                                    false
                                );

                                if (LeaveAccountDetail::where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()->balance > $grade_wise_detail->max_accumulation) {
                                    GeneralHelper::createOrUpdateTransaction(
                                        $year ? $year : $this->user?->join_date?->format('Y'),
                                        get_class($userLeaveAccount),
                                        $userLeaveAccount->id,
                                        get_class($grade_wise_detail),
                                        $grade_wise_detail->id,
                                        $grade_wise_detail->leave_type_id,
                                        get_class($this->user),
                                        $this->user?->id,
                                        $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 03, 000000),
                                        $this->user?->id,
                                        0,
                                        LeaveAccountDetail::where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()->balance - $grade_wise_detail->max_accumulation,
                                        'Debit #'.$grade_wise_detail->leave_type->code.' '.$user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 03, 000000),
                                        $this->user?->id,
                                        $this->user?->id,
                                        false
                                    );
                                }
                            }
                        }
                    }
                }

                $user_leave_check_muster->is_checked = true;
                $user_leave_check_muster->save();

                $this->user->last_check_date = $user_leave_check_muster->check_date;
                $this->user->last_check_id = $user_leave_check_muster->id;
                $this->user->save();

                $old_record = [];
                $agent = new Agent;
                $browser = $agent->browser().' '.$agent->version($agent->browser());
                $os = $agent->platform();

                $new_record = [
                    'ip' => request()->ip(),
                    'browser' => $os.' > '.$browser,
                    'user' => $userLeaveAccount->name.'('.$userLeaveAccount->code.')',
                    'date' => $user_leave_check_muster?->check_date?->copy()->format('Y-m-d'),
                ];

                $authUser = User::where('id', '1')->first();
                ActivityLog::dispatch($authUser, $userLeaveAccount, $old_record, $new_record, 'updated')->onQueue('processing');
            } else {
                Log::channel('attprocess')->debug('Not have any leave check muster record for | '.$this->user?->name.'('.$this->user?->code.')');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Credit Leave Balance. '.Str::limit($e->getMessage(), 200));
        }
    }
}
