<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_licenses', function (Blueprint $table): void {
            $table->text('license_key')->nullable()->after('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('company_licenses', function (Blueprint $table): void {
            $table->dropColumn('license_key');
        });
    }
};
