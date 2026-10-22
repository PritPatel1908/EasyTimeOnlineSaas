<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'shift_change_area' => 'area_id',
        'shift_change_designation' => 'designation_id',
        'shift_change_sub_category' => 'sub_category_id',
        'shift_change_category' => 'category_id',
        'shift_change_sub_department' => 'sub_department_id',
        'shift_change_department' => 'department_id',
    ];

    public function up(): void
    {
        foreach (array_keys(self::TABLES) as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $relatedId) {
            Schema::create($table, function (Blueprint $blueprint) use ($relatedId) {
                $blueprint->id();
                $blueprint->foreignId('shift_change_id')->constrained('shift_changes');
                $blueprint->foreignId($relatedId)->constrained();
                $blueprint->timestamps();
            });
        }
    }
};
