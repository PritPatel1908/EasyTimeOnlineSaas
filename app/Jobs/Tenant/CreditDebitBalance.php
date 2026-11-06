<?php

namespace App\Jobs\Tenant;

use App\Enums\BasedOnEnumn;
use App\Helpers\GeneralHelper;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveAccountDetail;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\User;
use App\Support\ActivityLogger;
use App\Support\LeaveRuleCadence;
use App\Support\PresentBasedLeaveCredit;
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
use Jenssegers\Agent\Agent;
use Throwable;

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
            $today = Carbon::today();
            $pendingMusters = LeaveCheckMuster::where('user_id', $this->user?->id)
                ->where('is_checked', false)
                ->whereDate('check_date', '<=', $today->toDateString());
            $user_leave_check_muster = $pendingMusters
                ->orderBy('check_date')
                ->orderBy('id')
                ->first();

            $year = $user_leave_check_muster?->check_date?->format('Y');

            if ($user_leave_check_muster !== null) {
                Log::channel('attprocess')->info(
                    'Processing leave balance muster for | '.$this->user->name.'('.$this->user->code.') on '
                    .$user_leave_check_muster->check_date->format('Y-m-d')
                );

                if ($this->user?->grade_wise_leave?->is_monthly) {
                    if ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details != null && count($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details) > 0) {
                        $checkDate = Carbon::parse($user_leave_check_muster->check_date);
                        $financialYear = FinancialYear::forDate($checkDate);

                        foreach ($this->user?->grade_wise_leave?->grade_wise_monthly_leave_details as $grade_wise_detail) {
                            if ($financialYear === null || ! $financialYear->is_started) {
                                Log::channel('attprocess')->warning(
                                    'Leave credit skipped: financial year is not started for user '
                                    .$this->user->id.' on '.$checkDate->format('Y-m-d')
                                );

                                break;
                            }

                            $leaveCreditType = $grade_wise_detail->leave_credit_type ?? 'monthly';

                            if ($leaveCreditType === 'manual') {
                                continue;
                            }

                            if ($leaveCreditType === 'yearly') {
                                continue;
                            }

                            $joinDate = Carbon::parse($this->user?->join_date);
                            $dayDiff = $joinDate->diffInDays($checkDate);
                            if ($joinDate->lte($checkDate)
                                && ($dayDiff > $grade_wise_detail->credit_after_days || $grade_wise_detail->credit_after_days == 0)) {
                                $monthField = strtolower($checkDate->format('M'));
                                $monthlyCredit = (float) ($grade_wise_detail->{$monthField} ?? $grade_wise_detail->increment ?? 0);
                                $total_leave_balance = $monthlyCredit;

                                if ($grade_wise_detail->new_joiner_based_on_credit === 'present_base') {
                                    [$periodStart, $periodEnd] = PresentBasedLeaveCredit::getAttendancePeriod(
                                        $grade_wise_detail->present_count_per ?? 'monthly',
                                        $checkDate,
                                        $financialYear->start_date,
                                    );
                                    $presentDays = $this->countPresentDaysForMonthlyLeaveCredit(
                                        $grade_wise_detail,
                                        $periodStart,
                                        $periodEnd,
                                    );
                                    $total_leave_balance = PresentBasedLeaveCredit::calculate(
                                        $monthlyCredit,
                                        $presentDays,
                                        $periodStart,
                                        $periodEnd,
                                        $grade_wise_detail->if_total_present_count,
                                        $grade_wise_detail->then_credit_balance_days === null
                                            ? null
                                            : (float) $grade_wise_detail->then_credit_balance_days,
                                    );
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
                            $checkDate = Carbon::parse(
                                $user_leave_check_muster->check_date ?? $this->user?->join_date
                            );
                            $financialYear = FinancialYear::forDate($checkDate);
                            if ($financialYear === null || ! $financialYear->is_started) {
                                Log::channel('attprocess')->warning(
                                    'Leave credit skipped: no started financial year covers leave check date '
                                    .$checkDate->format('Y-m-d')
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
                                    'Credit #'.$grade_wise_detail->leave_type->code.' '.$user_leave_check_muster?->check_date?->copy()->setTime(00, 00, 02, 000000),
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

                if ($this->user->last_check_date === null
                    || $user_leave_check_muster->check_date->gte($this->user->last_check_date)) {
                    $this->user->last_check_date = $user_leave_check_muster->check_date;
                    $this->user->last_check_id = $user_leave_check_muster->id;
                    $this->user->save();
                }

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
                ActivityLogger::log($authUser, $userLeaveAccount, $old_record, $new_record, 'updated');
            } else {
                $nextMuster = LeaveCheckMuster::where('user_id', $this->user->id)
                    ->where('is_checked', false)
                    ->orderBy('check_date')
                    ->first();

                if ($nextMuster !== null) {
                    Log::channel('attprocess')->debug(
                        'No due leave muster for | '.$this->user->name.'('.$this->user->code
                        .'); next pending muster is '.$nextMuster->check_date?->format('Y-m-d')
                    );
                } else {
                    Log::channel('attprocess')->debug(
                        'No pending leave muster for | '.$this->user->name.'('.$this->user->code.')'
                    );
                }
            }

            $this->processDueYearlyLeaveCredits($userLeaveAccount, $today);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::channel('attprocess')->error(
                'Error crediting leave balance for user '.$this->user->id.'. '.Str::limit($e->getMessage(), 200),
                ['exception' => $e],
            );
            throw $e;
        }
    }

    private function processDueYearlyLeaveCredits(LeaveAccount $userLeaveAccount, Carbon $today): void
    {
        $gradeWiseLeave = $this->user->grade_wise_leave;
        if ($gradeWiseLeave === null || ! $gradeWiseLeave->is_monthly) {
            return;
        }

        $monthlyRules = $gradeWiseLeave->grade_wise_monthly_leave_details;
        if (! LeaveRuleCadence::hasYearlyRules($monthlyRules)) {
            return;
        }

        $yearlyCheckDate = YearlyLeaveCredit::getScheduledCreditDate(
            $today,
            FinancialYear::forDate($today),
            GeneralConfiguration::where('key', 'yearly_leave_check_date')->value('value'),
            $this->user->join_date
                ? Carbon::parse($this->user->join_date)->startOfDay()
                : null,
        );
        if ($yearlyCheckDate === null) {
            return;
        }

        $financialYear = FinancialYear::forDate($yearlyCheckDate);
        if ($financialYear === null || ! $financialYear->is_started) {
            Log::channel('attprocess')->warning(
                'Yearly leave credit skipped: no started financial year for user '.$this->user->id
                .' on '.$yearlyCheckDate->format('Y-m-d')
            );

            return;
        }

        $joinDate = $this->user->join_date
            ? Carbon::parse($this->user->join_date)->startOfDay()
            : null;

        foreach ($monthlyRules as $gradeWiseDetail) {
            if (($gradeWiseDetail->leave_credit_type ?? 'monthly') !== 'yearly') {
                continue;
            }

            if ($gradeWiseDetail->new_joiner_based_on_credit !== null
                && ! in_array($gradeWiseDetail->new_joiner_based_on_credit, ['day_based', 'monthly_based', 'fortnightly', 'cutoff_mid'], true)) {
                Log::channel('attprocess')->warning(
                    'Unsupported yearly leave credit mode '.$gradeWiseDetail->new_joiner_based_on_credit
                    .' for user '.$this->user->id.' and leave rule '.$gradeWiseDetail->id
                );

                continue;
            }

            $annualCredit = YearlyLeaveCredit::calculateCredit(
                (float) ($gradeWiseDetail->annual_quota ?? 0),
                $gradeWiseDetail->new_joiner_based_on_credit,
                $joinDate,
                $yearlyCheckDate->copy()->startOfDay(),
                $financialYear->start_date->copy()->startOfDay(),
                $financialYear->end_date->copy()->startOfDay(),
                (int) ($gradeWiseDetail->credit_after_days ?? 0),
            );

            if ($annualCredit === null) {
                Log::channel('attprocess')->info(
                    'Yearly leave credit deferred by eligibility for user '.$this->user->id
                    .' on '.$yearlyCheckDate->format('Y-m-d')
                    .' (join date: '.$joinDate?->format('Y-m-d')
                    .', credit after days: '.$gradeWiseDetail->credit_after_days
                    .', mode: '.($gradeWiseDetail->new_joiner_based_on_credit ?? 'full_quota').')'
                );

                continue;
            }

            $transactionDate = $yearlyCheckDate->copy()->setTime(0, 0, 4);
            GeneralHelper::createOrUpdateTransaction(
                $yearlyCheckDate->format('Y'),
                get_class($userLeaveAccount),
                $userLeaveAccount->id,
                get_class($gradeWiseDetail),
                $gradeWiseDetail->id,
                $gradeWiseDetail->leave_type_id,
                get_class($this->user),
                $this->user->id,
                $transactionDate,
                $this->user->id,
                $annualCredit,
                0,
                'Annual Credit #'.$gradeWiseDetail->leave_type?->code.' '.$transactionDate,
                $this->user->id,
                $this->user->id,
                false
            );
            Log::channel('attprocess')->info(
                'Yearly leave credit transaction created/updated for user '.$this->user->id
                .' and leave rule '.$gradeWiseDetail->id.' on '.$transactionDate->format('Y-m-d')
                .' (credit: '.$annualCredit.')'
            );

            if ($gradeWiseDetail->max_accumulation <= 0) {
                continue;
            }

            $leaveAccountDetail = LeaveAccountDetail::where('leave_account_id', $userLeaveAccount->id)
                ->where('leave_type_id', $gradeWiseDetail->leave_type_id)
                ->first();

            if ($leaveAccountDetail === null || $leaveAccountDetail->balance <= $gradeWiseDetail->max_accumulation) {
                continue;
            }

            $debitDate = $transactionDate->copy()->setTime(0, 0, 5);
            GeneralHelper::createOrUpdateTransaction(
                $yearlyCheckDate->format('Y'),
                get_class($userLeaveAccount),
                $userLeaveAccount->id,
                get_class($gradeWiseDetail),
                $gradeWiseDetail->id,
                $gradeWiseDetail->leave_type_id,
                get_class($this->user),
                $this->user->id,
                $debitDate,
                $this->user->id,
                0,
                $leaveAccountDetail->balance - $gradeWiseDetail->max_accumulation,
                'Debit #'.$gradeWiseDetail->leave_type?->code.' '.$debitDate,
                $this->user->id,
                $this->user->id,
                false
            );
        }
    }

    private function countPresentDaysForMonthlyLeaveCredit(
        GradeWiseMonthlyLeaveDetail $gradeWiseDetail,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): float {
        $includedLeaveTypeIds = (array) ($gradeWiseDetail->include_leaves ?? []);
        if ($gradeWiseDetail->is_paid) {
            $includedLeaveTypeIds[] = $gradeWiseDetail->leave_type_id;
        }
        $includedLeaveTypeIds = array_values(array_unique(array_filter(
            array_map('intval', $includedLeaveTypeIds),
            fn (int $id): bool => $id > 0,
        )));

        $leaveTypeCodes = $includedLeaveTypeIds === []
            ? collect()
            : LeaveType::query()
                ->whereIn('id', $includedLeaveTypeIds)
                ->pluck('code');

        $statusCodes = $this->presentAttendanceStatusCodes(
            $gradeWiseDetail,
            $leaveTypeCodes->all(),
        );
        $attendanceRows = collect();
        for ($year = $periodStart->year; $year <= $periodEnd->year; $year++) {
            $attendance = Attendance::year($year);
            $attendanceTable = $attendance->getTable();
            $attendanceRows = $attendanceRows->merge(
                $attendance->newQuery()
                    ->join('status_masters', 'status_masters.id', '=', $attendanceTable.'.status_master_id')
                    ->where($attendanceTable.'.user_id', $this->user->id)
                    ->whereBetween($attendanceTable.'.date', [
                        max($periodStart->toDateString(), Carbon::create($year, 1, 1)->toDateString()),
                        min($periodEnd->toDateString(), Carbon::create($year, 12, 31)->toDateString()),
                    ])
                    ->whereIn('status_masters.code', $statusCodes)
                    ->get([
                        'status_masters.code',
                        $attendanceTable.'.is_half_day',
                    ]),
            );
        }

        return PresentBasedLeaveCredit::countPresentAttendanceDays(
            $attendanceRows,
            $leaveTypeCodes->all(),
            (bool) $gradeWiseDetail->count_weekly_off_as_present,
            (bool) $gradeWiseDetail->count_holiday_as_present,
        );
    }

    /**
     * @param  array<int, string>  $leaveTypeCodes
     * @return array<int, string>
     */
    private function presentAttendanceStatusCodes(
        GradeWiseMonthlyLeaveDetail $gradeWiseDetail,
        array $leaveTypeCodes,
    ): array {
        $codes = ['PP', 'PA', 'AP'];
        if ($gradeWiseDetail->count_weekly_off_as_present) {
            $codes = array_merge($codes, ['WOP', 'PWO', 'WHP']);
        }
        if ($gradeWiseDetail->count_holiday_as_present) {
            $codes[] = 'WHP';
        }

        foreach ($leaveTypeCodes as $leaveTypeCode) {
            $codes = array_merge($codes, [
                $leaveTypeCode,
                'A'.$leaveTypeCode,
                $leaveTypeCode.'A',
                'P'.$leaveTypeCode,
                $leaveTypeCode.'P',
            ]);
        }

        return array_values(array_unique($codes));
    }
}
