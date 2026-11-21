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
        Schema::table('half_day_rules', function (Blueprint $table): void {
            $table->boolean('mark_late_on_half_day')->default(false);
            $table->boolean('mark_early_on_half_day')->default(false);
        });

        Schema::table('absent_rules', function (Blueprint $table): void {
            $table->boolean('mark_late_on_absent')->default(false);
            $table->boolean('mark_early_on_absent')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('half_day_rules', function (Blueprint $table): void {
            $table->dropColumn(['mark_late_on_half_day', 'mark_early_on_half_day']);
        });

        Schema::table('absent_rules', function (Blueprint $table): void {
            $table->dropColumn(['mark_late_on_absent', 'mark_early_on_absent']);
        });
    }
};
