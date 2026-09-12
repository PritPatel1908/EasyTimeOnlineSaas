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
        Schema::create('shift_musters', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->boolean('is_auto')->default(false);
            // $table->foreignId('shift_change_id')->nullable()->constrained('shift_changes');
            $table->nullableMorphs('shiftchangeable');
            $table->json('shift')->nullable();
            $table->foreignId('calculated_shift')->nullable()->constrained('shifts');
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_musters');
    }
};
