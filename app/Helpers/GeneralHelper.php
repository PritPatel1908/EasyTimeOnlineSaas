<?php

namespace App\Helpers;

use App\Enums\StatusEnumn;
use App\Models\Tenant\LeaveLapse;
use App\Models\Tenant\Transaction;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

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
            // ->where('referenceable_type', $referenceable_type)
            // ->where('referenceable_id', $referenceable_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('authable_type', $authable_type)
            ->where('authable_id', $authable_id)
            ->where('trx_datetime', $trx_datetime)
            ->limit(1)
            ->first();

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
        $subsequentTransactions = (new Transaction(year: $year));
        $subsequentTransactions->setDynamicTable($year);
        $subsequentTransactions = $subsequentTransactions
            ->where('accountable_type', $accountable_type)
            ->where('accountable_id', $accountable_id)
            // ->where('referenceable_type', $referenceable_type)
            // ->where('referenceable_id', $referenceable_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('authable_type', $authable_type)
            ->where('authable_id', $authable_id)
            ->where('trx_datetime', '>', $trx_datetime)
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
        $account = $accountable_type::find($accountable_id);
        $leave_detail = $referenceable_type::find($referenceable_id);
        $account->leave_account_details()->updateOrCreate(
            [
                'leave_account_id' => $account->id,
                'leave_type_id' => $leave_detail->leave_type_id,
            ],
            [
                'balance' => $new_opening_balance,
            ]
        );
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
