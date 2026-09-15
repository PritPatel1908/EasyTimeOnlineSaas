<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_policy_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_policy_id')->constrained('data_policies');
            $table->foreignId('team_id')->constrained('teams');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_policy_team');
    }
};
