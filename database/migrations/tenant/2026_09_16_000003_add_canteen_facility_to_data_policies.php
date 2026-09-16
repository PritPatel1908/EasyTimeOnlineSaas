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
        Schema::table('data_policies', function (Blueprint $table): void {
            $table->boolean('all_canteen_facilities')->default(false)->after('all_areas');
        });

        Schema::create('data_policy_canteen_facility', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('data_policy_id')->constrained('data_policies')->cascadeOnDelete();
            $table->foreignId('canteen_facility_id')->constrained('canteen_facilities')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_policy_canteen_facility');

        Schema::table('data_policies', function (Blueprint $table): void {
            $table->dropColumn('all_canteen_facilities');
        });
    }
};
