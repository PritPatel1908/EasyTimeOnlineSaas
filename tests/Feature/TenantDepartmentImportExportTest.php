<?php

namespace Tests\Feature;

use App\Jobs\Tenant\ProcessDepartmentImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class TenantDepartmentImportExportTest extends TestCase
{
    public function test_import_resolves_company_and_location_by_id_name_or_code(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('email')->nullable();
            $table->json('location_id')->nullable();
            $table->integer('status')->default(1);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('status')->default(1);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Alpha Co',
            'code' => 'AC-1',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('locations')->insertGetId([
            'name' => 'Head Office',
            'code' => 'HO-1',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $job = new ProcessDepartmentImport(1, 'department-imports/test.csv');
        $method = new ReflectionMethod($job, 'resolveRelatedIds');
        $method->setAccessible(true);

        $this->assertSame([$companyId], $method->invoke($job, Company::class, '1'));
        $this->assertSame([$companyId], $method->invoke($job, Company::class, 'Alpha Co'));
        $this->assertSame([$companyId], $method->invoke($job, Company::class, 'AC-1'));
        $this->assertSame([$locationId], $method->invoke($job, Location::class, '1'));
        $this->assertSame([$locationId], $method->invoke($job, Location::class, 'Head Office'));
        $this->assertSame([$locationId], $method->invoke($job, Location::class, 'HO-1'));
    }
}
