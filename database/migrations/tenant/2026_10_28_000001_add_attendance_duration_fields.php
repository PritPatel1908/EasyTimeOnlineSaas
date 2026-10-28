<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'late_minutes',
        'early_minutes',
        'total_overtime_minutes',
        'approved_overtime_minutes',
    ];

    public function up(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1) {
                continue;
            }

            $missingColumns = array_values(array_filter(
                self::COLUMNS,
                static fn (string $column): bool => ! Schema::hasColumn($tableName, $column),
            ));

            if ($missingColumns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($missingColumns): void {
                foreach ($missingColumns as $column) {
                    $table->unsignedInteger($column)->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1) {
                continue;
            }

            $existingColumns = array_values(array_filter(
                self::COLUMNS,
                static fn (string $column): bool => Schema::hasColumn($tableName, $column),
            ));

            if ($existingColumns !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($existingColumns): void {
                    $table->dropColumn($existingColumns);
                });
            }
        }
    }
};
