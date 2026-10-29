<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1 || Schema::hasColumn($tableName, 'work_time')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedInteger('work_time')->default(0);
            });

            Schema::getConnection()->table($tableName)
                ->select(['id', 'in_time', 'out_time'])
                ->orderBy('id')
                ->chunkById(500, function ($attendances) use ($tableName): void {
                    foreach ($attendances as $attendance) {
                        $workTime = $attendance->in_time !== null && $attendance->out_time !== null
                            && Carbon::parse($attendance->out_time)->greaterThan(Carbon::parse($attendance->in_time))
                            ? (int) Carbon::parse($attendance->in_time)->diffInMinutes(Carbon::parse($attendance->out_time))
                            : 0;

                        Schema::getConnection()->table($tableName)
                            ->where('id', $attendance->id)
                            ->update(['work_time' => $workTime]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach (Schema::getTableListing(null, false) as $tableName) {
            if (preg_match('/\Aattendances\d{4}\z/i', $tableName) !== 1 || ! Schema::hasColumn($tableName, 'work_time')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('work_time');
            });
        }
    }
};
