<?php

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\Transaction;
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

class CreditLeaveApplication implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $leave_application;

    protected $sandwich_leave = false;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    // public function uniqueId(): string
    // {
    //     return $this->user->id;
    // }

    /**
     * Create a new job instance.
     */
    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leave_application = $leaveApplication;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            // $userLeaveAccount = $user->leave_account()->create(['user_id' => $user->id]);

            if ($this->leave_application?->is_only_second_half) {
                $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $this->leave_application?->user?->id]);
                $application_leave_count = 0.5;

                if ($this->leave_application?->user?->grade_wise_leave?->is_monthly) {
                    $grade_wise_detail = $this->leave_application?->user?->grade_wise_leave?->grade_wise_monthly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                        $adjusted_leave_count = $application_leave_count;
                        $this->sandwich_leave = false;

                        $this->makeTransaction(
                            $this->leave_application,
                            $userLeaveAccount,
                            $this->leave_application?->user,
                            $grade_wise_detail,
                            [],
                            $adjusted_leave_count,
                            $this->sandwich_leave,
                            null
                        );
                    }
                } else {
                    $grade_wise_detail = $this->leave_application?->user?->grade_wise_leave?->grade_wise_yearly_leave_details->where('leave_type_id', $this->leave_application?->leave_type_id)->first();
                    if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                        $adjusted_leave_count = $application_leave_count;
                        $this->sandwich_leave = false;

                        $this->makeTransaction(
                            $this->leave_application,
                            $userLeaveAccount,
                            $this->leave_application?->user,
                            $grade_wise_detail,
                            [],
                            $adjusted_leave_count,
                            $this->sandwich_leave,
                            null
                        );
                    }
                }
            } else {
                $userLeaveAccount = LeaveAccount::firstOrCreate(['user_id' => $this->leave_application?->user?->id]);
                $application_leave_count = $this->leave_application?->from_date->diffInDays($this->leave_application?->to_date);

                if ($this->leave_application?->is_second_half) {
                    $application_leave_count = $application_leave_count - 0.5;
                }

                if ($this->leave_application?->is_first_half) {
                    $application_leave_count = $application_leave_count - 0.5;
                }

                if ($this->leave_application?->user?->grade_wise_leave?->is_monthly) {
                    foreach ($this->leave_application?->user?->grade_wise_leave?->grade_wise_monthly_leave_details as $grade_wise_detail) {
                        if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                            if ($grade_wise_detail->allow_sandwich) {
                                if ($grade_wise_detail->leave_type_id == $grade_wise_detail->sandwich_leave_id) {
                                    $adjusted_leave_count = $application_leave_count + 1;
                                    $this->sandwich_leave = false;
                                } else {
                                    $weekoffOrHolidayDates = StatusMuster::where('user_id', $this->leave_application?->user?->id)
                                        ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                        ->where(function ($query) {
                                            $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                                ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                        })
                                        ->pluck('date');
                                    $adjusted_leave_count = ($application_leave_count + 1) - $weekoffOrHolidayDates->count();
                                    if ($weekoffOrHolidayDates->count()) {
                                        $this->sandwich_leave = true;
                                    } else {
                                        $this->sandwich_leave = false;
                                    }
                                }
                            } else {
                                $weekOffsAndHolidays = StatusMuster::where('user_id', $this->leave_application?->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                    ->where('status_master_id', StatusMaster::where('code', 'HL')->first()->id)
                                    ->count();

                                $adjusted_leave_count = $application_leave_count - $weekOffsAndHolidays;
                            }

                            $gradeWiseLeaveDetail = GradeWiseMonthlyLeaveDetail::where('grade_wise_leave_id', $grade_wise_detail->grade_wise_leave_id)->where('leave_type_id', $grade_wise_detail->sandwich_leave_id)->first();

                            $this->makeTransaction(
                                $this->leave_application,
                                $userLeaveAccount,
                                $this->leave_application?->user,
                                $grade_wise_detail,
                                $weekoffOrHolidayDates,
                                $adjusted_leave_count,
                                $this->sandwich_leave,
                                $gradeWiseLeaveDetail
                            );
                        }
                    }
                } else {
                    foreach ($this->leave_application?->user?->grade_wise_leave?->grade_wise_yearly_leave_details as $grade_wise_detail) {
                        if ($grade_wise_detail->leave_type_id == $this->leave_application?->leave_type_id) {
                            if ($grade_wise_detail->allow_sandwich) {
                                if ($grade_wise_detail->leave_type_id == $grade_wise_detail->sandwich_leave_id) {
                                    $adjusted_leave_count = $application_leave_count + 1;
                                    $this->sandwich_leave = false;
                                } else {
                                    $weekoffOrHolidayDates = StatusMuster::where('user_id', $this->leave_application?->user?->id)
                                        ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                        ->where(function ($query) {
                                            $query->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                                ->orWhere('status_master_id', StatusMaster::where('code', 'HL')->first()->id);
                                        })
                                        ->pluck('date');
                                    $adjusted_leave_count = ($application_leave_count + 1) - $weekoffOrHolidayDates->count();
                                    if ($weekoffOrHolidayDates->count()) {
                                        $this->sandwich_leave = true;
                                    } else {
                                        $this->sandwich_leave = false;
                                    }
                                }
                            } else {
                                $weekOffsAndHolidays = StatusMuster::where('user_id', $this->leave_application?->user?->id)
                                    ->whereBetween('date', [$this->leave_application?->from_date, $this->leave_application?->to_date])
                                    ->where('status_master_id', StatusMaster::where('code', 'WO')->first()->id)
                                    ->where('status_master_id', StatusMaster::where('code', 'HL')->first()->id)
                                    ->count();

                                $adjusted_leave_count = $application_leave_count - $weekOffsAndHolidays;
                            }

                            $gradeWiseLeaveDetail = GradeWiseYearlyLeaveDetail::where('grade_wise_leave_id', $grade_wise_detail->grade_wise_leave_id)->where('leave_type_id', $grade_wise_detail->sandwich_leave_id)->first();

                            $this->makeTransaction(
                                $this->leave_application,
                                $userLeaveAccount,
                                $this->leave_application?->user,
                                $grade_wise_detail,
                                $weekoffOrHolidayDates,
                                $adjusted_leave_count,
                                $this->sandwich_leave,
                                $gradeWiseLeaveDetail
                            );
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Leave Application. '.Str::limit($e->getMessage(), 200));
        }
    }

    public static function makeTransaction(
        $application,
        $userLeaveAccount,
        $user,
        $grade_wise_detail,
        $weekoffOrHolidayDates,
        $adjusted_leave_count,
        $sandwich_leave,
        $gradeWiseLeaveDetail
    ) {
        GeneralHelper::createOrUpdateTransaction(
            Carbon::now()?->format('Y'),
            get_class($userLeaveAccount),
            $userLeaveAccount->id,
            get_class($grade_wise_detail),
            $grade_wise_detail?->id,
            $grade_wise_detail?->leave_type_id,
            get_class($user),
            $user?->id,
            Carbon::now(),
            $user?->id,
            $adjusted_leave_count,
            0,
            'Credit #'.$grade_wise_detail?->leave_type->code.' '.Carbon::now(),
            $user->id,
            $user->id,
            false
        );

        if ($sandwich_leave) {
            $transaction = (new Transaction(year: Carbon::now()?->format('Y')));
            $transaction->setDynamicTable(Carbon::now()?->format('Y'));
            $transaction = $transaction
                ->where('accountable_type', get_class($userLeaveAccount))
                ->where('accountable_id', $userLeaveAccount->id)
                // ->where('referenceable_type', get_class($grade_wise_detail))
                // ->where('referenceable_id', $gradeWiseLeaveDetail->id)
                ->where('leave_type_id', $grade_wise_detail?->sandwich_leave_id)
                ->where('authable_type', get_class($user))
                ->where('authable_id', $user?->id)
                ->where('trx_datetime', Carbon::now())
                ->limit(1)
                ->first();
            if ($transaction) {
                // Update the existing transaction
                $transaction->update([
                    'credit' => $weekoffOrHolidayDates->count(),
                    'debit' => 0,
                    'remarks' => 'Credit #'.$grade_wise_detail->sandwich_leave->code.' '.Carbon::now(),
                ]);
                $transaction->balance = $transaction->opening_balance + $transaction->credit - $transaction->debit;
                $transaction->save();
            } else {
                // Get the latest transaction before the given datetime
                $transactionModel = (new Transaction(year: Carbon::now()?->format('Y')));
                $previousTransaction = $transactionModel->where('trx_datetime', '<', Carbon::now())
                    ->where('accountable_type', get_class($userLeaveAccount))
                    ->where('accountable_id', $userLeaveAccount->id)
                    // ->where('referenceable_type', get_class($grade_wise_detail))
                    // ->where('referenceable_id', $gradeWiseLeaveDetail->id)
                    ->where('leave_type_id', $grade_wise_detail?->sandwich_leave_id)
                    ->where('authable_type', get_class($user))
                    ->where('authable_id', $user?->id)
                    ->orderBy('trx_datetime', 'desc')
                    ->first();

                // Calculate the opening balance for the new transaction
                $opening_balance = $previousTransaction ? $previousTransaction->balance : 0.00;

                // Create a new transaction
                $transaction = (new Transaction(year: Carbon::now()?->format('Y')));
                $transaction->setDynamicTable(Carbon::now()?->format('Y'));
                $transaction = $transaction->create([
                    'accountable_type' => get_class($userLeaveAccount),
                    'accountable_id' => $userLeaveAccount->id,
                    'referenceable_type' => get_class($grade_wise_detail),
                    'referenceable_id' => $gradeWiseLeaveDetail->id,
                    'leave_type_id' => $grade_wise_detail?->sandwich_leave_id,
                    'authable_type' => get_class($user),
                    'authable_id' => $user?->id,
                    'trx_datetime' => Carbon::now(),
                    'trx_user_id' => $user?->id,
                    'opening_balance' => $opening_balance,
                    'credit' => $weekoffOrHolidayDates->count(),
                    'debit' => 0,
                    'balance' => $opening_balance + $weekoffOrHolidayDates->count() - 0,
                    'remarks' => 'Credit #'.$grade_wise_detail->sandwich_leave->code.' '.Carbon::now(),
                    'status' => 1,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }

            // Calculate the new balance
            // Step 2: Update the balances of subsequent transactions
            $subsequentTransactions = (new Transaction(year: Carbon::now()?->format('Y')));
            $subsequentTransactions->setDynamicTable(Carbon::now()?->format('Y'));
            $subsequentTransactions = $subsequentTransactions
                ->where('accountable_type', get_class($userLeaveAccount))
                ->where('accountable_id', $userLeaveAccount->id)
                // ->where('referenceable_type', get_class($grade_wise_detail))
                // ->where('referenceable_id', $gradeWiseLeaveDetail->id)
                ->where('leave_type_id', $grade_wise_detail?->sandwich_leave_id)
                ->where('authable_type', get_class($user))
                ->where('authable_id', $user?->id)
                ->where('trx_datetime', '>', Carbon::now())
                ->orderBy('trx_datetime', 'asc')
                ->get();

            $new_opening_balance = $transaction->balance;
            foreach ($subsequentTransactions as $subsequentTransaction) {
                $subsequentTransaction->opening_balance = $new_opening_balance;
                $subsequentTransaction->balance
                    = $subsequentTransaction->opening_balance
                    + $subsequentTransaction->credit
                    - $subsequentTransaction->debit;
                $subsequentTransaction->save();
                $new_opening_balance = $subsequentTransaction->balance;
            }

            // UPDATE ACOUNT BALANCE FROM ACCOUNTABLE
            $account = $userLeaveAccount::find($userLeaveAccount->id);
            // $leave_detail = $referenceable_type::find($referenceable_id);
            $account->leave_account_details()->updateOrCreate(
                [
                    'leave_account_id' => $account->id,
                    'leave_type_id' => $grade_wise_detail->sandwich_leave_id,
                ],
                [
                    'balance' => $new_opening_balance,
                ]
            );
        }
    }
}
