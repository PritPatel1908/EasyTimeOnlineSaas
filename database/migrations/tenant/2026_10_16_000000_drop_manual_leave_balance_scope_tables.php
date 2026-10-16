<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('manual_leave_balance_area');
        Schema::dropIfExists('manual_leave_balance_category');
        Schema::dropIfExists('manual_leave_balance_department');
        Schema::dropIfExists('manual_leave_balance_designation');
        Schema::dropIfExists('manual_leave_balance_sub_category');
        Schema::dropIfExists('manual_leave_balance_sub_department');
    }

    public function down(): void
    {
        // These tables are intentionally retired and must not be recreated.
    }
};
