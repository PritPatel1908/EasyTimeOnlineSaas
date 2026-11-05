<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Tenant\ProcessFinancialYearLeaveBalances;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\LeaveAccount;
use App\Models\Tenant\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProcessFinancialYearLeaveBalancesTest extends TestCase
{
    public function test_close_carries_only_the_accumulation_limit_and_lapses_the_remainder(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grade_wise_leave_id')->nullable();
            $table->date('left_date')->nullable();
            $table->boolean('is_inactive')->default(false);
            $table->softDeletes();
        });
        Schema::create('financial_years', function (Blueprint $table): void {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->softDeletes();
        });
        Schema::create('grade_wise_leaves', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_monthly')->default(false);
            $table->softDeletes();
        });
        Schema::create('grade_wise_yearly_leave_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grade_wise_leave_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->boolean('allow_c_f')->default(false);
            $table->boolean('allow_leave_lapse')->default(false);
            $table->unsignedInteger('max_accumulation')->default(0);
        });
        Schema::create('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grade_wise_leave_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->boolean('allow_c_f')->default(false);
            $table->boolean('allow_leave_lapse')->default(false);
            $table->unsignedInteger('max_accumulation')->default(0);
        });
        Schema::create('leave_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('leave_account_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('leave_account_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('type')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->string('transaction_year')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->decimal('credit_balance', 10, 2)->default(0);
            $table->decimal('debit_balance', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('leave_lapses', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('leave_type_id')->nullable();
            $table->decimal('lapse_count', 10, 2)->nullable();
            $table->timestamps();
        });

        foreach (['2027', '2028'] as $year) {
            Schema::create('transactions'.$year, function (Blueprint $table): void {
                $table->id();
                $table->string('accountable_type');
                $table->unsignedBigInteger('accountable_id');
                $table->string('referenceable_type');
                $table->unsignedBigInteger('referenceable_id');
                $table->string('authable_type');
                $table->unsignedBigInteger('authable_id');
                $table->dateTime('trx_datetime')->nullable();
                $table->unsignedBigInteger('trx_user_id')->nullable();
                $table->unsignedBigInteger('leave_type_id');
                $table->decimal('opening_balance', 10, 2);
                $table->decimal('credit', 10, 2);
                $table->decimal('debit', 10, 2);
                $table->decimal('balance', 10, 2);
                $table->string('remarks')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->decimal('lapse', 10, 2)->default(0);
                $table->decimal('encashment', 10, 2)->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        DB::table('users')->insert([
            [
                'id' => 10,
                'grade_wise_leave_id' => 1,
                'is_inactive' => false,
            ],
            [
                'id' => 11,
                'grade_wise_leave_id' => 2,
                'is_inactive' => false,
            ],
        ]);
        DB::table('financial_years')->insert([
            'id' => 2,
            'start_date' => '2027-04-01',
            'end_date' => '2028-03-31',
        ]);
        DB::table('grade_wise_leaves')->insert([
            ['id' => 1, 'is_monthly' => false],
            ['id' => 2, 'is_monthly' => true],
        ]);
        DB::table('grade_wise_yearly_leave_details')->insert([
            'id' => 3,
            'grade_wise_leave_id' => 1,
            'leave_type_id' => 4,
            'allow_c_f' => true,
            'allow_leave_lapse' => true,
            'max_accumulation' => 10,
        ]);
        DB::table('grade_wise_monthly_leave_details')->insert([
            'id' => 6,
            'grade_wise_leave_id' => 2,
            'leave_type_id' => 5,
            'allow_c_f' => true,
            'allow_leave_lapse' => true,
            'max_accumulation' => 3,
        ]);
        DB::table('leave_accounts')->insert([
            [
                'id' => 5,
                'user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'user_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('transactions2027')->insert([
            'accountable_type' => LeaveAccount::class,
            'accountable_id' => 5,
            'referenceable_type' => 'App\\Models\\Tenant\\ManualLeaveBalance',
            'referenceable_id' => 7,
            'authable_type' => User::class,
            'authable_id' => 10,
            'trx_datetime' => '2027-06-30 12:00:00',
            'trx_user_id' => 10,
            'leave_type_id' => 4,
            'opening_balance' => 0,
            'credit' => 15,
            'debit' => 0,
            'balance' => 15,
            'status' => 1,
            'lapse' => 0,
            'encashment' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('transactions2027')->insert([
            'accountable_type' => LeaveAccount::class,
            'accountable_id' => 6,
            'referenceable_type' => 'App\\Models\\Tenant\\ManualLeaveBalance',
            'referenceable_id' => 8,
            'authable_type' => User::class,
            'authable_id' => 11,
            'trx_datetime' => '2027-06-30 12:00:00',
            'trx_user_id' => 11,
            'leave_type_id' => 5,
            'opening_balance' => 0,
            'credit' => 8,
            'debit' => 0,
            'balance' => 8,
            'status' => 1,
            'lapse' => 0,
            'encashment' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $job = new ProcessFinancialYearLeaveBalances(FinancialYear::query()->findOrFail(2), 10);
        $job->handle();
        $job->handle();

        $yearlyCloseTransaction = DB::table('transactions2027')
            ->where('authable_id', 10)
            ->where('trx_datetime', '2028-03-31 23:59:59')
            ->first();
        $monthlyCloseTransaction = DB::table('transactions2027')
            ->where('authable_id', 11)
            ->where('trx_datetime', '2028-03-31 23:59:59')
            ->first();
        $yearlyNextYearTransaction = DB::table('transactions2028')->where('authable_id', 10)->first();
        $monthlyNextYearTransaction = DB::table('transactions2028')->where('authable_id', 11)->first();

        $this->assertSame(0.0, (float) $yearlyCloseTransaction->balance);
        $this->assertSame(15.0, (float) $yearlyCloseTransaction->debit);
        $this->assertSame(5.0, (float) $yearlyCloseTransaction->lapse);
        $this->assertSame(10.0, (float) $yearlyNextYearTransaction->credit);
        $this->assertSame(10.0, (float) $yearlyNextYearTransaction->balance);
        $this->assertSame(0.0, (float) $monthlyCloseTransaction->balance);
        $this->assertSame(8.0, (float) $monthlyCloseTransaction->debit);
        $this->assertSame(5.0, (float) $monthlyCloseTransaction->lapse);
        $this->assertSame(3.0, (float) $monthlyNextYearTransaction->credit);
        $this->assertSame(3.0, (float) $monthlyNextYearTransaction->balance);
        $this->assertSame(2, DB::table('leave_lapses')->count());
        $this->assertSame(10.0, (float) DB::table('leave_lapses')->sum('lapse_count'));
        $this->assertSame(4, DB::table('transactions2027')->count());
        $this->assertSame(2, DB::table('transactions2028')->count());
    }
}
