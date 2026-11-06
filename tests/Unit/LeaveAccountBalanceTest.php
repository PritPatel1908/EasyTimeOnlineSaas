<?php

namespace Tests\Unit;

use App\Support\LeaveAccountBalance;
use Tests\TestCase;

class LeaveAccountBalanceTest extends TestCase
{
    public function test_it_returns_the_latest_transaction_balance_for_a_leave_type(): void
    {
        $details = collect([
            (object) ['id' => 1, 'leave_type_id' => 3, 'transaction_year' => '2026', 'transaction_id' => 1, 'balance' => 2],
            (object) ['id' => 2, 'leave_type_id' => 3, 'transaction_year' => '2026', 'transaction_id' => 2, 'balance' => 4],
            (object) ['id' => 3, 'leave_type_id' => 3, 'transaction_year' => '2026', 'transaction_id' => 3, 'balance' => 6],
            (object) ['id' => 4, 'leave_type_id' => 3, 'transaction_year' => '2026', 'transaction_id' => 4, 'balance' => 4],
        ]);

        $this->assertSame(4.0, LeaveAccountBalance::forLeaveType($details, 3));
        $this->assertSame(['3' => 4.0], LeaveAccountBalance::all($details));
    }

    public function test_it_prefers_a_newer_transaction_year(): void
    {
        $details = collect([
            (object) ['id' => 10, 'leave_type_id' => 3, 'transaction_year' => '2026', 'transaction_id' => 30, 'balance' => 8],
            (object) ['id' => 11, 'leave_type_id' => 3, 'transaction_year' => '2027', 'transaction_id' => 1, 'balance' => 4],
        ]);

        $this->assertSame(4.0, LeaveAccountBalance::forLeaveType($details, 3));
    }
}
