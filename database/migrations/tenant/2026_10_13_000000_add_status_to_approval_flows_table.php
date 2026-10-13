<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_flows', function (Blueprint $table): void {
            $table->unsignedTinyInteger('status')->default(1)->after('approval_flow_code');
        });
    }

    public function down(): void
    {
        Schema::table('approval_flows', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
