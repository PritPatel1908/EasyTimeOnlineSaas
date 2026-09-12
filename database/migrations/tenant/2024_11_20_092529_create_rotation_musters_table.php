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
        Schema::create('rotation_musters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_id')->nullable()->constrained('shift_rotations');
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotation_musters');
    }
};
