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
        Schema::create('half_day_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('work_hr_less_than_minutes')->nullable();
            $table->boolean('single_punch_allowed')->default(false);
            $table->integer('late_coming_minutes')->nullable();
            $table->float('no_of_late')->nullable();
            $table->float('consecutive_late_coming')->nullable();
            $table->boolean('ignore_month_end_late')->default(false);
            $table->integer('early_going_minutes')->nullable();
            $table->float('no_of_early')->nullable();
            $table->float('consecutive_early_going')->nullable();
            $table->float('ignore_month_end_early')->nullable();
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
        Schema::dropIfExists('half_day_rules');
    }
};
