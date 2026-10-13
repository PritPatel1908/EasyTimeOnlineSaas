<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->boolean('keeps_balance')->default(false);
        });

        Schema::table('grade_wise_yearly_leave_details', function (Blueprint $table) {
            $table->boolean('keeps_balance')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table) {
            $table->dropColumn('keeps_balance');
        });

        Schema::table('grade_wise_yearly_leave_details', function (Blueprint $table) {
            $table->dropColumn('keeps_balance');
        });
    }
};
