<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\ManualAttendanceApplication;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantManualAttendanceApplicationColumnsMigrationTest extends TestCase
{
    public function test_migration_removes_organization_columns_and_model_relations(): void
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
        Schema::create('manual_attendance_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('location_id')->nullable();
            $table->foreignId('company_id')->nullable();
        });

        $migration = require base_path('database/migrations/tenant/2026_10_24_000001_remove_location_company_from_manual_attendance_applications_table.php');
        $migration->up();

        $this->assertFalse(Schema::hasColumn('manual_attendance_applications', 'location_id'));
        $this->assertFalse(Schema::hasColumn('manual_attendance_applications', 'company_id'));

        $application = new ManualAttendanceApplication();
        $this->assertNotContains('location_id', $application->getFillable());
        $this->assertNotContains('company_id', $application->getFillable());
        $this->assertArrayNotHasKey('location_id', $application->getCasts());
        $this->assertArrayNotHasKey('company_id', $application->getCasts());
        $this->assertFalse(method_exists($application, 'location'));
        $this->assertFalse(method_exists($application, 'company'));

        $migration->down();

        $this->assertTrue(Schema::hasColumn('manual_attendance_applications', 'location_id'));
        $this->assertTrue(Schema::hasColumn('manual_attendance_applications', 'company_id'));
    }
}
