<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_musters', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'date', 'is_locked'],
                'status_musters_user_date_locked_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('status_musters', function (Blueprint $table): void {
            $table->dropIndex('status_musters_user_date_locked_index');
        });
    }
};
