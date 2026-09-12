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
        Schema::create('rotation_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_rotation_id')->constrained('shift_rotations');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->integer('days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotation_values');
    }
};
