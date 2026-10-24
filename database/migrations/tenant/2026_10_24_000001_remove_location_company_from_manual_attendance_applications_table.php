<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'manual_attendance_applications';
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $columns = array_values(array_filter(
            ['location_id', 'company_id'],
            static fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));
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

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('manual_attendance_applications')) {
            return;
        }

        Schema::table('manual_attendance_applications', function (Blueprint $table): void {
            if (! Schema::hasColumn('manual_attendance_applications', 'location_id')) {
                $table->foreignId('location_id')->nullable()->constrained('locations')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('manual_attendance_applications', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            }
        });
    }
};
