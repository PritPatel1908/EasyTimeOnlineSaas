<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('week_off_swaps', 'wo_type')) {
            Schema::table('week_off_swaps', function (Blueprint $table): void {
                $table->dropColumn('wo_type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('week_off_swaps', 'wo_type')) {
            Schema::table('week_off_swaps', function (Blueprint $table): void {
                $table->tinyInteger('wo_type')->nullable();
            });
        }
    }
};
