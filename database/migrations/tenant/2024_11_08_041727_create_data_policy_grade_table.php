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
        Schema::create('data_policy_grade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_policy_id')->constrained('data_policies');
            $table->foreignId('grade_id')->constrained('grades');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_policy_grade');
    }
};
