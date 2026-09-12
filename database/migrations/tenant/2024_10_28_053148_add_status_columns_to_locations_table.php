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
        Schema::table('locations', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('sub_departments', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('absent_rules', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('overtime_rules', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('late_coming_rules', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('half_day_rules', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('financial_years', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('shift_rotations', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('leave_groups', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('data_policies', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('leave_reasons', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('short_leave_applications', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('manual_punches', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('shift_changes', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });

        Schema::table('coffs', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
};
