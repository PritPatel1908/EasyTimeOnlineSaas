<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canteen_facility_rules', function (Blueprint $table): void {
            $table->dropUnique(['canteen_facility_id']);
        });
    }

    public function down(): void
    {
        Schema::table('canteen_facility_rules', function (Blueprint $table): void {
            $table->unique('canteen_facility_id');
        });
    }
};
