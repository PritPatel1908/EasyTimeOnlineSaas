<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_rules', function (Blueprint $table) {
            $table->dropColumn([
                'ignore_early_come',
                'ignore_late_come',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('overtime_rules', function (Blueprint $table) {
            $table->boolean('ignore_early_come')->default(false);
            $table->boolean('ignore_late_come')->default(false);
        });
    }
};
