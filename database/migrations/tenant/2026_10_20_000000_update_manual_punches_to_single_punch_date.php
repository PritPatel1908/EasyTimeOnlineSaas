<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('manual_punches', function (Blueprint $table) {
            $table->renameColumn('from_punch_date', 'punch_date');
            $table->dropColumn('to_punch_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_punches', function (Blueprint $table) {
            $table->renameColumn('punch_date', 'from_punch_date');
            $table->date('to_punch_date')->nullable()->after('from_punch_date');
        });

        DB::table('manual_punches')->update([
            'to_punch_date' => DB::raw('from_punch_date'),
        ]);
    }
};
