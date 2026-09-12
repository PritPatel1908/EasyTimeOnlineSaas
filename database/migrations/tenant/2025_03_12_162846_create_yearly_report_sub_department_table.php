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
        Schema::create('yearly_report_sub_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yearly_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_department_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearly_report_sub_department');
    }
};
