<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantManualAttendanceApplicationShiftColumnsMigrationTest extends TestCase
{
    public function test_migration_removes_shift_columns_and_can_restore_them(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('manual_attendance_applications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shift_rotation_id')->nullable();
            $table->string('shift_type')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
        });

        $migration = require base_path('database/migrations/tenant/2026_10_24_000002_remove_shift_fields_from_manual_attendance_applications_table.php');
        $migration->up();

        $this->assertFalse(Schema::hasColumn('manual_attendance_applications', 'shift_rotation_id'));
        $this->assertFalse(Schema::hasColumn('manual_attendance_applications', 'shift_type'));
        $this->assertFalse(Schema::hasColumn('manual_attendance_applications', 'shift_id'));

        $migration->down();

        $this->assertTrue(Schema::hasColumn('manual_attendance_applications', 'shift_rotation_id'));
        $this->assertTrue(Schema::hasColumn('manual_attendance_applications', 'shift_type'));
        $this->assertTrue(Schema::hasColumn('manual_attendance_applications', 'shift_id'));

        Schema::dropIfExists('manual_attendance_applications');
        DB::purge('sqlite');
    }
}
