<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_rules', function (Blueprint $table) {
            $table->decimal('ignore_overtime', 8, 2)->default(0)->change();
            $table->decimal('ignore_late_going_overtime', 8, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('overtime_rules', function (Blueprint $table) {
            $table->boolean('ignore_overtime')->default(false)->change();
            $table->boolean('ignore_late_going_overtime')->default(false)->change();
        });
    }
};
