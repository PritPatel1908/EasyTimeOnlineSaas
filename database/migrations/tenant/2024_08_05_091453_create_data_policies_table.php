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
        Schema::create('data_policies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('self_only')->default(true);
            $table->boolean('all_locations')->default(false);
            $table->boolean('all_companies')->default(false);
            $table->boolean('all_departments')->default(false);
            $table->boolean('all_sub_departments')->default(false);
            $table->boolean('all_categories')->default(false);
            $table->boolean('all_sub_categories')->default(false);
            $table->boolean('all_designations')->default(false);
            $table->boolean('all_grades')->default(false);
            $table->boolean('all_units')->default(false);
            $table->boolean('all_bus_routes')->default(false);
            $table->boolean('all_areas')->default(false);
            $table->boolean('all_machines')->default(false);
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_policies');
    }
};
