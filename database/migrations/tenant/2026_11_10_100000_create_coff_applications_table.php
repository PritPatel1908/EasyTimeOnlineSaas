<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coff_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('coff_against_date');
            $table->date('coff_from_date');
            $table->date('coff_to_date');
            $table->boolean('is_half_day')->default(false);
            $table->boolean('is_first_half')->default(false);
            $table->boolean('is_second_half')->default(false);
            $table->string('status')->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['user_id', 'coff_from_date', 'coff_to_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coff_applications');
    }
};
