<?php

declare(strict_types=1);

namespace App\Jobs\Central;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tenantId) {}

    public function handle(): void
    {
        $tenant = Tenant::findOrFail($this->tenantId);
        $connectionName = 'tenant_provision_'.$this->tenantId;
        $connectionConfig = $tenant->database()->connection();
        $driver = $connectionConfig['driver'];

        if ($driver === 'sqlsrv') {
            $connectionConfig['database'] = 'master';
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            $connectionConfig['database'] = null;
        } elseif ($driver === 'pgsql') {
            $connectionConfig['database'] = 'postgres';
        }

        config(['database.connections.'.$connectionName => $connectionConfig]);
        config(['database.connections.'.$tenant->database()->getTemplateConnectionName() => $connectionConfig]);
        DB::purge($connectionName);
        DB::purge($tenant->database()->getTemplateConnectionName());
        $databaseManager = $tenant->database()->manager();
        $databaseManager->setConnection($connectionName);
        $databaseName = $tenant->database()->getName();

        try {
            if (! $databaseManager->databaseExists($databaseName)) {
                $databaseManager->createDatabase($tenant);
            }

            $tenant->run(function (): void {
                Artisan::call('migrate', config('tenancy.migration_parameters'));
            });
        } finally {
            DB::purge($connectionName);
        }
    }
}
