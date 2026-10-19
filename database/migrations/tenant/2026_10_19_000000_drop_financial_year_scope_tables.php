<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('financial_year_users');
        Schema::dropIfExists('financial_year_sub_department');
        Schema::dropIfExists('financial_year_sub_category');
        Schema::dropIfExists('financial_year_designation');
        Schema::dropIfExists('financial_year_department');
        Schema::dropIfExists('financial_year_category');
        Schema::dropIfExists('financial_year_area');
    }

    public function down(): void
    {
        throw new \LogicException('The financial-year scope tables are intentionally not recreated.');
    }
};
