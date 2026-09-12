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
        Schema::create('manual_attendance_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_rotation_id')->nullable()->constrained();
            $table->string('shift_type')->nullable();
            $table->foreignId('shift_id')->nullable()->constrained();
            $table->dateTime('in_time');
            $table->dateTime('out_time');
            $table->tinyInteger('status')->default(1);
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->text('reason')->nullable();
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
        Schema::dropIfExists('manual_attendance_applications');
    }
};
