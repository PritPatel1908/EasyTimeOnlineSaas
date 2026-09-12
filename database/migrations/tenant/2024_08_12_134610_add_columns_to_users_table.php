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
        Schema::table('users', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->string('fname')->nullable();
            $table->string('mname')->nullable();
            $table->string('lname')->nullable();
            $table->string('card')->nullable();
            $table->string('number')->nullable();
            $table->enum('user_type', ['employee', 'guest'])->nullable();
            $table->tinyInteger('status')->default(1);
            $table->boolean('is_locked')->default(false)->nullable();
            $table->string('profile_pic')->nullable();
            $table->date('dob')->nullable();
            $table->date('join_date')->nullable();
            $table->date('left_date')->nullable();
            $table->string('left_reason')->nullable();
            $table->foreignId('data_policy_id')->nullable()->constrained();
            // $table->foreignId('role_id')->nullable()->constrained();
            // $table->foreignId('shift_id')->nullable()->constrained();
            $table->foreignId('shift_rotation_id')->nullable()->constrained();
            $table->foreignId('leave_group_id')->nullable()->constrained();
            $table->foreignId('late_coming_rule_id')->nullable()->constrained();
            $table->foreignId('early_going_rule_id')->nullable()->constrained();
            $table->foreignId('half_day_rule_id')->nullable()->constrained();
            $table->foreignId('absent_rule_id')->nullable()->constrained();
            $table->foreignId('overtime_rule_id')->nullable()->constrained();
            $table->string('gender')->default('male')->nullable();
            $table->boolean('is_inactive')->default(false);
            $table->integer('login_attempts')->default(0)->after('is_inactive');
            $table->foreignId('created_by')->nullable()->constrained('users', 'id')->noActionOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id')->noActionOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users', 'id')->noActionOnDelete();
            $table->datetime('password_changed_at', $precision = 0)->nullable();
            $table->string('shift_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
