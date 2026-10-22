<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ShiftChangeMusterProcessor;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftMuster;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantShiftChangeMusterProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('shift_status')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
        });
        Schema::create('financial_years', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_started')->default(false);
            $table->date('end_date')->nullable();
            $table->softDeletes();
        });
        Schema::create('shift_changes', function (Blueprint $table): void {
            $table->id();
            $table->string('shift_type');
            $table->unsignedBigInteger('shift_rotation_id')->nullable();
            $table->date('from_date');
            $table->boolean('is_forever')->default(false);
            $table->date('to_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('shift_change_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shift_change_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('shift_change_shifts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shift_change_id');
            $table->unsignedBigInteger('shift_id');
            $table->timestamps();
        });
        Schema::create('shift_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->nullableMorphs('shiftchangeable');
            $table->json('shift')->nullable();
            $table->unsignedBigInteger('calculated_shift')->nullable();
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('event')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 2, 'name' => 'Test Employee']);
        DB::table('shifts')->insert([
            ['id' => 1, 'name' => 'Original Shift'],
            ['id' => 2, 'name' => 'Updated Forever Shift'],
            ['id' => 3, 'name' => 'Date Range Shift'],
        ]);
        DB::table('financial_years')->insert([
            'is_started' => true,
            'end_date' => '2026-10-31',
        ]);
        DB::table('shift_changes')->insert([
            'id' => 1,
            'shift_type' => 'fixed',
            'from_date' => '2026-10-01',
            'is_forever' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('shift_changes')->insert([
            'id' => 2,
            'shift_type' => 'fixed',
            'from_date' => '2026-10-10',
            'to_date' => '2026-10-12',
            'is_forever' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('shift_change_users')->insert([
            ['shift_change_id' => 1, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['shift_change_id' => 2, 'user_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('shift_change_shifts')->insert([
            ['shift_change_id' => 1, 'shift_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['shift_change_id' => 2, 'shift_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    protected function tearDown(): void
    {
        foreach ([
            'shift_musters',
            'activity_log',
            'shift_change_shifts',
            'shift_change_users',
            'shift_changes',
            'financial_years',
            'shifts',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_editing_forever_shift_change_reapplies_later_date_range_changes(): void
    {
        $foreverChange = ShiftChange::query()->findOrFail(1);
        ShiftChangeMusterProcessor::process($foreverChange);

        DB::table('shift_change_shifts')
            ->where('shift_change_id', 1)
            ->update(['shift_id' => 2]);

        ShiftChangeMusterProcessor::process($foreverChange->fresh());

        $musters = ShiftMuster::query()
            ->where('user_id', 2)
            ->orderBy('date')
            ->get()
            ->keyBy(static fn (ShiftMuster $muster): string => $muster->date->toDateString());

        $this->assertSame([2], $musters['2026-10-09']->shift);
        $this->assertSame([3], $musters['2026-10-10']->shift);
        $this->assertSame([3], $musters['2026-10-12']->shift);
        $this->assertSame([2], $musters['2026-10-13']->shift);
    }
}
