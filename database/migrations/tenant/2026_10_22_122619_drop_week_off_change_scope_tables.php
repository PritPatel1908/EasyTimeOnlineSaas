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
        Schema::dropIfExists('week_off_change_sub_department');
        Schema::dropIfExists('week_off_change_sub_category');
        Schema::dropIfExists('week_off_change_designation');
        Schema::dropIfExists('week_off_change_department');
        Schema::dropIfExists('week_off_change_category');
        Schema::dropIfExists('week_off_change_area');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('week_off_change_sub_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_id')->constrained();
            $table->foreignId('sub_department_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_change_sub_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_id')->constrained();
            $table->foreignId('sub_category_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_change_designation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_id')->constrained();
            $table->foreignId('designation_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_change_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_change_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_change_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_id')->constrained();
            $table->foreignId('area_id')->constrained();
            $table->timestamps();
        });
    }
};
