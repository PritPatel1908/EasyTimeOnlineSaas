<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_reports', function (Blueprint $table): void {
            $table->string('summary_orientation', 20)->default('vertical')->after('report_orientation');
        });

        DB::table('custom_reports')->update([
            'summary_orientation' => DB::raw('report_orientation'),
        ]);
    }

    public function down(): void
    {
        Schema::table('custom_reports', function (Blueprint $table): void {
            $table->dropColumn('summary_orientation');
        });
    }
};
