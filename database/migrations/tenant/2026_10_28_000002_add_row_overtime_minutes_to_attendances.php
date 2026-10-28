<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1 || Schema::hasColumn($tableName, 'row_overtime_minutes')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedInteger('row_overtime_minutes')->default(0);
            });
        }
    }

    public function down(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1 || ! Schema::hasColumn($tableName, 'row_overtime_minutes')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('row_overtime_minutes');
            });
        }
    }
};
