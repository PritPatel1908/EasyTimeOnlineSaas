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
        Schema::create('shift_change_applications', function (Blueprint $table) {
            $table->id();
            // TODO:remove user_id and make shift_change_user relationl table for multipal user selation
            // $table->foreignId('user_id')->nullable()->constrained();
            $table->string('shift_type')->nullable();
            // TODO:remove shift id hear and connected models
            // $table->foreignId('shift_id')->nullable()->constrained();
            $table->foreignId('shift_rotation_id')->nullable()->constrained();
            $table->tinyInteger('status')->default('1');
            $table->date('from_date')->nullable();
            $table->boolean('is_forever')->default(false);
            $table->date('to_date')->nullable();
            $table->string('reason')->nullable();
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
        Schema::dropIfExists('shift_change_applications');
    }
};
