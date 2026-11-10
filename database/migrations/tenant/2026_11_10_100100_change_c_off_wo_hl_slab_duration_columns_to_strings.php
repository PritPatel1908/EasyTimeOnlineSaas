<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('c_off_against_wo_hl_slabs', function (Blueprint $table): void {
            $table->string('from_time', 32)->nullable()->change();
            $table->string('to_time', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('c_off_against_wo_hl_slabs')->get(['from_time', 'to_time']) as $slab) {
            foreach ([$slab->from_time, $slab->to_time] as $duration) {
                if (is_string($duration)
                    && preg_match('/\A(\d{1,3}):([0-5]\d)/', $duration, $matches) === 1
                    && (int) $matches[1] >= 24) {
                    throw new RuntimeException('C-Off WO/HL slab durations of 24 hours or more cannot be converted back to SQL TIME columns.');
                }
            }
        }

        Schema::table('c_off_against_wo_hl_slabs', function (Blueprint $table): void {
            $table->time('from_time')->nullable()->change();
            $table->time('to_time')->nullable()->change();
        });
    }
};
