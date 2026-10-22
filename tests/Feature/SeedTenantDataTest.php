<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Central\SeedTenantData;
use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeedTenantDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_job_loads_tenant_from_central_database_with_active_tenancy(): void
    {
        Tenant::query()->create([
            'id' => 'acme',
            'tenancy_db_connection' => 'sqlite',
            'tenancy_db_name' => ':memory:',
        ]);

        $otherTenant = new Tenant(['id' => 'other']);
        tenancy()->initialize($otherTenant);

        Artisan::shouldReceive('call')
            ->once()
            ->with('db:seed', array_merge(
                config('tenancy.seeder_parameters'),
                ['--force' => true],
            ));

        try {
            (new SeedTenantData('acme'))->handle();

            $this->assertSame('other', tenancy()->tenant->id);
        } finally {
            tenancy()->end();
        }
    }
}
