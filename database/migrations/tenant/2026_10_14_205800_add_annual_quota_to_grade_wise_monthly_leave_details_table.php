<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->decimal('annual_quota', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->dropColumn('annual_quota');
        });
    }
};
