<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1) {
                continue;
            }

            $hasIsCoff = Schema::hasColumn($tableName, 'is_coff');
            $hasCoffCreditDays = Schema::hasColumn($tableName, 'coff_credit_dayes');
            if ($hasIsCoff && $hasCoffCreditDays) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($hasIsCoff, $hasCoffCreditDays): void {
                if (! $hasIsCoff) {
                    $table->boolean('is_coff')->default(false);
                }
                if (! $hasCoffCreditDays) {
                    $table->decimal('coff_credit_dayes', 10, 2)->default(0);
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

            $columns = array_values(array_filter(
                ['is_coff', 'coff_credit_dayes'],
                fn (string $column): bool => Schema::hasColumn($tableName, $column),
            ));
            if ($columns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
