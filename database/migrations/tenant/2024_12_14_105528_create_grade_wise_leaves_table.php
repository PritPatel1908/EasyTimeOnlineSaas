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
        Schema::create('grade_wise_leaves', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years');
            $table->boolean('is_monthly')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_wise_leaves');
    }
};
