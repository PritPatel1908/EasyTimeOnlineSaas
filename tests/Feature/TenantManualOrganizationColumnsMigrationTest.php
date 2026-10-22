<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantManualOrganizationColumnsMigrationTest extends TestCase
{
    public function test_migration_removes_organization_columns_without_foreign_keys_and_preserves_legacy_ids(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
        });
        DB::table('locations')->insert(['id' => 3]);
        DB::table('companies')->insert(['id' => 4]);

        Schema::create('manual_punches', function (Blueprint $table): void {
            $table->id();
            $table->time('punch_time');
            $table->foreignId('location_id')->nullable();
            $table->foreignId('company_id')->nullable();
        });
        Schema::create('manual_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('location_id')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->json('location_ids')->nullable();
            $table->json('company_ids')->nullable();
        });
        Schema::create('shift_changes', function (Blueprint $table): void {
            $table->id();
            $table->json('location_id')->nullable();
            $table->json('company_id')->nullable();
        });
        DB::table('manual_attendances')->insert([
            'id' => 1,
            'location_id' => 3,
            'company_id' => 4,
            'location_ids' => null,
            'company_ids' => null,
        ]);

        $migration = require base_path('database/migrations/tenant/2026_10_22_000300_remove_location_company_from_manual_tables.php');
        $migration->up();

        foreach (['manual_punches', 'manual_attendances', 'shift_changes'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'location_id'));
            $this->assertFalse(Schema::hasColumn($table, 'company_id'));
        }

        $attendance = DB::table('manual_attendances')->find(1);
        $this->assertSame([3], json_decode($attendance->location_ids, true));
        $this->assertSame([4], json_decode($attendance->company_ids, true));

        $migration->down();

        foreach (['manual_punches', 'manual_attendances', 'shift_changes'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'location_id'));
            $this->assertTrue(Schema::hasColumn($table, 'company_id'));
        }
    }
}
