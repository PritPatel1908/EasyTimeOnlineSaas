<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('late_coming_rules', 'status')) {
            Schema::table('late_coming_rules', function (Blueprint $table): void {
                $table->unsignedTinyInteger('status')->default(1)->after('ignore_late_coming_minutes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('late_coming_rules', 'status')) {
            Schema::table('late_coming_rules', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }
    }
};
