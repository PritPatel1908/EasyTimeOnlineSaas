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
        Schema::create('password_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name');
            $table->integer('password_expiry_days');
            $table->integer('notify_expiry_days');

            $table->foreignId('created_by')->nullable()->constrained('users', 'id')->noActionOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id')->noActionOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id')->noActionOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('password_policy_id')->nullable()->constrained('password_policies', 'id')->noActionOnDelete()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_policies');
    }
};
