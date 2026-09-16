<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_facilities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->decimal('total_cfa', 12, 2);
            $table->unsignedTinyInteger('status')->default(1);
            $table->foreignId('location_id')->constrained('locations');
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('canteen_facility_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canteen_facility_id')->unique()->constrained('canteen_facilities')->cascadeOnDelete();
            $table->unsignedInteger('total_absent_days');
            $table->boolean('company_contribution_in_percentage_wise')->default(false);
            $table->string('company_allowance_contribution_in_fixed')->nullable();
            $table->string('company_allowance_contribution_in_percentage')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_facility_rules');
        Schema::dropIfExists('canteen_facilities');
    }
};
