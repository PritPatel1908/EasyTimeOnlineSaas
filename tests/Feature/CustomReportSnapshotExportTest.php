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
            $table->unsignedBigInteger('status_master_id')->nullable();
            $table->string('shift_code')->nullable();
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
        Schema::create('status_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
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
            ['E001', 'Old Location'],
            [],
            ['Date'],
            ['14/01/2026'],
            ['15/01/2026'],
            ['16/01/2026'],
        ], $table['rows']);
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
        $this->assertContains(['HD', '0'], $statusFilteredTable['rows']);
        $this->assertNotContains(['2026-11-19', 'P'], $statusFilteredTable['rows']);

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
            ['Status', 'HD', 'P'],
            ['Count', '1', '1'],
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
