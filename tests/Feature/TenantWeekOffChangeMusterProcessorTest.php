<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Tenant\ApplyWeekOffChangeApplication;
use App\Jobs\Tenant\CreateWeekOffMuster;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Models\Tenant\WeekOffChange;
use App\Models\Tenant\WeekOffChangeApplication;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantWeekOffChangeMusterProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('financial_years', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_started')->default(false);
            $table->date('end_date')->nullable();
            $table->softDeletes();
        });
        Schema::create('week_off_changes', function (Blueprint $table): void {
            $table->id();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->boolean('is_forever')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_change_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_change_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('week_off_change_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_change_id');
            $table->unsignedTinyInteger('week_days');
            $table->unsignedTinyInteger('wo_type')->nullable();
            $table->boolean('first_week')->default(false);
            $table->boolean('second_week')->default(false);
            $table->boolean('third_week')->default(false);
            $table->boolean('fourth_week')->default(false);
            $table->boolean('fifth_week')->default(false);
            $table->timestamps();
        });
        Schema::create('week_off_musters', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->nullableMorphs('weekoffable');
            $table->boolean('is_locked')->default(false);
            $table->tinyInteger('wo_type')->nullable();
            $table->timestamps();
        });
        Schema::create('week_off_change_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('reason')->nullable();
            $table->date('from_date')->nullable();
            $table->boolean('is_forever')->default(false);
            $table->date('to_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('week_off_change_application_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_change_application_id');
            $table->unsignedTinyInteger('week_days');
            $table->unsignedTinyInteger('wo_type')->nullable();
            $table->boolean('first_week')->default(false);
            $table->boolean('second_week')->default(false);
            $table->boolean('third_week')->default(false);
            $table->boolean('fourth_week')->default(false);
            $table->boolean('fifth_week')->default(false);
            $table->timestamps();
        });
        Schema::create('week_off_change_application_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('week_off_change_application_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        DB::table('financial_years')->insert([
            'is_started' => true,
            'end_date' => '2026-10-31',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ([
            'approval_statuses',
            'week_off_musters',
            'week_off_change_application_users',
            'week_off_change_application_details',
            'week_off_change_applications',
            'week_off_change_details',
            'week_off_change_users',
            'week_off_changes',
            'financial_years',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_job_creates_missing_musters_and_reapplies_later_changes_without_touching_locked_rows(): void
    {
        $initialChange = $this->createChange(
            id: 1,
            fromDate: '2026-10-01',
            toDate: null,
            forever: true,
            userId: 2,
            weekday: 6,
            type: 1
        );
        $laterChange = $this->createChange(
            id: 2,
            fromDate: '2026-10-10',
            toDate: '2026-10-12',
            forever: false,
            userId: 2,
            weekday: 0,
            type: 2
        );
        DB::table('week_off_change_details')->insert([
            'week_off_change_id' => $initialChange->id,
            'week_days' => 1,
            'wo_type' => 3,
            'first_week' => true,
            'second_week' => true,
            'third_week' => true,
            'fourth_week' => true,
            'fifth_week' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('week_off_change_details')->insert([
            'week_off_change_id' => $initialChange->id,
            'week_days' => 0,
            'wo_type' => 3,
            'first_week' => true,
            'second_week' => true,
            'third_week' => true,
            'fourth_week' => true,
            'fifth_week' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('week_off_musters')->insert([
            'date' => '2026-10-17',
            'user_id' => 2,
            'weekoffable_type' => WeekOffChange::class,
            'weekoffable_id' => $initialChange->id,
            'is_locked' => true,
            'wo_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new CreateWeekOffMuster($initialChange))->handle();

        DB::table('week_off_change_details')
            ->where('week_off_change_id', $initialChange->id)
            ->update(['wo_type' => 3]);

        (new CreateWeekOffMuster($initialChange->fresh()))->handle();

        $musters = DB::table('week_off_musters')->get()
            ->keyBy(static fn (object $muster): string => substr((string) $muster->date, 0, 10));

        $this->assertCount(31, $musters);
        $this->assertSame(0, (int) $musters['2026-10-10']->wo_type);
        $this->assertSame($laterChange->id, (int) $musters['2026-10-10']->weekoffable_id);
        $this->assertSame(2, (int) $musters['2026-10-11']->wo_type);
        $this->assertSame($laterChange->id, (int) $musters['2026-10-11']->weekoffable_id);
        $this->assertSame(0, (int) $musters['2026-10-12']->wo_type);
        $this->assertSame($laterChange->id, (int) $musters['2026-10-12']->weekoffable_id);
        $this->assertSame(3, (int) $musters['2026-10-05']->wo_type);
        $this->assertSame($initialChange->id, (int) $musters['2026-10-05']->weekoffable_id);
        $this->assertSame(3, (int) $musters['2026-10-18']->wo_type);
        $this->assertSame($initialChange->id, (int) $musters['2026-10-18']->weekoffable_id);
        $this->assertSame(1, (int) $musters['2026-10-17']->wo_type);
        $this->assertTrue((bool) $musters['2026-10-17']->is_locked);
        $this->assertSame(3, (int) $musters['2026-10-24']->wo_type);
        $this->assertSame($initialChange->id, (int) $musters['2026-10-24']->weekoffable_id);
    }

    public function test_job_is_queued_for_each_week_off_change_record(): void
    {
        $firstChange = $this->createChange(
            id: 1,
            fromDate: '2026-10-01',
            toDate: null,
            forever: true,
            userId: 1,
            weekday: 0,
            type: 1
        );
        $secondChange = $this->createChange(
            id: 2,
            fromDate: '2026-10-02',
            toDate: null,
            forever: true,
            userId: 2,
            weekday: 1,
            type: 1
        );

        Queue::fake();

        CreateWeekOffMuster::dispatch($firstChange);
        CreateWeekOffMuster::dispatch($secondChange);

        Queue::assertPushedTimes(CreateWeekOffMuster::class, 2);
    }

    public function test_final_approval_queues_application_muster_processing_not_a_change_record(): void
    {
        DB::table('week_off_change_applications')->insert([
            'id' => 11,
            'from_date' => '2026-10-10',
            'is_forever' => false,
            'to_date' => '2026-10-12',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Schema::create('approval_statuses', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->string('approval_status');
        });
        DB::table('approval_statuses')->insert([
            'approvable_id' => 11,
            'approvable_type' => WeekOffChangeApplication::class,
            'approval_status' => 'approved',
        ]);

        Bus::fake();
        (new FinalizeApprovedApplication(WeekOffChangeApplication::class, 11))->handle();

        Bus::assertDispatched(ApplyWeekOffChangeApplication::class, static fn (ApplyWeekOffChangeApplication $job): bool => (
            $job->applicationId === 11
        ));
        Bus::assertNotDispatched(CreateWeekOffMuster::class);
    }

    public function test_approved_application_updates_musters_without_creating_week_off_change_records(): void
    {
        DB::table('week_off_change_applications')->insert([
            'id' => 10,
            'reason' => 'Approved schedule update',
            'from_date' => '2026-10-10',
            'is_forever' => false,
            'to_date' => '2026-10-12',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('week_off_change_application_users')->insert([
            'week_off_change_application_id' => 10,
            'user_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('week_off_change_application_details')->insert([
            'week_off_change_application_id' => 10,
            'week_days' => 6,
            'wo_type' => 1,
            'second_week' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('week_off_musters')->insert([
            [
                'date' => '2026-10-10',
                'user_id' => 2,
                'weekoffable_type' => WeekOffChange::class,
                'weekoffable_id' => 99,
                'is_locked' => false,
                'wo_type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => '2026-10-11',
                'user_id' => 2,
                'weekoffable_type' => WeekOffChange::class,
                'weekoffable_id' => 99,
                'is_locked' => true,
                'wo_type' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $job = new ApplyWeekOffChangeApplication(10);
        $job->handle();
        $job->handle();

        $this->assertSame(0, DB::table('week_off_changes')->count());
        $this->assertSame(3, DB::table('week_off_musters')
            ->where('user_id', 2)
            ->whereBetween('date', ['2026-10-10', '2026-10-12'])
            ->count());
        $this->assertDatabaseHas('week_off_musters', [
            'date' => '2026-10-10',
            'user_id' => 2,
            'weekoffable_type' => WeekOffChangeApplication::class,
            'weekoffable_id' => 10,
            'is_locked' => false,
            'wo_type' => 1,
        ]);
        $this->assertDatabaseHas('week_off_musters', [
            'date' => '2026-10-11',
            'user_id' => 2,
            'weekoffable_type' => WeekOffChange::class,
            'weekoffable_id' => 99,
            'is_locked' => true,
            'wo_type' => 3,
        ]);
        $this->assertDatabaseHas('week_off_musters', [
            'date' => '2026-10-12',
            'user_id' => 2,
            'weekoffable_type' => WeekOffChangeApplication::class,
            'weekoffable_id' => 10,
            'is_locked' => false,
            'wo_type' => 0,
        ]);
    }

    private function createChange(
        int $id,
        string $fromDate,
        ?string $toDate,
        bool $forever,
        int $userId,
        int $weekday,
        int $type
    ): WeekOffChange {
        DB::table('week_off_changes')->insert([
            'id' => $id,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'is_forever' => $forever,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('week_off_change_users')->insert([
            'week_off_change_id' => $id,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('week_off_change_details')->insert([
            'week_off_change_id' => $id,
            'week_days' => $weekday,
            'wo_type' => $type,
            'first_week' => true,
            'second_week' => true,
            'third_week' => true,
            'fourth_week' => true,
            'fifth_week' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return WeekOffChange::query()->findOrFail($id);
    }
}
