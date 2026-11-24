<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\CustomReport;
use App\Support\CustomReportDateRange;
use Database\Seeders\Tenant\AttendanceSpecificStatusReportSeeder;
use Database\Seeders\Tenant\EarlyGoingReportSeeder;
use Database\Seeders\Tenant\MachineInOutStatusReportSeeder;
use Database\Seeders\Tenant\MonthlyFixedWorkHoursReportSeeder;
use Database\Seeders\Tenant\MonthlyLateComingReportSeeder;
use Database\Seeders\Tenant\MonthlyManualPunchReportSeeder;
use Database\Seeders\Tenant\MonthlyMusterReportSeeder;
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
        Schema::create('status_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
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

    public function test_it_seeds_the_monthly_late_coming_report_once_with_late_minutes_summary(): void
    {
        app(MonthlyLateComingReportSeeder::class)->run();
        app(MonthlyLateComingReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Monthly Late Coming Report')
            ->firstOrFail();
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;
        $lateMinutesField = $fields[array_search('late_minutes', array_column($fields, 'field_name'), true)];

        $this->assertSame($dateRange['from']->toDateString(), $report->from_date->toDateString());
        $this->assertSame($dateRange['to']->toDateString(), $report->to_date->toDateString());
        $this->assertSame('this_month', $report->date_range_preset);
        $this->assertSame('horizontal', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame([
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'date',
            'late_minutes',
        ], array_column($fields, 'field_name'));
        $this->assertSame('hh_mm', $lateMinutesField['minute_format']);
        $this->assertTrue($lateMinutesField['use_in_summary']);
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }

    public function test_it_seeds_the_monthly_fixed_work_hours_report_once_with_fixed_hours_enabled(): void
    {
        app(MonthlyFixedWorkHoursReportSeeder::class)->run();
        app(MonthlyFixedWorkHoursReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Monthly Fixed Work Hours Report')
            ->firstOrFail();
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;
        $workingHoursField = $fields[array_search('working_hrs', array_column($fields, 'field_name'), true)];

        $this->assertSame('this_month', $report->date_range_preset);
        $this->assertSame($dateRange['from']->toDateString(), $report->from_date->toDateString());
        $this->assertSame($dateRange['to']->toDateString(), $report->to_date->toDateString());
        $this->assertSame('vertical', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame('hh_mm', $workingHoursField['minute_format']);
        $this->assertTrue($workingHoursField['use_in_summary']);
        $this->assertTrue($workingHoursField['show_fixed_hrs']);
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }

    public function test_it_seeds_the_monthly_manual_punch_report_once(): void
    {
        app(MonthlyManualPunchReportSeeder::class)->run();
        app(MonthlyManualPunchReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Monthly Manual Punch Report')
            ->firstOrFail();
        $dateRange = app(CustomReportDateRange::class)->resolve('last_month');
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);

        $this->assertSame($dateRange['from']->toDateString(), $report->from_date->toDateString());
        $this->assertSame($dateRange['to']->toDateString(), $report->to_date->toDateString());
        $this->assertSame('last_month', $report->date_range_preset);
        $this->assertSame('vertical', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame([
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'manual_punch',
        ], array_column($detail->custom_report_fields, 'field_name'));
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }

    public function test_it_seeds_the_monthly_muster_report_with_horizontal_status_and_days_summary(): void
    {
        app(MonthlyMusterReportSeeder::class)->run();
        app(MonthlyMusterReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Monthly Muster Report')
            ->firstOrFail();
        $dateRange = app(CustomReportDateRange::class)->resolve('last_month');
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;
        $attendanceStatusField = $fields[array_search('attendance_status', array_column($fields, 'field_name'), true)];
        $selectedPeriodDaysField = $fields[array_search('month_total_days', array_column($fields, 'field_name'), true)];

        $this->assertSame('last_month', $report->date_range_preset);
        $this->assertSame($dateRange['from']->toDateString(), $report->from_date->toDateString());
        $this->assertSame($dateRange['to']->toDateString(), $report->to_date->toDateString());
        $this->assertSame('horizontal', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame([
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'date',
            'attendance_status',
            'month_total_days',
        ], array_column($fields, 'field_name'));
        $this->assertTrue($attendanceStatusField['use_in_summary']);
        $this->assertTrue($selectedPeriodDaysField['use_in_summary']);
        $this->assertTrue($selectedPeriodDaysField['use_only_in_summary']);
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }

    public function test_it_seeds_the_attendance_specific_status_report_with_selected_status_codes_once(): void
    {
        $statusCodes = ['HLA', 'AHL', 'WOP', 'AWO', 'PA', 'AP', 'AA'];
        foreach ($statusCodes as $index => $code) {
            DB::table('status_masters')->insert([
                'id' => 10 + $index,
                'code' => $code,
            ]);
        }

        app(AttendanceSpecificStatusReportSeeder::class)->run();
        app(AttendanceSpecificStatusReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Attendance Specific Status Report')
            ->firstOrFail();
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;
        $statusField = $fields[array_search('attendance_specific_status', array_column($fields, 'field_name'), true)];

        $this->assertSame('this_month', $report->date_range_preset);
        $this->assertSame('vertical', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame(
            ['emp_code', 'emp_card', 'name', 'company_name', 'department_name', 'designation_name', 'category_name', 'date', 'attendance_specific_status'],
            array_column($fields, 'field_name'),
        );
        $this->assertSame([16, 15, 14, 13, 12, 11, 10], $statusField['status_master_ids']);
        $this->assertTrue($statusField['use_in_summary']);
        $this->assertFalse($statusField['half_status_count_as_half']);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }

    public function test_it_seeds_machine_in_out_report_without_machine_sequence_configuration(): void
    {
        app(MachineInOutStatusReportSeeder::class)->run();
        app(MachineInOutStatusReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Machine In/Out Status Report')
            ->firstOrFail();
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;
        $machineInOutField = $fields[array_search('machine_in_out', array_column($fields, 'field_name'), true)];

        $this->assertSame('this_month', $report->date_range_preset);
        $this->assertSame('horizontal', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame([
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'date',
            'machine_in_out',
        ], array_column($fields, 'field_name'));
        $this->assertSame([], $machineInOutField['machine_in_out']);
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }

    public function test_it_seeds_the_early_going_report_once_with_early_minutes_summary(): void
    {
        app(EarlyGoingReportSeeder::class)->run();
        app(EarlyGoingReportSeeder::class)->run();

        $report = CustomReport::query()
            ->with('custom_report_details')
            ->where('report_name', 'Early Going Report')
            ->firstOrFail();
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $detail = $report->custom_report_details->first();
        $this->assertNotNull($detail);
        $fields = $detail->custom_report_fields;
        $earlyMinutesField = $fields[array_search('early_minutes', array_column($fields, 'field_name'), true)];

        $this->assertSame($dateRange['from']->toDateString(), $report->from_date->toDateString());
        $this->assertSame($dateRange['to']->toDateString(), $report->to_date->toDateString());
        $this->assertSame('this_month', $report->date_range_preset);
        $this->assertSame('horizontal', $report->report_orientation);
        $this->assertSame('horizontal', $report->summary_orientation);
        $this->assertSame([
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'date',
            'early_minutes',
        ], array_column($fields, 'field_name'));
        $this->assertSame('hh_mm', $earlyMinutesField['minute_format']);
        $this->assertTrue($earlyMinutesField['use_in_summary']);
        $this->assertSame([], $detail->custom_report_filter);
        $this->assertSame(1, DB::table('custom_reports')->count());
        $this->assertSame(1, DB::table('custom_report_details')->count());
    }
}
