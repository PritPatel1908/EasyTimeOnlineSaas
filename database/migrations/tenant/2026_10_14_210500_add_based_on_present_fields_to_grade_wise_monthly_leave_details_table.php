<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->string('present_count_per', 20)->nullable();
            $table->unsignedInteger('if_total_present_count')->nullable();
            $table->decimal('then_credit_balance_days', 10, 2)->nullable();
            $table->json('include_leaves')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->dropColumn([
                'present_count_per',
                'if_total_present_count',
                'then_credit_balance_days',
                'include_leaves',
            ]);
        });
    }
};
