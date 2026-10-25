<?php

namespace App\Helpers;

use App\Enums\StatusEnumn;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\LeaveLapse;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\ManualLeaveBalance;
use App\Models\Tenant\Transaction;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class GeneralHelper
{
    public static function checkSettings($model)
    {
        // TODO: Implement License Check Code
        $data = [
            'us'.'er_'.'lim'.'it' => 'N/A',
            'lo'.'ca'.'ti'.'on'.'_'.'lim'.'it' => 'N/A',
            'co'.'m'.'pa'.'ny'.'_'.'lim'.'it' => 'N/A',
            'fe'.'at'.'u'.'re'.'s_l'.'is'.'t' => [],
            'se'.'r'.'ve'.'r_'.'i'.'d' => 'N/A',
        ];

        $data = Session::get('i'.'n'.'f'.'o', $data);
        $features_list = $data['fe'.'at'.'u'.'re'.'s_l'.'is'.'t'];

        $cuc = User::where('status', StatusEnumn::Active)->count();

        $data1 = [

            'se'.'r'.'v'.'e'.'r_'.'i'.'d' => 'N/A',
        ];
        $data1 = Session::get('se'.'r'.'v'.'e'.'r_'.'i'.'d', $data1);

        if ($data1 !== $data['se'.'r'.'ve'.'r_'.'i'.'d']) {
            Session::remove('se'.'r'.'v'.'e'.'r_'.'i'.'d');

            return false;
        }

        if ($cuc > $data['us'.'er_'.'lim'.'it']) {
            $features_lists = ['users'];
            if (in_array($model, $features_lists)) {
                return setting($model, '0') != '0';
            } else {
                return false;
            }
        } else {
            $features_lists = array_map(function ($feature) {
                return explode('::', $feature)[1];
            }, $features_list);

            if (in_array($model, $features_lists)) {
                return setting($model, '0') != '0';
            } else {
                return false;
            }
        }

        // return setting($model, "0") != "0";
    }

    public static function getEloquentSqlWithBindings(Builder $query)
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }

    public static function createOrUpdateTransaction(
        string $year,
        string $accountable_type,
        int $accountable_id,
        string $referenceable_type,
        int $referenceable_id,
        int $leave_type_id,
        string $authable_type,
        int $authable_id,
        $trx_datetime,
        $trx_user_id,
        $credit,
        $debit,
        $remarks,
        $created_by,
        $updated_by,
        bool $is_lapse
    ) {
        if ($accountable_type == null) {
            // dd('accountable_type is null');
        }

        // Step 1: Find or create the transaction with the specific datetime and user ID
        $transaction = (new Transaction(year: $year));
        $transaction->setDynamicTable($year);
        $transaction = $transaction
            ->where('accountable_type', $accountable_type)
            ->where('accountable_id', $accountable_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('authable_type', $authable_type)
            ->where('authable_id', $authable_id)
            ->where('trx_datetime', $trx_datetime)
            ->where('referenceable_type', $referenceable_type)
            ->where('referenceable_id', $referenceable_id);
        $transaction = $transaction->limit(1)->first();

        if ($transaction) {
            // Update the existing transaction
            $transaction->update([
                'credit' => $credit,
                'debit' => $debit,
                'remarks' => $remarks,
            ]);
            // $transaction->credit = $credit;
            // $transaction->debit = $debit;
            // $transaction->remarks = $remarks;
            $transaction->balance = $transaction->opening_balance + $transaction->credit - $transaction->debit;
            $transaction->save();
        } else {
            // Get the latest transaction before the given datetime
            $transactionModel = (new Transaction(year: $year));
            $previousTransaction = $transactionModel->where('trx_datetime', '<', $trx_datetime)
                ->where('accountable_type', $accountable_type)
                ->where('accountable_id', $accountable_id)
                // ->where('referenceable_type', $referenceable_type)
                // ->where('referenceable_id', $referenceable_id)
                ->where('leave_type_id', $leave_type_id)
                ->where('authable_type', $authable_type)
                ->where('authable_id', $authable_id)
                ->orderBy('trx_datetime', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Calculate the opening balance for the new transaction
            $opening_balance = $previousTransaction ? $previousTransaction->balance : 0.00;

            // Create a new transaction
            $transaction = (new Transaction(year: $year));
            $transaction->setDynamicTable($year);
            $transaction = $transaction->create([
                'accountable_type' => $accountable_type,
                'accountable_id' => $accountable_id,
                'referenceable_type' => $referenceable_type,
                'referenceable_id' => $referenceable_id,
                'leave_type_id' => $leave_type_id,
                'authable_type' => $authable_type,
                'authable_id' => $authable_id,
                'trx_datetime' => $trx_datetime,
                'trx_user_id' => $trx_user_id,
                'opening_balance' => $opening_balance,
                'credit' => $credit,
                'debit' => $debit,
                'balance' => $opening_balance + $credit - $debit,
                'remarks' => $remarks,
                'status' => 1,
                'created_by' => $created_by,
                'updated_by' => $updated_by,
            ]);

            if ($is_lapse) {
                $transaction->lapse = $debit;
                $transaction->save();

                $leave_detail = $referenceable_type::find($referenceable_id);
                LeaveLapse::updateOrCreate(
                    [
                        'date' => $trx_datetime,
                        'user_id' => $authable_id,
                        'leave_type_id' => $leave_detail->leave_type_id,
                    ],
                    [
                        'lapse_count' => $debit,
                    ]
                );
            }
        }

        // Calculate the new balance
        // Step 2: Update the balances of subsequent transactions
        $transactions = (new Transaction(year: $year));
        $transactions->setDynamicTable($year);
        $transactions = $transactions
            ->where('accountable_type', $accountable_type)
            ->where('accountable_id', $accountable_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('authable_type', $authable_type)
            ->where('authable_id', $authable_id)
            ->orderBy('trx_datetime', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $new_opening_balance = 0.0;
        foreach ($transactions as $index => $ledgerTransaction) {
            if ($index === 0) {
                $new_opening_balance = (float) $ledgerTransaction->opening_balance;
            } else {
                $ledgerTransaction->opening_balance = $new_opening_balance;
            }
            $ledgerTransaction->balance = $ledgerTransaction->opening_balance
                + $ledgerTransaction->credit
                - $ledgerTransaction->debit;
            $ledgerTransaction->save();
            $new_opening_balance = (float) $ledgerTransaction->balance;
        }

        $account = $accountable_type::findOrFail($accountable_id);
        self::syncLeaveAccountDetails($account, $year, $leave_type_id);
    }

    private static function syncLeaveAccountDetails(
        LeaveAccount $account,
        string $year,
        int $leaveTypeId,
    ): void {
        $transactions = (new Transaction(year: $year))
            ->where('accountable_type', $account::class)
            ->where('accountable_id', $account->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('authable_type', User::class)
            ->where('authable_id', $account->user_id)
            ->orderBy('trx_datetime')
            ->orderBy('id')
            ->get();

        foreach ($transactions as $transaction) {
            $detail = $account->leave_account_details()->firstOrNew([
                'leave_account_id' => $account->id,
                'leave_type_id' => $leaveTypeId,
                'transaction_year' => $year,
                'transaction_id' => $transaction->id,
            ]);
            $detail->fill([
                'balance' => $transaction->balance,
                'type' => $transaction->referenceable_type,
                'type_id' => $transaction->referenceable_id,
                'credit_balance' => $transaction->credit,
                'debit_balance' => $transaction->debit,
            ])->save();
        }
    }

    public static function syncManualLeaveBalanceToAccounts(ManualLeaveBalance $manualLeaveBalance): void
    {
        $manualLeaveBalance->load(['Users', 'leave_type']);
        $date = $manualLeaveBalance->date;
        $leaveType = $manualLeaveBalance->leave_type;

        if ($date === null || $leaveType === null) {
            throw new InvalidArgumentException('A manual leave balance must have a date and leave type before it can be applied.');
        }

        $credit = max((float) $manualLeaveBalance->balance, 0);
        $debit = max(-(float) $manualLeaveBalance->balance, 0);

        foreach ($manualLeaveBalance->Users as $user) {
            $account = LeaveAccount::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['code' => $user->code],
            );

            self::createOrUpdateTransaction(
                $date->format('Y'),
                $account::class,
                (int) $account->id,
                $manualLeaveBalance::class,
                (int) $manualLeaveBalance->id,
                (int) $manualLeaveBalance->leave_type_id,
                $user::class,
                (int) $user->id,
                Carbon::parse($date),
                (int) $user->id,
                $credit,
                $debit,
                ($credit > 0 ? 'Credit #' : 'Debit #').$leaveType->code.' '.$date->toDateTimeString(),
                (int) ($manualLeaveBalance->created_by ?? $user->id),
                (int) ($manualLeaveBalance->updated_by ?? $user->id),
                false,
            );
        }
    }

    /**
     * @param  array<int, int>  $userIds
     */
    public static function reverseLeaveApplicationBalance(
        LeaveApplication $application,
        array $userIds,
        ?int $actorId,
    ): void {
        DB::transaction(static function () use ($application, $userIds, $actorId): void {
            self::updateLeaveApplicationTransactions($application, $userIds, $actorId, true);
        });
    }

    /**
     * @param  array<int, int>  $userIds
     */
    public static function clearLeaveApplicationBalance(
        LeaveApplication $application,
        array $userIds,
        ?int $actorId,
    ): void {
        DB::transaction(static function () use ($application, $userIds, $actorId): void {
            self::updateLeaveApplicationTransactions($application, $userIds, $actorId, false);
        });
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private static function updateLeaveApplicationTransactions(
        LeaveApplication $application,
        array $userIds,
        ?int $actorId,
        bool $reverse,
    ): void {
        $year = $application->created_at?->format('Y');
        if ($year === null) {
            throw new InvalidArgumentException('A leave application must have a creation date before its balance can be reversed.');
        }

        $applicationLedger = new Transaction(year: $year);
        $applicationLedger->setDynamicTable($year);
        $ledgerUserIds = $applicationLedger
            ->where('referenceable_type', LeaveApplication::class)
            ->where('referenceable_id', $application->id)
            ->where('accountable_type', LeaveAccount::class)
            ->pluck('authable_id')
            ->map(static fn (mixed $userId): int => (int) $userId)
            ->all();

        $userIds = array_values(array_unique(array_merge($userIds, $ledgerUserIds)));
        foreach (array_unique($userIds) as $userId) {
            $user = User::query()->findOrFail($userId);
            $account = LeaveAccount::query()->where('user_id', $user->id)->first();
            if ($account === null) {
                continue;
            }

            $ledger = new Transaction(year: $year);
            $ledger->setDynamicTable($year);
            $applicationTransactions = $ledger
                ->where('accountable_type', LeaveAccount::class)
                ->where('accountable_id', $account->id)
                ->where('referenceable_type', LeaveApplication::class)
                ->where('referenceable_id', $application->id)
                ->where('authable_type', User::class)
                ->where('authable_id', $user->id)
                ->get();

            foreach ($applicationTransactions as $applicationTransaction) {
                if ($reverse && (float) $applicationTransaction->debit <= 0) {
                    continue;
                }

                $applicationTransaction->credit = $reverse
                    ? (float) $applicationTransaction->debit
                    : 0;
                $applicationTransaction->debit = 0;
                $applicationTransaction->remarks = $reverse
                    ? 'Reversed debit for leave application #'.$application->id
                    : 'Reset leave debit for application #'.$application->id;
                $applicationTransaction->updated_by = $actorId;
                $applicationTransaction->save();

                $transactions = (new Transaction(year: $year))
                    ->where('accountable_type', LeaveAccount::class)
                    ->where('accountable_id', $account->id)
                    ->where('leave_type_id', $applicationTransaction->leave_type_id)
                    ->where('authable_type', User::class)
                    ->where('authable_id', $user->id)
                    ->orderBy('trx_datetime')
                    ->orderBy('id')
                    ->get();

                $balance = null;
                foreach ($transactions as $index => $transaction) {
                    if ($index > 0) {
                        $transaction->opening_balance = $balance;
                    }
                    $transaction->balance = $transaction->opening_balance
                        + $transaction->credit
                        - $transaction->debit;
                    $transaction->save();
                    $balance = $transaction->balance;
                }

                $latestTransaction = $transactions->last();
                if ($latestTransaction !== null) {
                    self::syncLeaveAccountDetails(
                        $account,
                        $year,
                        (int) $applicationTransaction->leave_type_id,
                    );
                }
            }
        }
    }

    public static function handleCarryForwardLeave(
        $year,
        string $accountable_type,
        int $accountable_id,
        string $referenceable_type,
        int $referenceable_id,
        int $leave_type_id,
        string $authable_type,
        int $authable_id,
        $trx_datetime,
        $trx_user_id,
        $balance,
        $remarks,
        $created_by,
        $updated_by,
        $carry_forward,
        $credit_next
    ) {
        // self::createOrUpdateTransaction(
        //     $year->copy()->subDay()->format('Y'),
        //     $accountable_type,
        //     $accountable_id,
        //     $referenceable_type,
        //     $referenceable_id,
        //     $authable_type,
        //     $authable_id,
        //     $trx_datetime->copy()->subDay(),
        //     $trx_user_id,
        //     0,
        //     $debit,
        //     $debit . " leave is lapse",
        //     $created_by,
        //     $updated_by,
        //     true
        // );

        if ($carry_forward) {
            if ($credit_next == false) {
                self::createOrUpdateTransaction(
                    $year->copy()->subDay()->format('Y'),
                    $accountable_type,
                    $accountable_id,
                    $referenceable_type,
                    $referenceable_id,
                    $leave_type_id,
                    $authable_type,
                    $authable_id,
                    $trx_datetime->copy()->subDay()->setTime(23, 59, 59, 000000),
                    $trx_user_id,
                    0,
                    $balance,
                    $remarks,
                    // $balance . " leave is debit, due to carry forward leave balance to next year",
                    $created_by,
                    $updated_by,
                    false
                );
            }

            if ($credit_next) {
                self::createOrUpdateTransaction(
                    $year->copy()->format('Y'),
                    $accountable_type,
                    $accountable_id,
                    $referenceable_type,
                    $referenceable_id,
                    $leave_type_id,
                    $authable_type,
                    $authable_id,
                    $trx_datetime->copy()->setTime(00, 00, 01, 000000),
                    $trx_user_id,
                    $balance,
                    0,
                    // $remarks,
                    $remarks,
                    $created_by,
                    $updated_by,
                    false
                );
            }
        } else {
            self::createOrUpdateTransaction(
                $year->copy()->subDay()->format('Y'),
                $accountable_type,
                $accountable_id,
                $referenceable_type,
                $referenceable_id,
                $leave_type_id,
                $authable_type,
                $authable_id,
                $trx_datetime->copy()->subDay()->setTime(23, 59, 59, 000000),
                $trx_user_id,
                0,
                $balance,
                $remarks,
                // $balance . " leave is debit, due to  leave balance to next year",
                $created_by,
                $updated_by,
                false
            );
        }

        // self::createOrUpdateTransaction(
        //     $year->copy()->format('Y'),
        //     $accountable_type,
        //     $accountable_id,
        //     $referenceable_type,
        //     $referenceable_id,
        //     $authable_type,
        //     $authable_id,
        //     $trx_datetime->copy()->addSecond(),
        //     $trx_user_id,
        //     $credit,
        //     0,
        //     $credit . " leave is credit due to this yesr eligible leave credit",
        //     $created_by,
        //     $updated_by,
        //     false
        // );
    }
}
