<?php

declare(strict_types=1);

namespace App\TenantDatabaseManagers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantDatabaseManager;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Exceptions\NoConnectionSetException;

class SQLServerDatabaseManager implements TenantDatabaseManager
{
    protected ?string $connection = null;

    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $this->quoteIdentifier($tenant->database()->getName());

        return $this->database()->statement("CREATE DATABASE {$database}");
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $this->quoteIdentifier($tenant->database()->getName());

        return $this->database()->statement("DROP DATABASE {$database}");
    }

    public function databaseExists(string $name): bool
    {
        $this->configureTenantConnection();

        return (bool) $this->database()->selectOne(
            'SELECT name FROM sys.databases WHERE name = ?',
            [$name],
        );
    }

    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $baseConfig['database'] = $databaseName;

        return $baseConfig;
    }

    private function database(): Connection
    {
        if ($this->connection === null) {
            throw new NoConnectionSetException(self::class);
        }

        return DB::connection($this->connection);
    }

    private function configureTenantConnection(): void
    {
        if ($this->connection === null || ! tenancy()->initialized || tenancy()->tenant === null) {
            return;
        }

        $tenantConnection = tenancy()->tenant->database()->getTemplateConnectionName();

        if ($this->connection !== $tenantConnection) {
            return;
        }

        config([
            'database.connections.'.$this->connection => tenancy()->tenant->database()->connection(),
        ]);

        DB::purge($this->connection);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '['.str_replace(']', ']]', $identifier).']';
    }
}
