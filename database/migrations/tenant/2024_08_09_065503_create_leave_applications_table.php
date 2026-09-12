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
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('leave_type_id')->nullable()->constrained();
            $table->foreignId('leave_reason_id')->nullable()->constrained();
            $table->string('reason_explanation')->nullable();
            $table->boolean('is_only_second_half')->default(false);
            $table->date('from_date')->nullable();
            $table->boolean('is_second_half')->default(false);
            $table->datetime('application_date')->default(now());
            $table->date('to_date')->nullable();
            $table->boolean('is_first_half')->default(false);
            $table->decimal('leave_count', 10, 2)->nullable();
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
        Schema::dropIfExists('leave_applications');
    }
};
