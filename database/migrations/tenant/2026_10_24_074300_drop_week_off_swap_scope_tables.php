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
        Schema::dropIfExists('week_off_swap_sub_department');
        Schema::dropIfExists('week_off_swap_sub_category');
        Schema::dropIfExists('week_off_swap_designation');
        Schema::dropIfExists('week_off_swap_department');
        Schema::dropIfExists('week_off_swap_category');
        Schema::dropIfExists('week_off_swap_area');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('week_off_swap_sub_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('sub_department_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_swap_sub_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('sub_category_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_swap_designation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('designation_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_swap_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('department_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_swap_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('category_id')->constrained();
            $table->timestamps();
        });

        Schema::create('week_off_swap_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('area_id')->constrained();
            $table->timestamps();
        });
    }
};
