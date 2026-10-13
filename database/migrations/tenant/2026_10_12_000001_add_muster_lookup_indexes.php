<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_musters', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'shift_musters_user_date_index');
        });

        Schema::table('week_off_musters', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'week_off_musters_user_date_index');
        });

        Schema::table('rotation_musters', function (Blueprint $table) {
            $table->index(['rotation_id', 'date'], 'rotation_musters_rotation_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('shift_musters', function (Blueprint $table) {
            $table->dropIndex('shift_musters_user_date_index');
        });

        Schema::table('week_off_musters', function (Blueprint $table) {
            $table->dropIndex('week_off_musters_user_date_index');
        });

        Schema::table('rotation_musters', function (Blueprint $table) {
            $table->dropIndex('rotation_musters_rotation_date_index');
        });
    }
};
