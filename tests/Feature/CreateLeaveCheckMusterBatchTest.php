<?php

namespace Tests\Feature;

use App\Jobs\Tenant\CreateLeaveCheckMusterBatch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateLeaveCheckMusterBatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->date('join_date')->nullable();
            $table->date('left_date')->nullable();
            $table->boolean('is_inactive')->default(false);
            $table->unsignedBigInteger('grade_wise_leave_id')->nullable();
            $table->date('last_check_date')->nullable();
            $table->unsignedBigInteger('last_check_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('financial_years', function (Blueprint $table): void {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_started')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('grade_wise_leaves', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_monthly')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grade_wise_leave_id');
            $table->string('leave_credit_type')->nullable();
            $table->timestamps();
        });
        Schema::create('grade_wise_yearly_leave_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grade_wise_leave_id');
            $table->timestamps();
        });
        Schema::create('general_configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('leave_check_musters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('check_date')->nullable();
            $table->boolean('is_checked')->default(false);
            $table->timestamps();
        });
    }

    public function test_started_financial_year_gets_a_yearly_muster_for_each_eligible_yearly_leave_user(): void
    {
        DB::table('financial_years')->insert([
            ['id' => 1, 'start_date' => '2026-04-01', 'end_date' => '2027-03-31'],
            ['id' => 2, 'start_date' => '2027-04-01', 'end_date' => '2028-03-31'],
        ]);
        DB::table('grade_wise_leaves')->insert([
            ['id' => 1, 'name' => 'Yearly leave', 'is_monthly' => true],
            ['id' => 2, 'name' => 'Monthly leave', 'is_monthly' => true],
        ]);
        DB::table('grade_wise_monthly_leave_details')->insert([
            ['grade_wise_leave_id' => 1, 'leave_credit_type' => 'yearly'],
            ['grade_wise_leave_id' => 2, 'leave_credit_type' => 'monthly'],
        ]);
        DB::table('general_configurations')->insert([
            'key' => 'yearly_leave_check_date',
            'value' => '4',
        ]);
        DB::table('users')->insert([
            'id' => 2,
            'name' => 'Yearly User',
            'code' => 'Y1',
            'join_date' => '2025-01-01',
            'grade_wise_leave_id' => 1,
            'last_check_date' => '2026-04-04',
            'last_check_id' => 1,
        ]);
        DB::table('users')->insert([
            'id' => 3,
            'name' => 'Monthly User',
            'code' => 'M1',
            'join_date' => '2025-01-01',
            'grade_wise_leave_id' => 2,
        ]);
        DB::table('leave_check_musters')->insert([
            'user_id' => 2,
            'check_date' => '2026-04-04',
            'is_checked' => true,
        ]);

        (new CreateLeaveCheckMusterBatch(2))->handle();

        $this->assertTrue(DB::table('leave_check_musters')
            ->where('user_id', 2)
            ->whereDate('check_date', '2027-04-04')
            ->where('is_checked', false)
            ->exists());
        $this->assertFalse(DB::table('leave_check_musters')
            ->where('user_id', 3)
            ->whereDate('check_date', '2027-04-04')
            ->exists());

        (new CreateLeaveCheckMusterBatch(2))->handle();

        $this->assertSame(
            1,
            DB::table('leave_check_musters')
                ->where('user_id', 2)
                ->whereDate('check_date', '2027-04-04')
                ->count(),
        );
    }
}
