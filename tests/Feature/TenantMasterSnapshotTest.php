<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\Location;
use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\User;
use App\Services\Tenant\MasterSnapshotService;
use Database\Seeders\Tenant\MasterSnapshotRecoverySeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantMasterSnapshotTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.master_snapshot_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'master_snapshot_test',
        ]);
        DB::purge('master_snapshot_test');

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('user_type')->nullable();
            $table->json('location_id')->nullable();
            $table->date('join_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('master_type');
            $table->unsignedBigInteger('master_id');
            $table->string('type');
            $table->json('value');
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('for_all')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('master_snapshot_test');
        DB::purge('master_snapshot_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_master_name_and_code_updates_close_the_previous_snapshot(): void
    {
        MasterSnapshot::query()->create([
            'master_type' => Location::class,
            'master_id' => 10,
            'type' => 'both',
            'value' => ['name' => 'Old', 'code' => 'OLD'],
            'from_date' => today()->subDay(),
            'for_all' => true,
        ]);

        $location = new Location;
        $location->setRawAttributes(['id' => 10, 'name' => 'Old', 'code' => 'OLD'], true);
        $location->setAttribute('name', 'New');
        $location->setAttribute('code', 'NEW');
        $location->syncChanges();

        app(MasterSnapshotService::class)->captureUpdated($location);

        $snapshots = MasterSnapshot::query()->orderBy('id')->get();
        $this->assertCount(2, $snapshots);
        $this->assertSame(today()->toDateString(), $snapshots[0]->to_date->toDateString());
        $this->assertSame('both', $snapshots[1]->type);
        $this->assertSame(['name' => 'New', 'code' => 'NEW'], $snapshots[1]->value);
        $this->assertNull($snapshots[1]->to_date);
        $this->assertNull($snapshots[1]->user_id);
        $this->assertTrue($snapshots[1]->for_all);
    }

    public function test_employee_location_change_closes_old_assignment_and_snapshots_new_location(): void
    {
        DB::table('locations')->insert([
            ['id' => 1, 'name' => 'Old Location', 'code' => 'OLD', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'New Location', 'code' => 'NEW', 'created_at' => now(), 'updated_at' => now()],
        ]);
        MasterSnapshot::query()->create([
            'master_type' => Location::class,
            'master_id' => 1,
            'type' => 'both',
            'value' => ['name' => 'Old Location', 'code' => 'OLD'],
            'from_date' => today()->subDay(),
            'user_id' => 5,
            'for_all' => false,
        ]);

        $employee = new User;
        $employee->setRawAttributes([
            'id' => 5,
            'user_type' => 'employee',
            'location_id' => '[1]',
        ], true);
        $employee->setAttribute('location_id', [2]);
        $employee->syncChanges();

        app(MasterSnapshotService::class)->captureUpdated($employee);

        $snapshots = MasterSnapshot::query()->orderBy('id')->get();
        $this->assertCount(2, $snapshots);
        $this->assertSame(today()->toDateString(), $snapshots[0]->to_date->toDateString());
        $this->assertSame(Location::class, $snapshots[1]->master_type);
        $this->assertSame(2, $snapshots[1]->master_id);
        $this->assertSame('both', $snapshots[1]->type);
        $this->assertSame(['name' => 'New Location', 'code' => 'NEW'], $snapshots[1]->value);
        $this->assertSame(5, $snapshots[1]->user_id);
        $this->assertFalse($snapshots[1]->for_all);
        $this->assertNull($snapshots[1]->to_date);
    }

    public function test_new_employee_assignment_is_snapshotted(): void
    {
        DB::table('locations')->insert([
            'id' => 4,
            'name' => 'Employee Location',
            'code' => 'EMP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 8,
            'user_type' => 'employee',
            'location_id' => '[4]',
            'join_date' => '2020-02-15',
        ]);

        app(MasterSnapshotService::class)->captureCreated($employee);
        app(MasterSnapshotService::class)->captureCreated($employee);

        $snapshot = MasterSnapshot::query()->sole();
        $this->assertSame(Location::class, $snapshot->master_type);
        $this->assertSame(4, $snapshot->master_id);
        $this->assertSame(['name' => 'Employee Location', 'code' => 'EMP'], $snapshot->value);
        $this->assertSame(8, $snapshot->user_id);
        $this->assertSame('2020-02-15', $snapshot->from_date->toDateString());
        $this->assertFalse($snapshot->for_all);
        $this->assertNull($snapshot->to_date);
    }

    public function test_migration_seeds_current_master_and_employee_values_as_baseline(): void
    {
        DB::table('locations')->insert([
            'id' => 3,
            'name' => 'Current Location',
            'code' => 'CUR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id' => 7,
            'user_type' => 'employee',
            'location_id' => '[3]',
            'join_date' => '2018-03-14',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id' => 8,
            'user_type' => 'employee',
            'location_id' => '[3]',
            'join_date' => '2020-07-22',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::dropIfExists('master_snapshots');
        $migration = require database_path('migrations/tenant/2026_11_14_000000_create_master_snapshots_table.php');
        $migration->up();

        $snapshots = MasterSnapshot::query()->orderByDesc('for_all')->get();
        $this->assertCount(3, $snapshots);
        $this->assertSame(today()->toDateString(), $snapshots[0]->from_date->toDateString());
        $this->assertSame(Location::class, $snapshots[0]->master_type);
        $this->assertSame(['name' => 'Current Location', 'code' => 'CUR'], $snapshots[0]->value);
        $employeeSnapshots = $snapshots->where('for_all', false)->keyBy('user_id');
        $this->assertSame('2018-03-14', $employeeSnapshots[7]->from_date->toDateString());
        $this->assertSame('2020-07-22', $employeeSnapshots[8]->from_date->toDateString());
    }

    public function test_recovery_seeder_recreates_missing_table_and_is_idempotent(): void
    {
        DB::table('locations')->insert([
            'id' => 12,
            'name' => 'Current Location',
            'code' => 'CUR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('users')->insert([
            'id' => 21,
            'user_type' => 'employee',
            'location_id' => '[12]',
            'join_date' => '2021-08-03',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::dropIfExists('master_snapshots');
        $seeder = new MasterSnapshotRecoverySeeder;
        $seeder->run();
        $seeder->run();

        $snapshots = MasterSnapshot::query()->orderByDesc('for_all')->get();
        $this->assertTrue(Schema::hasTable('master_snapshots'));
        $this->assertCount(2, $snapshots);
        $this->assertSame(today()->toDateString(), $snapshots[0]->from_date->toDateString());
        $this->assertSame(['name' => 'Current Location', 'code' => 'CUR'], $snapshots[0]->value);
        $this->assertSame(21, $snapshots[1]->user_id);
        $this->assertSame(today()->toDateString(), $snapshots[1]->from_date->toDateString());
    }
}
