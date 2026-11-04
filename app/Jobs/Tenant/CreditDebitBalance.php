<?php

namespace App\Jobs\Tenant;

use App\Support\ActivityLogger;
use App\Enums\BasedOnEnumn;
use App\Enums\YesNoEnumn;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveAccountDetail;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\User;
use App\Support\YearlyLeaveCredit;
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
use Throwable;
use Jenssegers\Agent\Agent;

class CreditDebitBalance implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    protected ?string $tenantId = null;

    protected $is_credited = false;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 7200;

    public $timeout = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return ($this->tenantId ?? tenant()?->getTenantKey() ?? 'central').':'.$this->user->id;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->tenantId = tenant()?->getTenantKey();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->processUser($this->user);
    }

    private function processUser(User $user): void
    {
        $this->user = $user;

        DB::beginTransaction();
        try {
            // $userLeaveAccount = $this->user?->leave_account()->create(['user_id' => $this->user?->id]);
            $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $this->user?->id]);
            $user_leave_check_muster = LeaveCheckMuster::where('user_id', $this->user?->id)
                ->where('is_checked', false)
                ->orderBy('check_date', 'desc')
                ->first();

            if ($user_leave_check_muster?->check_date?->isAfter(Carbon::today())) {
                Log::channel('attprocess')->debug(
                    'Leave balance check is not due yet for | '.$this->user->name.'('.$this->user->code.') on '
                    .$user_leave_check_muster->check_date->format('Y-m-d')
                );
                DB::commit();

                return;
            }

            $year = $user_leave_check_muster?->check_date?->format('Y');

            if ($user_leave_check_muster !== null) {
                if ($this->user?->grade_wise_leave?->is_monthly) {
                    if ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details != null && count($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details) > 0) {
                        $checkDate = Carbon::parse($user_leave_check_muster->check_date);
                        $financialYear = FinancialYear::forDate($checkDate);
                        $yearlyCheckDate = GeneralConfiguration::where('key', 'yearly_leave_check_date')->value('value');
                        $isYearlyCreditDate = YearlyLeaveCredit::isCreditDate($checkDate, $financialYear, $yearlyCheckDate);

                        foreach ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details as $grade_wise_detail) {
                            $leaveCreditType = $grade_wise_detail->leave_credit_type ?? 'monthly';

                            if ($leaveCreditType === 'manual') {
                                continue;
                            }

                            if ($leaveCreditType === 'yearly') {
                                if (! $isYearlyCreditDate || $financialYear === null) {
                                    continue;
                                }

                                if ($grade_wise_detail->new_joiner_based_on_credit !== null
                                    && $grade_wise_detail->new_joiner_based_on_credit !== 'day_based') {
                                    Log::channel('attprocess')->warning(
                                        'Unsupported yearly leave credit mode '.$grade_wise_detail->new_joiner_based_on_credit
                                        .' for user '.$this->user?->id.' and leave rule '.$grade_wise_detail->id
                                    );
                                    continue;
                                }

                                $joinDate = $this->user?->join_date
                                    ? Carbon::parse($this->user->join_date)->startOfDay()
                                    : null;
                                $annualCredit = YearlyLeaveCredit::calculateCredit(
                                    (float) ($grade_wise_detail->annual_quota ?? 0),
                                    $grade_wise_detail->new_joiner_based_on_credit,
                                    $joinDate,
                                    $checkDate->copy()->startOfDay(),
                                    $financialYear->start_date->copy()->startOfDay(),
                                    $financialYear->end_date->copy()->startOfDay(),
                                    (int) ($grade_wise_detail->credit_after_days ?? 0),
                                );

                                if ($annualCredit === null) {
                                    continue;
                                }

                                $transactionDate = $checkDate->copy()->setTime(0, 0, 4);
                                GeneralHelper::createOrUpdateTransaction(
                                    $checkDate->format('Y'),
                                    get_class($userLeaveAccount),
                                    $userLeaveAccount->id,
                                    get_class($grade_wise_detail),
                                    $grade_wise_detail->id,
                                    $grade_wise_detail->leave_type_id,
                                    get_class($this->user),
                                    $this->user->id,
                                    $transactionDate,
                                    $this->user->id,
                                    $annualCredit,
                                    0,
                                    'Annual Credit #'.$grade_wise_detail->leave_type?->code.' '.$transactionDate,
                                    $this->user->id,
                                    $this->user->id,
                                    false
                                );

                                if ($grade_wise_detail->max_accumulation > 0) {
                                    $leaveAccountDetail = LeaveAccountDetail::where('leave_account_id', $userLeaveAccount->id)
                                        ->where('leave_type_id', $grade_wise_detail->leave_type_id)
                                        ->first();

                                    if ($leaveAccountDetail !== null && $leaveAccountDetail->balance > $grade_wise_detail->max_accumulation) {
                                        $debitDate = $transactionDate->copy()->setTime(0, 0, 5);
                                        GeneralHelper::createOrUpdateTransaction(
                                            $checkDate->format('Y'),
                                            get_class($userLeaveAccount),
                                            $userLeaveAccount->id,
                                            get_class($grade_wise_detail),
                                            $grade_wise_detail->id,
                                            $grade_wise_detail->leave_type_id,
                                            get_class($this->user),
                                            $this->user->id,
                                            $debitDate,
                                            $this->user->id,
                                            0,
                                            $leaveAccountDetail->balance - $grade_wise_detail->max_accumulation,
                                            'Debit #'.$grade_wise_detail->leave_type?->code.' '.$debitDate,
                                            $this->user->id,
                                            $this->user->id,
                                            false
                                        );
                                    }
                                }

                                continue;
                            }

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
                                    'Credit #' . $grade_wise_detail->leave_type->code . ' ' . $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
                                    $this->user?->id,
                                    $this->user?->id,
                                    false
                                );

                                if ($grade_wise_detail->max_accumulation > 0 && LeaveAccountDetail::where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()->balance > $grade_wise_detail->max_accumulation) {
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
                                        'Debit #' . $grade_wise_detail->leave_type->code . ' ' . $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 03, 000000),
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
                            $checkDate = Carbon::parse(
                                $user_leave_check_muster->check_date ?? $this->user?->join_date
                            );
                            $financialYear = FinancialYear::forDate($checkDate);
                            if ($financialYear === null) {
                                Log::channel('attprocess')->warning(
                                    'No financial year covers leave check date '.$checkDate->format('Y-m-d')
                                    .' for user '.$this->user->id
                                );
                                continue;
                            }

                            $joinDate = Carbon::parse($this->user?->join_date);
                            $currentDate = Carbon::now();
                            $dayDiff = $joinDate->diffInDays($currentDate);
                            if ($dayDiff > $grade_wise_detail->credit_after_days || $grade_wise_detail->credit_after_days == 0) {
                                if ($grade_wise_detail->based_on == BasedOnEnumn::Day->value) {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_days = $financialYear->start_date->diffInDays($financialYear->end_date) + 1;
                                    $remaining = round($checkDate->diffInDays($financialYear->end_date));
                                    $total_leave_balance = ($eligible) / ($total_days) * ($remaining);
                                } elseif ($grade_wise_detail->based_on == BasedOnEnumn::Fortnightly->value) {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_months = 12;
                                    $remaining_month = round($checkDate->diffInMonths($financialYear->end_date));
                                    $total_leave_balance = ($eligible) / ($total_months) * ($remaining_month);

                                    if ($user_leave_check_muster->check_date ? $user_leave_check_muster?->check_date->format('d') : $this->user?->join_date->format('d') <= 15) {
                                        $total_leave_balance = $total_leave_balance;
                                    } else {
                                        $total_leave_balance = $total_leave_balance - 1;
                                    }
                                } elseif ($grade_wise_detail->based_on == BasedOnEnumn::Monthly->value) {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_months = 12;
                                    $remaining_month = round($checkDate->diffInMonths($financialYear->end_date));
                                    $total_leave_balance = ($eligible) / ($total_months) * ($remaining_month);
                                } else {
                                    $eligible = $grade_wise_detail->eligible;
                                    $total_days = $financialYear->start_date->diffInDays($financialYear->end_date) + 1;
                                    $remaining = round($checkDate->diffInDays($financialYear->end_date));
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
                                    'Credit #' . $grade_wise_detail->leave_type->code . ' ' . $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
                                    $this->user?->id,
                                    $this->user?->id,
                                    false
                                );

                                if ($grade_wise_detail->max_accumulation > 0 && LeaveAccountDetail::where('leave_type_id', $grade_wise_detail->leave_type_id)?->first()->balance > $grade_wise_detail->max_accumulation) {
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
                                        'Debit #' . $grade_wise_detail->leave_type->code . ' ' . $user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 03, 000000),
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

                if ($this->user->last_check_date === null
                    || $user_leave_check_muster->check_date->gte($this->user->last_check_date)) {
                    $this->user->last_check_date = $user_leave_check_muster->check_date;
                    $this->user->last_check_id = $user_leave_check_muster->id;
                    $this->user->save();
                }

                $old_record = [];
                $agent = new Agent;
                $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
                $os = $agent->platform();

                $new_record = [
                    'ip' => request()->ip(),
                    'browser' => $os . ' > ' . $browser,
                    'user' => $userLeaveAccount->name . '(' . $userLeaveAccount->code . ')',
                    'date' => $user_leave_check_muster?->check_date?->copy()->format('Y-m-d'),
                ];

                $authUser = User::where('id', '1')->first();
                ActivityLogger::log($authUser, $userLeaveAccount, $old_record, $new_record, 'updated');
            } else {
                Log::channel('attprocess')->debug('Not have any leave check muster record for | ' . $this->user?->name . '(' . $this->user?->code . ')');
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::channel('attprocess')->error('Error crediting leave balance for user '.$this->user->id.'. '.Str::limit($e->getMessage(), 200));
            throw $e;
        }
    }
}
