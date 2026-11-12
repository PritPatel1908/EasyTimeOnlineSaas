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
        Schema::create('email_template_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('application_type', 100);
            $table->string('application_action', 100);
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unique(
                ['application_type', 'application_action'],
                'email_template_assignment_type_action_unique'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_template_assignments');
    }
};
