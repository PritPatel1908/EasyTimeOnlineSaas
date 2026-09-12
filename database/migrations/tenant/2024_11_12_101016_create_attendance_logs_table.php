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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->default(null)->constrained()->nullOnDelete();
            // From DMS
            $table->string('user_code', 50)->nullable();
            $table->unsignedBigInteger('dms_log_id')->nullable();
            $table->unsignedBigInteger('dms_user_id')->nullable();
            $table->unsignedBigInteger('dms_area_id')->nullable();
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->dateTime('datetime', precision: 0)->nullable();
            $table->tinyInteger('punch_type')->nullable();
            $table->tinyInteger('verify_type')->nullable();

            // From Attendance Logs
            $table->boolean('is_out')->default(false);
            $table->boolean('debug_me')->default(true);
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_staged')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_sanctioned')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->boolean('has_error')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->boolean('is_locked')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
