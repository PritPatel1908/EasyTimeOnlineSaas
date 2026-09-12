<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseReferenceChecker
{
    /**
     * @param array<int, string>|null $referenceColumns
     * @return array{table: string, column: string}|null
     */
    public static function findReferences(
        string $referencedTable,
        int|string $referencedId,
        ?array $referenceColumns = null,
        ?string $connectionName = null,
    ): ?array {
        $connection = DB::connection($connectionName);
        $schema     = $connection->getSchemaBuilder();
        $targetColumns = array_map(
            'strtolower',
            $referenceColumns ?? [Str::singular($referencedTable).'_id'],
        );

        foreach ($schema->getTables() as $tableDefinition) {
            $table = self::metadataName($tableDefinition);

            if ($table === null
                || strcasecmp($table, $referencedTable) === 0
                || strcasecmp($table, 'migrations') === 0
            ) {
                continue;
            }

            foreach ($schema->getColumns($table) as $columnDefinition) {
                $column = self::metadataName($columnDefinition);

                if ($column === null || ! in_array(strtolower($column), $targetColumns, true)) {
                    continue;
                }

                if (self::columnContainsId($connection, $table, $column, $referencedId)) {
                    return ['table' => $table, 'column' => $column];
                }
            }
        }

        return null;
    }

    /**
     * Returns every table+column pair that references the given record.
     * Unlike findReferences(), this does not stop at the first match.
     *
     * @param  array<int, string>|null  $referenceColumns
     * @return array<int, array{table: string, column: string}>
     */
    public static function findAllReferences(
        string $referencedTable,
        int|string $referencedId,
        ?array $referenceColumns = null,
        ?string $connectionName = null,
    ): array {
        $connection = DB::connection($connectionName);
        $schema     = $connection->getSchemaBuilder();
        $targetColumns = array_map(
            'strtolower',
            $referenceColumns ?? [Str::singular($referencedTable).'_id'],
        );

        $found = [];

        foreach ($schema->getTables() as $tableDefinition) {
            $table = self::metadataName($tableDefinition);

            if ($table === null
                || strcasecmp($table, $referencedTable) === 0
                || strcasecmp($table, 'migrations') === 0
            ) {
                continue;
            }

            foreach ($schema->getColumns($table) as $columnDefinition) {
                $column = self::metadataName($columnDefinition);

                if ($column === null || ! in_array(strtolower($column), $targetColumns, true)) {
                    continue;
                }

                if (self::columnContainsId($connection, $table, $column, $referencedId)) {
                    $found[] = ['table' => $table, 'column' => $column];
                    // One match per table is enough; move on to the next table.
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param array<int, string>|null $referenceColumns
     */
    public static function hasReferences(
        string $referencedTable,
        int|string $referencedId,
        ?array $referenceColumns = null,
        ?string $connectionName = null,
    ): bool {
        return self::findReferences($referencedTable, $referencedId, $referenceColumns, $connectionName) !== null;
    }

    private static function columnContainsId(
        Connection $connection,
        string $table,
        string $column,
        int|string $referencedId,
    ): bool {
        $driver = $connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $id     = (string) $referencedId;
        $query  = $connection->table($table);
        $wrapped = $query->getGrammar()->wrap($column);

        if ($driver === 'sqlsrv') {
            /*
             * SQL Server: column may hold any of these formats:
             *   1        plain integer
             *   "1"      JSON-encoded scalar  (nvarchar column with json cast)
             *   [1,2]    JSON array
             *   ["1","2"] JSON array of strings
             *
             * Strategy:
             *  - TRY_CAST covers plain integers stored as numeric strings.
             *  - CHARINDEX covers JSON-encoded scalars ("1") and JSON arrays ([1] / ["1"]).
             *    We search for both `"<id>"` (quoted) and `,<id>,` / `[<id>]` patterns.
             */
            return $query->where(function ($q) use ($wrapped, $id): void {
                // Plain integer match
                $q->whereRaw("TRY_CAST({$wrapped} AS BIGINT) = ?", [$id]);

                // JSON-encoded scalar: "1"
                $q->orWhereRaw("CHARINDEX(?, {$wrapped}) > 0", ['"'.$id.'"']);

                // JSON array element: [1,2] or [1] — numeric value not quoted
                $q->orWhereRaw(
                    "CHARINDEX(?, ',' + REPLACE(REPLACE({$wrapped}, '[', ''), ']', '') + ',') > 0",
                    [','.$id.','],
                );
            })->exists();
        }

        if ($driver === 'sqlite') {
            /*
             * SQLite has no JSON_CONTAINS. Column may hold plain integer or JSON text.
             * Use a simple equality check plus LIKE-based JSON array search.
             */
            return $query->where(function ($q) use ($column, $wrapped, $id, $connection): void {
                // Plain integer / string match
                $q->where($column, $id);

                // JSON array: strip [ ] and wrap with commas, then LIKE search
                $q->orWhereRaw(
                    "',' || REPLACE(REPLACE(REPLACE({$wrapped}, '[', ''), ']', ''), '\"', '') || ',' LIKE ?",
                    ["%,{$id},%"],
                );
            })->exists();
        }

        // MySQL / MariaDB / PostgreSQL
        return $query->where(function ($q) use ($column, $id): void {
            $q->where($column, $id);
            $q->orWhereJsonContains($column, (int) $id);
            $q->orWhereJsonContains($column, $id); // string variant
        })->exists();
    }

    private static function metadataName(mixed $definition): ?string
    {
        $name = is_array($definition) ? ($definition['name'] ?? null) : ($definition->name ?? null);

        return is_string($name) && $name !== '' ? $name : null;
    }
}
