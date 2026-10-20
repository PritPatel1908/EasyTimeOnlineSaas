<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('overtime_rules', 'ignore_early_come_minutes')) {
            Schema::table('overtime_rules', function (Blueprint $table) {
                $table->time('ignore_early_come_minutes')->nullable();
            });
        }

        if (! Schema::hasColumn('overtime_rules', 'ignore_late_come_minutes')) {
            Schema::table('overtime_rules', function (Blueprint $table) {
                $table->time('ignore_late_come_minutes')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['ignore_early_come_minutes', 'ignore_late_come_minutes'],
            static fn (string $column): bool => Schema::hasColumn('overtime_rules', $column),
        ));

        if ($columns !== []) {
            Schema::table('overtime_rules', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
