<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\GeneralHelper;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualLeaveBalance;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveAccountDetailBalanceTest extends TestCase
{
    public function test_account_detail_keeps_running_balance_and_latest_transaction_amounts(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable();
        });
        Schema::create('leave_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
        });
        Schema::create('leave_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->timestamps();
        });
        Schema::create('leave_account_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_account_id');
            $table->foreignId('leave_type_id');
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('type')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->decimal('credit_balance', 10, 2)->default(0);
            $table->decimal('debit_balance', 10, 2)->default(0);
            $table->timestamps();
        });
        $migration = require base_path('database/migrations/tenant/2026_10_25_160500_add_transaction_reference_to_leave_account_details_table.php');
        $migration->up();
        Schema::create('manual_leave_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_type_id');
            $table->decimal('balance', 10, 2);
            $table->dateTime('date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('manual_leave_balance_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_leave_balance_id');
            $table->foreignId('user_id');
            $table->timestamps();
        });
        Schema::create('leave_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_type_id');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('transactions2026', function (Blueprint $table): void {
            $table->id();
            $table->string('accountable_type');
            $table->unsignedBigInteger('accountable_id');
            $table->string('referenceable_type');
            $table->unsignedBigInteger('referenceable_id');
            $table->string('authable_type');
            $table->unsignedBigInteger('authable_id');
            $table->dateTime('trx_datetime')->nullable();
            $table->unsignedBigInteger('trx_user_id')->nullable();
            $table->unsignedBigInteger('leave_type_id')->nullable();
            $table->decimal('opening_balance', 10, 2);
            $table->decimal('credit', 10, 2);
            $table->decimal('debit', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('remarks')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->decimal('lapse', 10, 2)->nullable()->default(0);
            $table->decimal('encashment', 10, 2)->nullable()->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 10]);
        DB::table('leave_types')->insert(['id' => 3, 'code' => 'AL']);
        DB::table('leave_accounts')->insert([
            'id' => 1,
            'user_id' => 10,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);
        DB::table('manual_leave_balances')->insert([
            'id' => 1,
            'leave_type_id' => 3,
            'balance' => 2,
            'date' => '2026-10-25 09:58:47',
            'created_at' => '2026-10-25 09:58:47',
            'updated_at' => '2026-10-25 09:58:47',
        ]);
        DB::table('manual_leave_balances')->insert([
            'id' => 2,
            'leave_type_id' => 3,
            'balance' => 2,
            'date' => '2026-10-25 15:56:26',
            'created_at' => '2026-10-25 15:56:26',
            'updated_at' => '2026-10-25 15:56:26',
        ]);
        DB::table('manual_leave_balance_users')->insert([
            'manual_leave_balance_id' => 1,
            'user_id' => 10,
            'created_at' => '2026-10-25 09:58:47',
            'updated_at' => '2026-10-25 09:58:47',
        ]);
        DB::table('manual_leave_balance_users')->insert([
            'manual_leave_balance_id' => 2,
            'user_id' => 10,
            'created_at' => '2026-10-25 15:56:26',
            'updated_at' => '2026-10-25 15:56:26',
        ]);
        DB::table('leave_applications')->insert([
            'id' => 1,
            'leave_type_id' => 3,
            'created_at' => '2026-10-25 15:33:12',
            'updated_at' => '2026-10-25 15:33:12',
        ]);

        GeneralHelper::syncManualLeaveBalanceToAccounts(ManualLeaveBalance::query()->findOrFail(1));
        GeneralHelper::syncManualLeaveBalanceToAccounts(ManualLeaveBalance::query()->findOrFail(2));
        $account = LeaveAccount::query()->firstOrFail();
        GeneralHelper::createOrUpdateTransaction(
            '2026',
            LeaveAccount::class,
            (int) $account->id,
            LeaveApplication::class,
            1,
            3,
            User::class,
            10,
            Carbon::parse('2026-10-25 15:33:12'),
            10,
            0,
            6,
            'Leave debit',
            10,
            10,
            false,
        );
        GeneralHelper::syncManualLeaveBalanceToAccounts(ManualLeaveBalance::query()->findOrFail(2));

        $details = DB::table('leave_account_details')
            ->where('leave_type_id', 3)
            ->orderBy('transaction_id')
            ->get();

        $this->assertCount(3, $details);
        $this->assertSame([
            [2.0, 2.0, 0.0, ManualLeaveBalance::class, 1],
            [-2.0, 2.0, 0.0, ManualLeaveBalance::class, 2],
            [-4.0, 0.0, 6.0, LeaveApplication::class, 1],
        ], $details->map(static fn (object $detail): array => [
            (float) $detail->balance,
            (float) $detail->credit_balance,
            (float) $detail->debit_balance,
            $detail->type,
            (int) $detail->type_id,
        ])->all());
        $this->assertSame(['2026', '2026', '2026'], $details->pluck('transaction_year')->all());
    }
}
