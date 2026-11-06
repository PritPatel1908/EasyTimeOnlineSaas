<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\LeaveAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeaveAccountDetailController extends Controller
{
    public function index(): View
    {
        $transactionTables = collect(Schema::getTableListing(null, false))
            ->map(static function (string $table): ?array {
                if (preg_match('/\Atransactions(\d{4})\z/', $table, $matches) !== 1) {
                    return null;
                }

                return ['table' => $table, 'year' => $matches[1]];
            })
            ->filter()
            ->sortBy('year')
            ->values();

        $yearlyQueries = $transactionTables->map(static function (array $table) {
            return DB::table($table['table'])
                ->selectRaw('? as transaction_year', [$table['year']])
                ->addSelect([
                    'id',
                    'accountable_id as leave_account_id',
                    'leave_type_id',
                    'opening_balance',
                    'credit',
                    'debit',
                    'balance',
                    'referenceable_type',
                    'referenceable_id',
                    'trx_datetime',
                    'remarks',
                ])
                ->where('accountable_type', LeaveAccount::class)
                ->whereNull('deleted_at');
        });

        if ($yearlyQueries->isEmpty()) {
            $ledger = DB::table('leave_accounts')
                ->selectRaw('NULL as transaction_year, NULL as id, NULL as leave_account_id, NULL as leave_type_id, NULL as opening_balance, NULL as credit, NULL as debit, NULL as balance, NULL as referenceable_type, NULL as referenceable_id, NULL as trx_datetime, NULL as remarks')
                ->whereRaw('1 = 0');
        } else {
            $ledger = $yearlyQueries->shift();
            foreach ($yearlyQueries as $yearlyQuery) {
                $ledger->unionAll($yearlyQuery);
            }
        }

        $transactions = DB::query()
            ->fromSub($ledger, 'leave_transactions')
            ->select([
                'transaction_year',
                'id',
                'leave_account_id',
                'leave_type_id',
                'opening_balance',
                'credit',
                'debit',
                'balance',
                'referenceable_type',
                'referenceable_id',
                'trx_datetime',
                'remarks',
            ])
            ->orderByDesc('trx_datetime')
            ->orderByDesc('transaction_year')
            ->orderByDesc('id');

        $transactions = $transactions->paginate(15)->withQueryString();
        $accountIds = $transactions->getCollection()->pluck('leave_account_id')->filter()->unique();
        $leaveTypeIds = $transactions->getCollection()->pluck('leave_type_id')->filter()->unique();

        return view('employee-structure.leave-account-details.index', [
            'transactions' => $transactions,
            'accountsById' => LeaveAccount::query()->with('user')->whereIn('id', $accountIds)->get()->keyBy('id'),
            'leaveTypesById' => \App\Models\Tenant\LeaveType::query()->whereIn('id', $leaveTypeIds)->get()->keyBy('id'),
        ]);
    }
}
