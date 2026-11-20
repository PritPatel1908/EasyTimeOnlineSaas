<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\CustomReport;
use App\Support\CustomReportDateRange;
use Database\Seeders\Tenant\MonthlyPerformanceReportSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonthlyPerformanceReportSeederTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.monthly_report_seeder_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'monthly_report_seeder_test',
        ]);
        DB::purge('monthly_report_seeder_test');

        Schema::create('custom_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('report_name');
            $table->date('from_date');
            $table->date('to_date');
            $table->string('date_range_preset');
            $table->string('report_orientation');
            $table->string('summary_orientation');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });
        Schema::create('custom_report_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('custom_report_id')->constrained('custom_reports')->cascadeOnDelete();
            $table->json('custom_report_fields');
            $table->json('custom_report_filter');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('monthly_report_seeder_test');
        DB::purge('monthly_report_seeder_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_it_seeds_the_monthly_performance_report_once_per_tenant(): void
    {
        app(MonthlyPerformanceReportSeeder::class)->run();
        app(MonthlyPerformanceReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Monthly Performance Report')
            ->firstOrFail();
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;

        $this->assertSame('this_month', $report->date_range_preset);
        $this->assertSame($dateRange['from']->toDateString(), $report->from_date->toDateString());
        $this->assertSame($dateRange['to']->toDateString(), $report->to_date->toDateString());
        $this->assertSame('vertical', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame(1, $report->status);
        $this->assertSame([
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'date',
            'shift_name',
            'attendance_in_time',
            'attendance_out_time',
            'late_minutes',
            'early_minutes',
            'working_hrs',
            'overtime_minutes',
            'approved_overtime_minutes',
            'attendance_status',
        ], array_column($fields, 'field_name'));
        $this->assertSame('h:i A', $fields[9]['date_time_format']);
        $this->assertSame('hh_mm', $fields[11]['minute_format']);
        $this->assertTrue($fields[11]['use_in_summary']);
        $this->assertTrue($fields[16]['use_in_summary']);
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }
}
