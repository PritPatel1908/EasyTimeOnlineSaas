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
        Schema::create('week_off_swap_sub_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_swap_id')->constrained('week_off_swaps');
            $table->foreignId('sub_department_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('week_off_swap_sub_department');
    }
};
