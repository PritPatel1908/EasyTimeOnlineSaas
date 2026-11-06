<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveAccountDetail;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeaveAccountController extends Controller
{
    private const INDEX_URL = 'employee-structure/leave-accounts';

    public function index(): View
    {
        $accounts = LeaveAccount::query()
            ->with('user')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $latestDetails = LeaveAccountDetail::query()
            ->with('leave_type')
            ->whereIn('leave_account_id', $accounts->getCollection()->modelKeys())
            ->orderByDesc('transaction_year')
            ->orderByDesc('transaction_id')
            ->orderByDesc('id')
            ->get()
            ->unique(static fn (LeaveAccountDetail $detail): string => $detail->leave_account_id.':'.$detail->leave_type_id);

        $balancesByAccount = [];
        foreach ($latestDetails as $detail) {
            $balancesByAccount[$detail->leave_account_id][] = [
                'leave_type' => $detail->leave_type,
                'balance' => (float) $detail->balance,
            ];
        }

        foreach ($accounts as $account) {
            $accountBalances = $balancesByAccount[$account->id] ?? [];
            $account->setAttribute('final_leave_balances', $accountBalances);
            $account->setAttribute(
                'final_balance_total',
                array_sum(array_column($accountBalances, 'balance')),
            );
        }

        return view('employee-structure.leave-accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    public function create(): View
    {
        return view('employee-structure.leave-accounts.form', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('leave_accounts', 'user_id')],
        ]);

        $validated['code'] = User::query()->findOrFail($validated['user_id'])->code;

        LeaveAccount::query()->create($validated);

        return redirect(url(self::INDEX_URL))->with('success', 'Leave account created successfully.');
    }

    public function edit(Request $request): View
    {
        $account = LeaveAccount::query()
            ->with('user')
            ->findOrFail($request->route('leave_account'));

        return view('employee-structure.leave-accounts.form', $this->formOptions($account));
    }

    public function update(Request $request): RedirectResponse
    {
        $account = LeaveAccount::query()->findOrFail($request->route('leave_account'));
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('leave_accounts', 'user_id')->ignore($account->id),
            ],
        ]);

        $validated['code'] = User::query()->findOrFail($validated['user_id'])->code;

        $account->update($validated);

        return redirect(url(self::INDEX_URL))->with('success', 'Leave account updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $account = LeaveAccount::query()->findOrFail($request->route('leave_account'));

        if ($account->leave_account_details()->exists()) {
            return redirect(url(self::INDEX_URL))
                ->with('error', 'This account cannot be deleted while leave account details are linked to it.');
        }

        DB::transaction(static function () use ($account): void {
            $account->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Leave account deleted successfully.');
    }

    private function formOptions(?LeaveAccount $account = null): array
    {
        $users = User::query()
            ->when(
                $account !== null,
                fn ($query) => $query->where(function ($query) use ($account): void {
                    $query->whereDoesntHave('leave_account')->orWhereKey($account->user_id);
                }),
                fn ($query) => $query->whereDoesntHave('leave_account'),
            )
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return [
            'account' => $account,
            'users' => $users,
        ];
    }
}
