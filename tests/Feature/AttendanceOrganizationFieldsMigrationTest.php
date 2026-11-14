<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceOrganizationFieldsMigrationTest extends TestCase
{
    private const TABLES = [
        'attendances2025',
        'attendances2026',
        'in_out_musters2025',
        'in_out_musters2026',
    ];

    private const COLUMNS = [
        'location_id',
        'company_id',
        'department_id',
        'sub_department_id',
        'category_id',
        'sub_category_id',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_merge(self::TABLES, ['other_table', 'locations', 'companies']) as $tableName) {
            Schema::dropIfExists($tableName);
        }

        Schema::create('locations', function ($table): void {
            $table->id();
        });
        Schema::create('companies', function ($table): void {
            $table->id();
        });

        foreach (array_merge(self::TABLES, ['other_table']) as $tableName) {
            Schema::create($tableName, function ($table): void {
                $table->id();
                $table->foreignId('location_id')->nullable()->constrained('locations');
                $table->foreignId('company_id')->nullable()->constrained('companies');
                $table->json('department_id')->nullable();
                $table->json('sub_department_id')->nullable();
                $table->json('category_id')->nullable();
                $table->json('sub_category_id')->nullable();
            });
        }
    }

    protected function tearDown(): void
    {
        foreach (array_merge(self::TABLES, ['other_table', 'locations', 'companies']) as $tableName) {
            Schema::dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_migration_removes_fields_from_yearly_tables_and_restores_empty_columns_on_rollback(): void
    {
        $migration = require database_path(
            'migrations/tenant/2026_10_29_000001_remove_organization_fields_from_attendance_tables.php',
        );

        $migration->up();

        foreach (self::TABLES as $tableName) {
            foreach (self::COLUMNS as $column) {
                $this->assertFalse(Schema::hasColumn($tableName, $column));
            }
        }
        $this->assertTrue(Schema::hasColumn('other_table', 'location_id'));

        $migration->down();

        foreach (self::TABLES as $tableName) {
            foreach (self::COLUMNS as $column) {
                $this->assertTrue(Schema::hasColumn($tableName, $column));
            }
        }
    }
}
