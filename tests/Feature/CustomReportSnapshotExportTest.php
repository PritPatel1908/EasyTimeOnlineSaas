<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\Location;
use App\Models\Tenant\MasterSnapshot;
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
        Schema::create('shifts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->softDeletes();
        });
        Schema::create('attendances2026', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->string('shift_code')->nullable();
            $table->decimal('c_off_days', 10, 2)->default(0);
            $table->boolean('is_coff')->default(false);
            $table->decimal('coff_credit_dayes', 10, 2)->default(0);
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

    public function test_shift_name_is_exported_in_date_attendance_rows_from_attendance_shift(): void
    {
        DB::table('shifts')->insert([
            'id' => 12,
            'name' => 'Night Shift',
            'code' => 'NS',
        ]);
        DB::table('attendances2026')->insert([
            'user_id' => 9,
            'date' => '2026-11-20',
            'shift_id' => 12,
            'shift_code' => 'NS',
        ]);
        $employee = new User;
        $employee->setRawAttributes(['id' => 9, 'code' => 'E009', 'shift_id' => 7], true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'shift_name'],
                ['field_name' => 'shift_code'],
                ['field_name' => 'date'],
            ],
            '2026-11-20',
            '2026-11-20',
            'vertical',
            [$employee],
        );

        $this->assertSame([], $table['headers']);
        $this->assertSame([
            ['Emp Code'],
            ['E009'],
            ['Date', 'Shift Name', 'Shift Code'],
            ['2026-11-20', 'Night Shift', 'NS'],
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
