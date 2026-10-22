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
        Schema::table('week_off_changes', function (Blueprint $table) {
            $table->dropColumn(['location_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('week_off_changes', function (Blueprint $table) {
            $table->json('location_id')->nullable();
            $table->json('company_id')->nullable();
        });
    }
};
