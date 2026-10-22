<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Central\MigrateTenantDatabase;
use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MigrateTenantDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_keeps_the_env_configured_central_connection_unchanged(): void
    {
        $databaseName = 'tenant-config-test-'.str_replace('.', '', uniqid('', true)).'.sqlite';
        $tenant = Tenant::query()->create([
            'id' => 'acme',
            'tenancy_db_connection' => 'sqlite',
            'tenancy_db_name' => $databaseName,
        ]);
        $centralConnection = config('tenancy.database.central_connection');
        $centralConfig = config('database.connections.'.$centralConnection);

        $this->assertSame('central', $centralConnection);
        $this->assertSame(
            config('database.connections.'.env('DB_CONNECTION', 'sqlite')),
            $centralConfig,
        );

        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', config('tenancy.migration_parameters'))
            ->andReturn(0);

        try {
            (new MigrateTenantDatabase('acme'))->handle();

            $this->assertSame($centralConfig, config('database.connections.'.$centralConnection));
            $this->assertDatabaseHas('tenants', ['id' => 'acme']);
        } finally {
            $tenant->database()->manager()->deleteDatabase($tenant);
        }
    }
}
