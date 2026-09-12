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
        Schema::create('c_off_against_wo_hl_slabs', function (Blueprint $table) {
            $table->id();
            $table->decimal('credit_days', 10, 2)->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_off_against_wo_hl_slabs');
    }
};
