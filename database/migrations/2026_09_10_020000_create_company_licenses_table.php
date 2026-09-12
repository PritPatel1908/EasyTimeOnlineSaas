<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_licenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->boolean('have_leave')->default(false);
            $table->boolean('have_payroll')->default(false);
            $table->unsignedInteger('location_count')->default(0);
            $table->unsignedInteger('company_count')->default(0);
            $table->unsignedInteger('user_count')->default(0);
            $table->date('expiry_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_licenses');
    }
};
