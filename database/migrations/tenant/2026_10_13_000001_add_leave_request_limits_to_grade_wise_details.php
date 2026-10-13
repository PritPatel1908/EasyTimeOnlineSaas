<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->integer('maximum_request_in_a_month')->nullable();
            $table->integer('maximum_request_in_a_year')->nullable();
        });

        Schema::table('grade_wise_yearly_leave_details', function (Blueprint $table) {
            $table->integer('maximum_request_in_a_month')->nullable();
            $table->integer('maximum_request_in_a_year')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->dropColumn(['maximum_request_in_a_month', 'maximum_request_in_a_year']);
        });

        Schema::table('grade_wise_yearly_leave_details', function (Blueprint $table) {
            $table->dropColumn(['maximum_request_in_a_month', 'maximum_request_in_a_year']);
        });
    }
};
