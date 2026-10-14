<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->decimal('jan', 10, 2)->nullable();
            $table->decimal('feb', 10, 2)->nullable();
            $table->decimal('mar', 10, 2)->nullable();
            $table->decimal('apr', 10, 2)->nullable();
            $table->decimal('may', 10, 2)->nullable();
            $table->decimal('jun', 10, 2)->nullable();
            $table->decimal('jul', 10, 2)->nullable();
            $table->decimal('aug', 10, 2)->nullable();
            $table->decimal('sep', 10, 2)->nullable();
            $table->decimal('oct', 10, 2)->nullable();
            $table->decimal('nov', 10, 2)->nullable();
            $table->decimal('dec', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('grade_wise_monthly_leave_details', function (Blueprint $table): void {
            $table->dropColumn([
                'jan',
                'feb',
                'mar',
                'apr',
                'may',
                'jun',
                'jul',
                'aug',
                'sep',
                'oct',
                'nov',
                'dec',
            ]);
        });
    }
};
