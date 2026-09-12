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
        Schema::create('week_off_change_application_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_off_change_application_id')->constrained('week_off_change_applications')->onDelete('cascade');
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
        Schema::dropIfExists('week_off_change_application_details');
    }
};
