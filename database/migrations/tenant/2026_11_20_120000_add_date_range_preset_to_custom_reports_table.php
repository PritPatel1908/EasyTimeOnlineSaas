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
            $table->string('date_range_preset', 32)->default('custom')->after('to_date');
            $table->date('from_date')->nullable()->change();
            $table->date('to_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        $today = now()->toDateString();
        DB::table('custom_reports')->whereNull('from_date')->update(['from_date' => $today]);
        DB::table('custom_reports')->whereNull('to_date')->update(['to_date' => $today]);

        Schema::table('custom_reports', function (Blueprint $table): void {
            $table->date('from_date')->nullable(false)->change();
            $table->date('to_date')->nullable(false)->change();
            $table->dropColumn('date_range_preset');
        });
    }
};
