<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_infos', function (Blueprint $table): void {
            $table->longText('emergency_name')->nullable()->change();
            $table->longText('emergency_number')->nullable()->change();
            $table->longText('emergency_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personal_infos', function (Blueprint $table): void {
            $table->string('emergency_name')->nullable()->change();
            $table->string('emergency_number')->nullable()->change();
            $table->string('emergency_address')->nullable()->change();
        });
    }
};
