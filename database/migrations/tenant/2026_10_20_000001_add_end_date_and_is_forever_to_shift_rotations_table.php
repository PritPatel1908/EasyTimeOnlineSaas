<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_rotations', function (Blueprint $table): void {
            $table->date('end_date')->nullable()->after('start_date');
            $table->boolean('is_forever')->default(false)->after('end_date');
        });

        DB::table('shift_rotations')
            ->whereNull('end_date')
            ->update(['is_forever' => true]);
    }

    public function down(): void
    {
        Schema::table('shift_rotations', function (Blueprint $table): void {
            $table->dropColumn(['end_date', 'is_forever']);
        });
    }
};
