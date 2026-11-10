<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_slabs', function (Blueprint $table): void {
            $table->string('value_from', 32)->nullable()->change();
            $table->string('value_to', 32)->nullable()->change();
            $table->string('head_value', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('overtime_slabs')->get(['value_from', 'value_to', 'head_value']) as $slab) {
            foreach ([$slab->value_from, $slab->value_to, $slab->head_value] as $duration) {
                if (is_string($duration)
                    && preg_match('/\A(\d{1,3}):([0-5]\d)/', $duration, $matches) === 1
                    && (int) $matches[1] >= 24) {
                    throw new RuntimeException('Overtime slab durations of 24 hours or more cannot be converted back to SQL TIME columns.');
                }
            }
        }

        Schema::table('overtime_slabs', function (Blueprint $table): void {
            $table->time('value_from')->nullable()->change();
            $table->time('value_to')->nullable()->change();
            $table->time('head_value')->nullable()->change();
        });
    }
};
