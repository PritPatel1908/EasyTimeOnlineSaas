<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomReportDateRangeMigrationTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.custom_report_date_range_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'custom_report_date_range_test',
        ]);
        DB::purge('custom_report_date_range_test');

        Schema::create('custom_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('report_name');
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedTinyInteger('status')->default(1);
            $table->string('report_orientation')->default('vertical');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_date_range_test');
        DB::purge('custom_report_date_range_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_preset_migration_allows_empty_fixed_dates_and_rolls_back_safely(): void
    {
        $migration = require base_path('database/migrations/tenant/2026_11_20_120000_add_date_range_preset_to_custom_reports_table.php');
        $migration->up();

        DB::table('custom_reports')->insert([
            'report_name' => 'Dynamic report',
            'from_date' => null,
            'to_date' => null,
            'status' => 1,
            'report_orientation' => 'vertical',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('custom', DB::table('custom_reports')->value('date_range_preset'));

        $migration->down();

        $report = DB::table('custom_reports')->first();
        $this->assertNotNull($report->from_date);
        $this->assertNotNull($report->to_date);
        $this->assertFalse(Schema::hasColumn('custom_reports', 'date_range_preset'));
    }

    public function test_summary_orientation_migration_preserves_existing_report_orientation(): void
    {
        DB::table('custom_reports')->insert([
            [
                'report_name' => 'Vertical report',
                'from_date' => '2026-11-01',
                'to_date' => '2026-11-02',
                'status' => 1,
                'report_orientation' => 'vertical',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'report_name' => 'Horizontal report',
                'from_date' => '2026-11-01',
                'to_date' => '2026-11-02',
                'status' => 1,
                'report_orientation' => 'horizontal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $migration = require base_path('database/migrations/tenant/2026_11_20_152000_add_summary_orientation_to_custom_reports_table.php');
        $migration->up();

        $this->assertSame(
            ['vertical', 'horizontal'],
            DB::table('custom_reports')->orderBy('id')->pluck('summary_orientation')->all(),
        );

        $migration->down();

        $this->assertFalse(Schema::hasColumn('custom_reports', 'summary_orientation'));
    }
}
