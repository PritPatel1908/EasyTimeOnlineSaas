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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('machine_ip')->nullable();
            $table->string('machine_serial_number')->nullable();
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->unsignedBigInteger('dms_area_id')->nullable();
            $table->tinyInteger('sync_dms')->default(0);
            $table->string('network_status')->default('offline');
            $table->string('access_direction')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations', 'id')->noActionOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies', 'id')->noActionOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas', 'id')->noActionOnDelete();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('machines');
    }
};
