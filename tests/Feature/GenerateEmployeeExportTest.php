<?php

namespace Tests\Feature;

use App\Jobs\Tenant\GenerateEmployeeExport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class GenerateEmployeeExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
    }

    public function test_names_resolves_scalar_and_array_relation_ids(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });

        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Hourly'],
            ['id' => 2, 'name' => 'Salaried'],
        ]);

        $job = new GenerateEmployeeExport(1, 'employees.csv');
        $names = new ReflectionMethod($job, 'names');
        $names->setAccessible(true);

        $this->assertSame('Hourly', $names->invoke($job, 1, 'Category'));
        $this->assertSame('Hourly, Salaried', $names->invoke($job, [1, 2], 'Category'));
    }

    public function test_employee_export_values_follow_their_header_names(): void
    {
        $job = new GenerateEmployeeExport(1, 'employees.csv');
        $orderByExportColumns = new ReflectionMethod($job, 'orderByExportColumns');
        $orderByExportColumns->setAccessible(true);

        $row = $orderByExportColumns->invoke($job, [
            'join_date' => '2022-06-01',
            'status' => 'Active',
            'blood_group_id' => 3,
            'dob' => '1998-04-19',
            'gender' => 'male',
            'number' => '0000001067',
            'card' => 'EMP-1067',
            'user_type' => 'employee',
        ]);

        $this->assertSame(count(\App\Models\Tenant\User::IMPORT_EXPORT_COLUMNS), count($row));
        $this->assertSame('1998-04-19', $row[array_search('dob', \App\Models\Tenant\User::IMPORT_EXPORT_COLUMNS, true)]);
        $this->assertSame(3, $row[array_search('blood_group_id', \App\Models\Tenant\User::IMPORT_EXPORT_COLUMNS, true)]);
        $this->assertSame('2022-06-01', $row[array_search('join_date', \App\Models\Tenant\User::IMPORT_EXPORT_COLUMNS, true)]);
        $this->assertSame('Active', $row[array_search('status', \App\Models\Tenant\User::IMPORT_EXPORT_COLUMNS, true)]);
    }
}
