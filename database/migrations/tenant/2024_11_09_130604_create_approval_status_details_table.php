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
        Schema::create('approval_status_details', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('level')->nullable();
            $table->foreignId('approval_status_id')->constrained('approval_statuses')->onDelete('cascade');
            $table->foreignId('approval_flow_detail_id')->constrained('approval_flow_details')->onDelete('cascade');
            $table->timestamp('timestamp')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('approval_status')->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id');
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id');
            $table->softDeletes(); // enables soft deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_status_details');
    }
};
