<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'location_id',
        'company_id',
        'department_id',
        'sub_department_id',
        'category_id',
        'sub_category_id',
    ];

    public function up(): void
    {
        foreach ($this->attendanceTables() as $tableName) {
            $columns = array_values(array_filter(
                self::COLUMNS,
                static fn (string $column): bool => Schema::hasColumn($tableName, $column),
            ));

            if ($columns === []) {
                continue;
            }

            $foreignKeys = DB::getDriverName() === 'sqlite'
                ? []
                : array_filter(
                    Schema::getForeignKeys($tableName),
                    static fn (array $foreignKey): bool => count(array_intersect($foreignKey['columns'], $columns)) > 0,
                );

            Schema::table($tableName, function (Blueprint $table) use ($columns, $foreignKeys): void {
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey['name']);
                }

                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->attendanceTables() as $tableName) {
            $columns = array_values(array_filter(
                self::COLUMNS,
                static fn (string $column): bool => ! Schema::hasColumn($tableName, $column),
            ));

            if ($columns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                if (in_array('location_id', $columns, true)) {
                    $table->foreignId('location_id')->nullable()->constrained('locations');
                }
                if (in_array('company_id', $columns, true)) {
                    $table->foreignId('company_id')->nullable()->constrained('companies');
                }
                foreach (['department_id', 'sub_department_id', 'category_id', 'sub_category_id'] as $column) {
                    if (in_array($column, $columns, true)) {
                        $table->json($column)->nullable();
                    }
                }
            });
        }
    }

    /**
     * @return list<string>
     */
    private function attendanceTables(): array
    {
        return array_values(array_filter(
            Schema::getTableListing(null, false),
            static fn (string $tableName): bool => preg_match('/\A(?:attendances|in_out_musters)\d{4}\z/i', $tableName) === 1,
        ));
    }
};
