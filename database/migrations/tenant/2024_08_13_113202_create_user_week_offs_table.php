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
        Schema::create('user_week_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('week_days')->nullable();
            $table->tinyInteger('wo_type')->nullable();
            $table->boolean('first_week')->default(false);
            $table->boolean('second_week')->default(false);
            $table->boolean('third_week')->default(false);
            $table->boolean('fourth_week')->default(false);
            $table->boolean('fifth_week')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_week_offs');
    }
};
