<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'manual_attendance_area' => 'area_id',
        'manual_attendance_designation' => 'designation_id',
        'manual_attendance_sub_category' => 'sub_category_id',
        'manual_attendance_category' => 'category_id',
        'manual_attendance_sub_department' => 'sub_department_id',
        'manual_attendance_department' => 'department_id',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (array_keys(self::TABLES) as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::TABLES as $table => $relatedId) {
            Schema::create($table, function (Blueprint $blueprint) use ($relatedId) {
                $blueprint->id();
                $blueprint->foreignId('manual_attendance_id')->constrained('manual_attendances');
                $blueprint->foreignId($relatedId)->constrained();
                $blueprint->timestamps();
            });
        }
    }
};
