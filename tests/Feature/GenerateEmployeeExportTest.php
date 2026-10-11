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
            'blood_group' => 'O+',
            'dob' => '1998-04-19',
            'gender' => 'Male',
            'number' => '0000001067',
            'card' => 'EMP-1067',
            'user_type' => 'employee',
            'bank_account_number' => '1234567890',
            'flat_building' => 'A-101',
            'educations' => '[{"education":"B.Com"}]',
        ]);

        $columns = \App\Models\Tenant\User::EMPLOYEE_EXPORT_COLUMNS;
        $this->assertSame(count($columns), count($row));
        $this->assertSame('1998-04-19', $row[array_search('dob', $columns, true)]);
        $this->assertSame('O+', $row[array_search('blood_group', $columns, true)]);
        $this->assertSame('2022-06-01', $row[array_search('join_date', $columns, true)]);
        $this->assertSame('Active', $row[array_search('status', $columns, true)]);
        $this->assertSame('1234567890', $row[array_search('bank_account_number', $columns, true)]);
        $this->assertSame('A-101', $row[array_search('flat_building', $columns, true)]);
        $this->assertSame('[{"education":"B.Com"}]', $row[array_search('educations', $columns, true)]);
    }

    public function test_employee_export_headers_cover_employee_form_sections_without_changing_import_template(): void
    {
        $exportColumns = \App\Models\Tenant\User::EMPLOYEE_EXPORT_COLUMNS;
        $importColumns = \App\Models\Tenant\User::IMPORT_EXPORT_COLUMNS;

        $this->assertContains('bank_account_number', $exportColumns);
        $this->assertContains('aadhar_number', $exportColumns);
        $this->assertContains('blood_group', $exportColumns);
        $this->assertContains('family_details', $exportColumns);
        $this->assertContains('week_offs', $exportColumns);
        $this->assertNotContains('blood_group', $importColumns);
        $this->assertContains('blood_group_id', $importColumns);
    }
}
