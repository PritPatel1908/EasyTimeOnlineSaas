<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sub_departments', 'status')) {
            Schema::table('sub_departments', function (Blueprint $table): void {
                $table->tinyInteger('status')->default(1)->after('department_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sub_departments', 'status')) {
            Schema::table('sub_departments', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }
    }
};
