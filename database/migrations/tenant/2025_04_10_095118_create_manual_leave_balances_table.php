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
        Schema::create('manual_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->json('location_id')->nullable();
            $table->json('company_id')->nullable();
            $table->foreignId('leave_type_id')->nullable()->constrained();
            $table->decimal('balance', 10, 2)->default(0);
            $table->dateTime('date')->nullable();
            $table->text('reason')->nullable();
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
        Schema::dropIfExists('manual_leave_balances');
    }
};
