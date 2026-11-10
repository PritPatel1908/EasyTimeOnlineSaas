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
        Schema::create('overtime_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('value_from', 32)->nullable();
            $table->string('value_to', 32)->nullable();
            $table->string('head_value', 32)->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->foreignId('overtime_rule_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_slabs');
    }
};
