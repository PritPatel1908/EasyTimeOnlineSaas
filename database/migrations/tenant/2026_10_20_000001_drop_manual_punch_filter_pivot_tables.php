<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'manual_punch_area' => 'area_id',
        'manual_punch_designation' => 'designation_id',
        'manual_punch_sub_category' => 'sub_category_id',
        'manual_punch_category' => 'category_id',
        'manual_punch_sub_department' => 'sub_department_id',
        'manual_punch_department' => 'department_id',
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
                $blueprint->foreignId('manual_punch_id')->constrained('manual_punches');
                $blueprint->foreignId($relatedId)->constrained();
                $blueprint->timestamps();
            });
        }
    }
};
