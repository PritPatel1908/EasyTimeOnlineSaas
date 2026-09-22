<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('team_id')->nullable()->after('leave_group_id')->constrained('teams')->nullOnDelete();
            $table->foreignId('canteen_facility_id')->nullable()->after('team_id')->constrained('canteen_facilities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['canteen_facility_id']);
            $table->dropForeign(['team_id']);
            $table->dropColumn(['canteen_facility_id', 'team_id']);
        });
    }
};
