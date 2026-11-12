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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('template_type', [
                'manual_punch_application',
                'overtime_application',
                'manual_attendance_application',
                'shift_change_application',
                'leave_application',
                'short_leave_application',
                'coff_application',
                'week_off_change_application',
                'week_off_swap_application',
                'birthday_notification',
                'joining_notification',
                'onboarding_notification',
                'manual_punch_application_approve_reject',
                'overtime_application_approve_reject',
                'manual_attendance_application_approve_reject',
                'shift_change_application_approve_reject',
                'leave_application_approve_reject',
                'short_leave_application_approve_reject',
                'coff_application_approve_reject',
                'week_off_change_application_approve_reject',
                'week_off_swap_application_approve_reject',
            ])->unique();
            $table->longText('email_content')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
