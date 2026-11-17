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
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_snapshot_test');
        DB::purge('custom_report_snapshot_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_date_report_uses_effective_snapshot_values_and_collapses_unchanged_dates(): void
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

        $this->assertSame(['Emp Code', 'Date', 'Location Name'], $table['headers']);
        $this->assertSame([
            ['E001', '14/01/2026', 'Old Location'],
            ['E001', '15/01/2026 - 16/01/2026', 'New Location'],
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

    private function snapshot(
        int $masterId,
        array $value,
        string $fromDate,
        ?string $toDate,
        ?int $userId,
        bool $forAll,
    ): void {
        MasterSnapshot::query()->create([
            'master_type' => Location::class,
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
