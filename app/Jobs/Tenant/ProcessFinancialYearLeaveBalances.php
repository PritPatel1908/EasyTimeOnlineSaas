<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\Transaction;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessFinancialYearLeaveBalances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $financialYearId;

    private ?int $authUserId;

    public function __construct(FinancialYear $financialYear, ?int $authUserId)
    {
        $this->financialYearId = (int) $financialYear->getKey();
        $this->authUserId = $authUserId;
    }

    public function handle(): void
    {
        $financialYear = FinancialYear::query()->findOrFail($this->financialYearId);
        $closeDate = Carbon::parse($financialYear->end_date)->setTime(23, 59, 59);
        $activityDate = $financialYear->updated_at?->copy() ?? Carbon::now();
        $nextYearCreditDate = $activityDate->copy()->addYear();
        $transactionYear = $activityDate->format('Y');
        $nextYearTransactionYear = $nextYearCreditDate->format('Y');
        $settlementRemarkPrefix = 'Financial year close #'.$financialYear->id.': ';

        User::query()
            ->where('id', '!=', 1)
            ->whereNull('left_date')
            ->where('is_inactive', false)
            ->with([
                'grade_wise_leave.grade_wise_monthly_leave_details',
                'grade_wise_leave.grade_wise_yearly_leave_details',
            ])
            ->chunkById(100, function ($users) use (
                $closeDate,
                $activityDate,
                $nextYearCreditDate,
                $nextYearTransactionYear,
                $transactionYear,
                $settlementRemarkPrefix,
            ): void {
                foreach ($users as $user) {
                    $leaveGroup = $user->grade_wise_leave;
                    if ($leaveGroup === null) {
                        continue;
                    }

                    $leaveRules = $leaveGroup->is_monthly
                        ? $leaveGroup->grade_wise_monthly_leave_details
                        : $leaveGroup->grade_wise_yearly_leave_details;
                    if ($leaveRules->isEmpty()) {
                        continue;
                    }

                    DB::transaction(function () use (
                        $user,
                        $leaveRules,
                        $closeDate,
                        $activityDate,
                        $nextYearCreditDate,
                        $nextYearTransactionYear,
                        $transactionYear,
                        $settlementRemarkPrefix,
                    ): void {
                        $account = LeaveAccount::query()->firstOrCreate(['user_id' => $user->id]);

                        foreach ($leaveRules->unique('leave_type_id') as $leaveRule) {
                            if ($leaveRule->leave_type_id === null) {
                                continue;
                            }

                            $balance = $this->balanceAtDate(
                                $account,
                                (int) $leaveRule->leave_type_id,
                                (int) $user->id,
                                $closeDate,
                                $transactionYear,
                                $settlementRemarkPrefix,
                            );
                            if ($balance <= 0) {
                                continue;
                            }

                            $carryForwardEnabled = (bool) $leaveRule->allow_c_f;
                            $lapseEnabled = (bool) $leaveRule->allow_leave_lapse;
                            if (! $carryForwardEnabled && ! $lapseEnabled) {
                                continue;
                            }

                            $maximumAccumulation = (float) ($leaveRule->max_accumulation ?? 0);
                            $carryForward = $carryForwardEnabled
                                ? ($maximumAccumulation > 0 ? min($balance, $maximumAccumulation) : $balance)
                                : 0.0;
                            $lapse = $lapseEnabled ? $balance - $carryForward : 0.0;
                            $debit = $carryForward + $lapse;

                            if ($debit > 0) {
                                GeneralHelper::createOrUpdateTransaction(
                                    $transactionYear,
                                    LeaveAccount::class,
                                    (int) $account->id,
                                    $leaveRule::class,
                                    (int) $leaveRule->id,
                                    (int) $leaveRule->leave_type_id,
                                    User::class,
                                    (int) $user->id,
                                    $activityDate,
                                    (int) $user->id,
                                    0,
                                    $debit,
                                    $settlementRemarkPrefix.'leave balance settled.',
                                    $this->authUserId,
                                    $this->authUserId,
                                    $lapse > 0,
                                    $lapse,
                                );
                            }

                            if ($carryForward > 0) {
                                GeneralHelper::createOrUpdateTransaction(
                                    $nextYearTransactionYear,
                                    LeaveAccount::class,
                                    (int) $account->id,
                                    $leaveRule::class,
                                    (int) $leaveRule->id,
                                    (int) $leaveRule->leave_type_id,
                                    User::class,
                                    (int) $user->id,
                                    $nextYearCreditDate,
                                    (int) $user->id,
                                    $carryForward,
                                    0,
                                    $settlementRemarkPrefix.'carried forward leave balance.',
                                    $this->authUserId,
                                    $this->authUserId,
                                    false,
                                );
                            }
                        }
                    });
                }
            });
    }

    private function balanceAtDate(
        LeaveAccount $account,
        int $leaveTypeId,
        int $userId,
        Carbon $date,
        string $transactionYear,
        string $settlementRemarkPrefix,
    ): float
    {
        $settlementRemarks = [
            $settlementRemarkPrefix.'leave balance settled.',
            $settlementRemarkPrefix.'carried forward leave balance.',
        ];

        foreach (array_unique([(int) $date->format('Y'), (int) $date->format('Y') - 1, (int) $transactionYear]) as $year) {
            if (! Schema::hasTable('transactions'.$year)) {
                continue;
            }

            $transaction = (new Transaction(year: (string) $year))
                ->where('accountable_type', LeaveAccount::class)
                ->where('accountable_id', $account->id)
                ->where('leave_type_id', $leaveTypeId)
                ->where('authable_type', User::class)
                ->where('authable_id', $userId)
                ->where('trx_datetime', '<=', $date)
                ->where(static function ($query) use ($settlementRemarks): void {
                    $query->whereNull('remarks')
                        ->orWhereNotIn('remarks', $settlementRemarks);
                })
                ->orderByDesc('trx_datetime')
                ->orderByDesc('id')
                ->first();

            if ($transaction !== null) {
                return max((float) $transaction->balance, 0);
            }
        }

        return 0.0;
    }
}
