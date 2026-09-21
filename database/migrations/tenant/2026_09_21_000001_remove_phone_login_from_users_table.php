<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'allow_phone_login')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('allow_phone_login');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'allow_phone_login')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('allow_phone_login')->nullable()->default(false);
            });
        }
    }
};
