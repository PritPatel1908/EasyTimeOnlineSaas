<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('grade_wise_leaves', 'financial_year_id')) {
            return;
        }

        Schema::table('grade_wise_leaves', function (Blueprint $table): void {
            $table->dropForeign(['financial_year_id']);
            $table->dropColumn('financial_year_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('grade_wise_leaves', 'financial_year_id')) {
            return;
        }

        Schema::table('grade_wise_leaves', function (Blueprint $table): void {
            $table->foreignId('financial_year_id')->nullable()->constrained('financial_years');
        });
    }
};
