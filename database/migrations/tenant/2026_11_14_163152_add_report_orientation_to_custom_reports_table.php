<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_reports', function (Blueprint $table) {
            $table->string('report_orientation', 20)->default('vertical')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_reports', function (Blueprint $table) {
            $table->dropColumn('report_orientation');
        });
    }
};
