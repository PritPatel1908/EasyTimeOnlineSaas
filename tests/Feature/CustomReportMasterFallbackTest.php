<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\User;
use App\Support\CustomReportTableBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomReportMasterFallbackTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.custom_report_master_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'custom_report_master_test',
        ]);
        DB::purge('custom_report_master_test');

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

        foreach (['companies', 'departments', 'designations', 'categories'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->softDeletes();
            });
        }

        DB::table('companies')->insert(['id' => 10, 'name' => 'Acme Industries', 'code' => 'ACME']);
        DB::table('departments')->insert(['id' => 20, 'name' => 'Production', 'code' => 'PROD']);
        DB::table('designations')->insert(['id' => 30, 'name' => 'Operator', 'code' => 'OP']);
        DB::table('categories')->insert(['id' => 40, 'name' => 'Worker', 'code' => 'WRK']);
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_master_test');
        DB::purge('custom_report_master_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_master_fields_fall_back_to_current_master_values_when_snapshots_are_missing(): void
    {
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 7,
            'code' => 'E001',
            'company_id' => '[10]',
            'department_id' => '[20]',
            'designation_id' => '[30]',
            'category_id' => '[40]',
        ], true);

        $table = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'emp_code'],
                ['field_name' => 'company_name'],
                ['field_name' => 'department_name'],
                ['field_name' => 'designation_name'],
                ['field_name' => 'category_name'],
            ],
            '2026-11-01',
            '2026-11-30',
            'vertical',
            [$employee],
        );

        $this->assertSame([[
            'E001',
            'Acme Industries',
            'Production',
            'Operator',
            'Worker',
        ]], $table['rows']);
    }
}
