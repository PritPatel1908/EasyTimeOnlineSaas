<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\Location;
use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\Shift;
use App\Models\Tenant\User;
use App\Support\CustomReportTableBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomReportSnapshotExportTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.custom_report_snapshot_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'custom_report_snapshot_test',
        ]);
        DB::purge('custom_report_snapshot_test');

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
        Schema::create('attendances2026', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->dateTime('shift_in_time')->nullable();
            $table->dateTime('shift_out_time')->nullable();
            $table->unsignedBigInteger('status_master_id')->nullable();
            $table->string('shift_code')->nullable();
            $table->boolean('is_half_day')->default(false);
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->boolean('is_late')->default(false);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->boolean('is_early')->default(false);
            $table->unsignedInteger('early_minutes')->default(0);
            $table->unsignedInteger('work_time')->default(0);
            $table->unsignedInteger('total_overtime_minutes')->default(0);
            $table->unsignedInteger('approved_overtime_minutes')->default(0);
            $table->decimal('c_off_days', 10, 2)->default(0);
            $table->boolean('is_coff')->default(false);
            $table->decimal('coff_credit_dayes', 10, 2)->default(0);
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->boolean('fix_work_hours')->default(false);
            $table->boolean('fix_work_hours_as_per_shift')->default(false);
            $table->string('fix_work_hours_value')->nullable();
        });
        Schema::create('status_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->softDeletes();
        });
        Schema::create('general_configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_snapshot_test');
        DB::purge('custom_report_snapshot_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_date_report_groups_employee_details_above_report_rows(): void
    {
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 7,
            'code' => 'E001',
            'location_id' => '[2]',
        ], true);

        $this->snapshot(1, ['name' => 'Old Location', 'code' => 'OLD'], '2026-01-01', '2026-01-15', 7, false);
        $this->snapshot(2, ['name' => 'New Location', 'code' => 'NEW'], '2026-01-15', null, 7, false);
        $this->snapshot(1, ['name' => 'Old Location', 'code' => 'OLD'], '2026-01-01', '2026-01-15', null, true);
        $this->snapshot(2, ['name' => 'New Location', 'code' => 'NEW'], '2026-01-15', null, null, true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date', 'date_time_format' => 'd/m/Y'],
                ['field_name' => 'location_name'],
            ],
            '2026-01-14',
            '2026-01-16',
            'vertical',
            [$employee],
        );

        $this->assertSame([], $table['headers']);
        $this->assertSame([
            ['Emp Code', 'Location Name'],
            ['E001', 'Old Location (14/01/2026), New Location (15/01/2026 to 16/01/2026)'],
            [],
            ['Date'],
            ['14/01/2026'],
            ['15/01/2026'],
            ['16/01/2026'],
        ], $table['rows']);
    }

    public function test_default_report_date_time_and_datetime_formats_use_general_configuration(): void
    {
        DB::table('general_configurations')->insert([
            ['key' => 'date_format', 'value' => 'd/m'],
            ['key' => 'time_format', 'value' => 'h:i A'],
            ['key' => 'datetime_format', 'value' => 'd-m-Y h:i A'],
        ]);
        DB::table('attendances2026')->insert([
            [
                'user_id' => 7,
                'date' => '2026-02-03',
                'in_time' => '2026-02-03 08:15:00',
                'out_time' => '2026-02-03 18:30:00',
            ],
        ]);

        $employee = new User;
        $employee->setRawAttributes([
            'id' => 7,
            'code' => 'E007',
            'last_login_at' => '2026-02-03 09:14:00',
        ], true);
        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date', 'date_time_format' => 'default'],
                ['field_name' => 'attendance_in_time', 'date_time_format' => 'default'],
                ['field_name' => 'attendance_out_time', 'date_time_format' => 'default'],
                ['field_name' => 'last_login', 'date_time_format' => 'default'],
            ],
            '2026-02-03',
            '2026-02-03',
            'vertical',
            [$employee],
        );

        $this->assertSame([
            ['Emp Code', 'Last Login'],
            ['E007', '03-02-2026 09:14 AM'],
            [],
            ['Date', 'Attendance In Time', 'Attendance Out Time'],
            ['03/02', '08:15 AM', '06:30 PM'],
        ], $table['rows']);
    }

    public function test_working_hours_can_show_category_fixed_custom_or_shift_hours(): void
    {
        DB::table('categories')->insert([
            ['id' => 1, 'fix_work_hours' => true, 'fix_work_hours_as_per_shift' => false, 'fix_work_hours_value' => '06:00'],
            ['id' => 2, 'fix_work_hours' => true, 'fix_work_hours_as_per_shift' => true, 'fix_work_hours_value' => '06:00'],
            ['id' => 3, 'fix_work_hours' => false, 'fix_work_hours_as_per_shift' => false, 'fix_work_hours_value' => '06:00'],
        ]);
        DB::table('attendances2026')->insert([
            [
                'user_id' => 71,
                'date' => '2026-11-23',
                'shift_in_time' => '2026-11-23 09:00:00',
                'shift_out_time' => '2026-11-23 17:00:00',
                'work_time' => 420,
            ],
            [
                'user_id' => 72,
                'date' => '2026-11-23',
                'shift_in_time' => '2026-11-23 09:00:00',
                'shift_out_time' => '2026-11-23 17:00:00',
                'work_time' => 600,
            ],
            [
                'user_id' => 73,
                'date' => '2026-11-23',
                'shift_in_time' => '2026-11-23 09:00:00',
                'shift_out_time' => '2026-11-23 17:00:00',
                'work_time' => 600,
            ],
        ]);

        $employees = [];
        foreach ([71 => 1, 72 => 2, 73 => 3] as $employeeId => $categoryId) {
            $employee = new User;
            $employee->setRawAttributes([
                'id' => $employeeId,
                'code' => 'E'.$employeeId,
                'category_id' => json_encode([$categoryId]),
            ], true);
            $employees[] = $employee;
        }

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date'],
                ['field_name' => 'working_hrs', 'minute_format' => 'hh_mm', 'show_fixed_hrs' => true],
            ],
            '2026-11-23',
            '2026-11-23',
            'vertical',
            $employees,
        );

        $this->assertContains(['2026-11-23', '06:00'], $table['rows']);
        $this->assertContains(['2026-11-23', '08:00'], $table['rows']);
        $this->assertContains(['2026-11-23', '10:00'], $table['rows']);
    }

    public function test_master_section_uses_fallback_before_snapshot_and_shows_value_date_ranges_when_changed(): void
    {
        DB::table('locations')->insert([
            'id' => 50,
            'name' => 'Fallback Location',
            'code' => 'FALLBACK',
        ]);
        $this->snapshot(50, ['name' => 'Snapshot Location', 'code' => 'SNAP'], '2026-12-16', null, null, true);
        $this->snapshot(50, ['name' => 'Snapshot Location', 'code' => 'SNAP'], '2026-12-16', null, 7, false);

        $employee = new User;
        $employee->setRawAttributes([
            'id' => 7,
            'code' => 'E007',
            'location_id' => '[50]',
        ], true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date'],
                ['field_name' => 'location_name'],
            ],
            '2026-12-01',
            '2026-12-31',
            'vertical',
            [$employee],
        );

        $this->assertSame([
            ['Emp Code', 'Location Name'],
            ['E007', 'Fallback Location (2026-12-01 to 2026-12-15), Snapshot Location (2026-12-16 to 2026-12-31)'],
        ], array_slice($table['rows'], 0, 2));
    }

    public function test_master_section_keeps_a_single_value_when_fallback_matches_snapshot(): void
    {
        DB::table('locations')->insert([
            'id' => 51,
            'name' => 'Same Location',
            'code' => 'SAME',
        ]);
        $this->snapshot(51, ['name' => 'Same Location', 'code' => 'SAME'], '2026-12-16', null, null, true);
        $this->snapshot(51, ['name' => 'Same Location', 'code' => 'SAME'], '2026-12-16', null, 8, false);

        $employee = new User;
        $employee->setRawAttributes([
            'id' => 8,
            'code' => 'E008',
            'location_id' => '[51]',
        ], true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'date'],
                ['field_name' => 'location_name'],
            ],
            '2026-12-01',
            '2026-12-31',
            'vertical',
            [$employee],
        );

        $this->assertSame([
            ['Emp Code', 'Location Name'],
            ['E008', 'Same Location'],
        ], array_slice($table['rows'], 0, 2));
    }

    public function test_report_without_date_uses_current_assignment_and_snapshot_value_once_per_employee(): void
    {
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 8,
            'code' => 'E002',
            'location_id' => '[3]',
        ], true);
        $this->snapshot(3, ['name' => 'Current Location', 'code' => 'CUR'], today()->toDateString(), null, null, true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'location_name'],
            ],
            today()->subDays(30)->toDateString(),
            today()->toDateString(),
            'vertical',
            [$employee],
        );

        $this->assertSame(['Emp Code', 'Location Name'], $table['headers']);
        $this->assertSame([['E002', 'Current Location']], $table['rows']);
    }

    public function test_attendance_values_and_shift_snapshots_are_resolved_by_attendance_date(): void
    {
        DB::table('status_masters')->insert([
            ['id' => 1, 'code' => 'P'],
            ['id' => 2, 'code' => 'HD'],
        ]);
        $this->snapshot(12, ['name' => 'Day Shift', 'code' => 'DS'], '2026-11-01', '2026-11-20', null, true, Shift::class);
        $this->snapshot(12, ['name' => 'Night Shift', 'code' => 'NS'], '2026-11-20', null, null, true, Shift::class);
        DB::table('attendances2026')->insert([
            'user_id' => 9,
            'date' => '2026-11-19',
            'shift_id' => 12,
            'status_master_id' => 1,
            'shift_code' => 'DIRECT-VALUE',
            'in_time' => '2026-11-19 08:04:05',
            'out_time' => '2026-11-19 17:00:00',
            'is_late' => true,
            'late_minutes' => 5,
            'early_minutes' => 0,
            'work_time' => 510,
            'total_overtime_minutes' => 30,
            'approved_overtime_minutes' => 10,
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 9,
            'date' => '2026-11-20',
            'shift_id' => 12,
            'status_master_id' => 2,
            'is_half_day' => true,
            'shift_code' => 'DIRECT-VALUE',
            'in_time' => '2026-11-20 08:00:00',
            'out_time' => '2026-11-20 16:00:00',
            'is_late' => false,
            'late_minutes' => 9,
            'is_early' => true,
            'early_minutes' => 15,
            'work_time' => 480,
            'total_overtime_minutes' => 45,
            'approved_overtime_minutes' => 30,
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 10,
            'date' => '2026-11-19',
            'shift_id' => 12,
            'status_master_id' => 1,
            'in_time' => '2026-11-19 09:00:00',
            'out_time' => '2026-11-19 17:00:00',
            'is_late' => true,
            'late_minutes' => 2,
            'work_time' => 60,
        ]);
        $employee = new User;
        $employee->setRawAttributes(['id' => 9, 'code' => 'E009', 'shift_id' => 7], true);
        $secondEmployee = new User;
        $secondEmployee->setRawAttributes(['id' => 10, 'code' => 'E010'], true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'shift_name'],
                ['field_name' => 'shift_code'],
                ['field_name' => 'attendance_in_time', 'date_time_format' => 'H:i'],
                ['field_name' => 'attendance_out_time', 'date_time_format' => 'H:i'],
                ['field_name' => 'late_minutes', 'use_in_summary' => true],
                ['field_name' => 'early_minutes', 'minute_format' => 'hh_mm', 'use_in_summary' => true],
                ['field_name' => 'working_hrs', 'minute_format' => 'decimal', 'use_in_summary' => true],
                ['field_name' => 'overtime_minutes', 'use_in_summary' => true],
                ['field_name' => 'approved_overtime_minutes', 'minute_format' => 'hh_mm', 'use_in_summary' => true],
                ['field_name' => 'attendance_status', 'use_in_summary' => true],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'vertical',
            [$employee, $secondEmployee],
        );

        $this->assertSame([], $table['headers']);
        $this->assertSame([
            ['Emp Code'],
            ['E009'],
            [],
            [
                'Date',
                'Shift Name',
                'Shift Code',
                'Attendance In Time',
                'Attendance Out Time',
                'Late Minutes',
                'Early Minutes',
                'Working Hrs',
                'Overtime Minutes',
                'Approved Overtime Minutes',
                'Attendance Status',
            ],
            ['2026-11-19', 'Day Shift', 'DS', '08:04', '17:00', '5', '00:00', '8.50', '30', '00:10', 'P'],
            ['2026-11-20', 'Night Shift', 'NS', '08:00', '16:00', '0', '00:15', '8.00', '45', '00:30', 'HD'],
            [],
            ['Summary'],
            ['Late Minutes', '5'],
            ['Early Minutes', '00:15'],
            ['Working Hrs', '16.50'],
            ['Overtime Minutes', '75'],
            ['Approved Overtime Minutes', '00:40'],
            ['Attendance Status'],
            ['Status', 'Count'],
            ['HD', '1'],
            ['P', '1'],
            ['Grand Total', '2'],
            [],
            ['Emp Code'],
            ['E010'],
            [],
            [
                'Date',
                'Shift Name',
                'Shift Code',
                'Attendance In Time',
                'Attendance Out Time',
                'Late Minutes',
                'Early Minutes',
                'Working Hrs',
                'Overtime Minutes',
                'Approved Overtime Minutes',
                'Attendance Status',
            ],
            ['2026-11-19', 'Day Shift', 'DS', '09:00', '17:00', '2', '00:00', '1.00', '0', '00:00', 'P'],
            ['2026-11-20', '', '', '', '', '0', '00:00', '', '', '', ''],
            [],
            ['Summary'],
            ['Late Minutes', '2'],
            ['Early Minutes', '00:00'],
            ['Working Hrs', '1.00'],
            ['Overtime Minutes', '0'],
            ['Approved Overtime Minutes', '00:00'],
            ['Attendance Status'],
            ['Status', 'Count'],
            ['P', '1'],
            ['Grand Total', '1'],
        ], $table['rows']);

        $horizontalTable = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'late_minutes', 'use_in_summary' => true],
                ['field_name' => 'attendance_status', 'use_in_summary' => true],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'horizontal',
            [$employee],
        );

        $this->assertSame([], $horizontalTable['headers']);
        $this->assertSame([
            ['Emp Code'],
            ['E009'],
            [],
            ['Date', '2026-11-19', '2026-11-20'],
            ['Late Minutes', '5', '0'],
            ['Attendance Status', 'P', 'HD'],
            [],
            ['Summary'],
            ['Late Minutes', '5'],
            ['Attendance Status'],
            ['Status', 'Count'],
            ['HD', '1'],
            ['P', '1'],
            ['Grand Total', '2'],
        ], $horizontalTable['rows']);

        $statusFilteredTable = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'attendance_specific_status', 'status_master_ids' => [2], 'use_in_summary' => true],
                ['field_name' => 'date'],
            ],
            '2026-11-01',
            '2026-11-30',
            'vertical',
            [$employee, $secondEmployee],
        );

        $statusDateRows = array_values(array_filter(
            $statusFilteredTable['rows'],
            static fn (array $row): bool => isset($row[0]) && preg_match('/^2026-11-\d{2}$/', $row[0]) === 1,
        ));
        $this->assertContains(['2026-11-20', 'HD'], $statusFilteredTable['rows']);
        $this->assertContains(['2026-11-19', ''], $statusFilteredTable['rows']);
        $this->assertContains(['2026-11-01', ''], $statusFilteredTable['rows']);
        $this->assertContains(['2026-11-30', ''], $statusFilteredTable['rows']);
        $this->assertCount(60, $statusDateRows);
        $this->assertContains(['HD', '1'], $statusFilteredTable['rows']);
        $this->assertContains(['Grand Total', '1'], $statusFilteredTable['rows']);
        $this->assertNotContains(['HD', '0'], $statusFilteredTable['rows']);
        $this->assertSame(
            1,
            count(array_filter(
                $statusFilteredTable['rows'],
                static fn (array $row): bool => $row === ['Attendance Status'],
            )),
        );
        $this->assertNotContains(['2026-11-19', 'P'], $statusFilteredTable['rows']);

        $halfStatusSummary = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                [
                    'field_name' => 'attendance_specific_status',
                    'status_master_ids' => [2],
                    'use_in_summary' => true,
                    'half_status_count_as_half' => true,
                ],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'vertical',
            [$employee],
        );
        $this->assertContains(['HD', '0.5'], $halfStatusSummary['rows']);
        $this->assertContains(['Grand Total', '0.5'], $halfStatusSummary['rows']);

        $horizontalHalfStatusSummary = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                [
                    'field_name' => 'attendance_specific_status',
                    'status_master_ids' => [2],
                    'use_in_summary' => true,
                    'half_status_count_as_half' => true,
                ],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'vertical',
            [$employee],
            'horizontal',
        );
        $this->assertContains(['Count', '0.5', '0.5'], $horizontalHalfStatusSummary['rows']);

        $horizontalStatusSummary = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'attendance_specific_status', 'status_master_ids' => [2], 'use_in_summary' => true],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'vertical',
            [$employee, $secondEmployee],
            'horizontal',
        );
        $this->assertSame(
            1,
            count(array_filter(
                $horizontalStatusSummary['rows'],
                static fn (array $row): bool => $row === ['Attendance Status'],
            )),
        );
        $this->assertContains(['Status', 'HD', 'Grand Total'], $horizontalStatusSummary['rows']);
        $this->assertContains(['Count', '1', '1'], $horizontalStatusSummary['rows']);

        $horizontalEmployeeSummary = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'attendance_specific_status', 'status_master_ids' => [2], 'use_in_summary' => true],
            ],
            '2026-11-19',
            '2026-11-20',
            'vertical',
            [$employee, $secondEmployee],
            'horizontal',
        );
        $this->assertSame(
            [
                'Emp Code',
                'Attendance Specific Status',
                'Summary - Attendance Status HD Count',
                'Summary - Attendance Status Grand Total Count',
            ],
            $horizontalEmployeeSummary['headers'],
        );
        $this->assertSame(['E010', '', '', '0'], $horizontalEmployeeSummary['rows'][1]);

        $horizontalSummaryTable = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'late_minutes', 'use_in_summary' => true],
                ['field_name' => 'attendance_status', 'use_in_summary' => true],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'horizontal',
            [$employee],
            'horizontal',
        );

        $this->assertSame([
            ['Emp Code'],
            ['E009'],
            [],
            ['Date', '2026-11-19', '2026-11-20'],
            ['Late Minutes', '5', '0'],
            ['Attendance Status', 'P', 'HD'],
            [],
            ['Summary'],
            ['Late Minutes'],
            ['5'],
            ['Attendance Status'],
            ['Status', 'HD', 'P', 'Grand Total'],
            ['Count', '1', '1', '2'],
        ], $horizontalSummaryTable['rows']);

        $legacyFormatTable = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'working_hrs', 'date_time_format' => 'H:i', 'use_in_summary' => true],
                ['field_name' => 'date'],
            ],
            '2026-11-19',
            '2026-11-20',
            'vertical',
            [$employee],
        );

        $this->assertSame([
            ['Emp Code'],
            ['E009'],
            [],
            ['Date', 'Working Hrs'],
            ['2026-11-19', '08:30'],
            ['2026-11-20', '08:00'],
            [],
            ['Summary'],
            ['Working Hrs', '16:30'],
        ], $legacyFormatTable['rows']);
    }

    public function test_missing_late_and_early_minutes_use_the_selected_decimal_format(): void
    {
        DB::table('attendances2026')->insert([
            'user_id' => 11,
            'date' => '2026-11-20',
            'is_late' => false,
            'late_minutes' => 15,
            'is_early' => false,
            'early_minutes' => 20,
        ]);
        $employee = new User;
        $employee->setRawAttributes(['id' => 11, 'code' => 'E011'], true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'date'],
                ['field_name' => 'late_minutes', 'minute_format' => 'decimal'],
                ['field_name' => 'early_minutes', 'minute_format' => 'decimal'],
            ],
            '2026-11-20',
            '2026-11-20',
            'vertical',
            [$employee],
        );

        $this->assertSame([
            ['Employee'],
            ['E011'],
            [],
            ['Date', 'Late Minutes', 'Early Minutes'],
            ['2026-11-20', '0.00', '0.00'],
        ], $table['rows']);
    }

    private function snapshot(
        int $masterId,
        array $value,
        string $fromDate,
        ?string $toDate,
        ?int $userId,
        bool $forAll,
        string $masterType = Location::class,
    ): void {
        MasterSnapshot::query()->create([
            'master_type' => $masterType,
            'master_id' => $masterId,
            'type' => 'both',
            'value' => $value,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'user_id' => $userId,
            'for_all' => $forAll,
        ]);
    }
}
