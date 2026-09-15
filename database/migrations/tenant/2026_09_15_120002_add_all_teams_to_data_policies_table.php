<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_policies', function (Blueprint $table): void {
            $table->boolean('all_teams')->default(false)->after('all_departments');
        });
    }

    public function down(): void
    {
        Schema::table('data_policies', function (Blueprint $table): void {
            $table->dropColumn('all_teams');
        });
    }
};
