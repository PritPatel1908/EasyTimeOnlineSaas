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
            $table->json('location_id')->nullable();
            $table->json('company_id')->nullable();
            $table->json('department_id')->nullable();
            $table->json('sub_department_id')->nullable();
            $table->json('category_id')->nullable();
            $table->json('sub_category_id')->nullable();
            $table->json('designation_id')->nullable();
            $table->json('grade_id')->nullable();
            $table->json('unit_id')->nullable();
            $table->json('bus_route_id')->nullable();
            $table->string('dms_user_id')->nullable();
            $table->date('rejoin_date')->nullable();
            $table->string('rejoin_reason')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamp('inactive_date')->nullable();
            $table->integer('inactive_days')->default(10);
            $table->datetime('last_active_at')->nullable();
            $table->datetime('last_login_at')->nullable();
            $table->boolean('shift_status')->nullable()->default(0);
            $table->foreignId('shift_change_id')->nullable()->constrained('shift_changes');
            $table->foreignId('week_off_change_id')->nullable()->constrained('week_off_changes');
            $table->foreignId('shift_change_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('week_off_change_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('week_off_swap_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('manual_punch_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('manual_attendance_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('leave_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('short_leave_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('grade_wise_leave_id')->nullable()->constrained();
            $table->date('last_check_date')->nullable();
            $table->integer('last_check_id')->nullable();
            $table->integer('short_leave_minutes')->nullable();
            $table->foreignId('coff_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->foreignId('od_approval_flow_id')->nullable()->constrained('approval_flows');
            $table->string('aadhar_number')->nullable();
            $table->string('uan_number')->nullable();
            $table->string('esic_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['sub_department_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sub_category_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['bus_route_id']);
            $table->dropColumn('dms_user_id');
            $table->dropColumn('rejoin_date');
            $table->dropColumn('rejoin_reason');
            $table->dropColumn('reference_name');
            $table->dropColumn('reference_number');
            $table->dropColumn('inactive_date');
            $table->dropColumn('inactive_days');
            $table->dropColumn('last_active_at');
            $table->dropColumn('last_login_at');
            $table->dropColumn('shift_status');
            $table->dropForeign(['shift_change_id']);
            $table->dropForeign(['week_off_change_id']);
            $table->dropForeign(['shift_change_approval_flow_id']);
            $table->dropForeign(['week_off_change_approval_flow_id']);
            $table->dropForeign(['week_off_swap_approval_flow_id']);
            $table->dropForeign(['manual_punch_approval_flow_id']);
            $table->dropForeign(['manual_attendance_approval_flow_id']);
            $table->dropForeign(['leave_approval_flow_id']);
            $table->dropForeign(['short_leave_approval_flow_id']);
            $table->dropForeign(['grade_wise_leave_id']);
            $table->dropColumn('last_check_date');
            $table->dropColumn('last_check_id');
            $table->dropColumn('short_leave_minutes');
            $table->dropForeign(['coff_approval_flow_id']);
            $table->dropForeign(['od_approval_flow_id']);
            $table->dropColumn('location_id');
            $table->dropColumn('company_id');
            $table->dropColumn('department_id');
            $table->dropColumn('sub_department_id');
            $table->dropColumn('category_id');
            $table->dropColumn('sub_category_id');
            $table->dropColumn('designation_id');
            $table->dropColumn('grade_id');
            $table->dropColumn('unit_id');
            $table->dropColumn('bus_route_id');
            $table->dropColumn('shift_change_id');
            $table->dropColumn('week_off_change_id');
            $table->dropColumn('shift_change_approval_flow_id');
            $table->dropColumn('week_off_change_approval_flow_id');
            $table->dropColumn('week_off_swap_approval_flow_id');
            $table->dropColumn('manual_punch_approval_flow_id');
            $table->dropColumn('manual_attendance_approval_flow_id');
            $table->dropColumn('leave_approval_flow_id');
            $table->dropColumn('short_leave_approval_flow_id');
            $table->dropColumn('grade_wise_leave_id');
            $table->dropColumn('last_check_date');
            $table->dropColumn('last_check_id');
            $table->dropColumn('short_leave_minutes');
            $table->dropColumn('coff_approval_flow_id');
            $table->dropColumn('od_approval_flow_id');
            $table->dropColumn('aadhar_number');
            $table->dropColumn('uan_number');
            $table->dropColumn('esic_number');
        });
    }
};
