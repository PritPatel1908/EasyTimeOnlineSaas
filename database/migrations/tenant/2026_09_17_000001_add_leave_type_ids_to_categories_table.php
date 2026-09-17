<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['leave_type_id']);
            $table->json('leave_type_id')->nullable()->change();
        });

        DB::table('categories')
            ->whereNotNull('leave_type_id')
            ->orderBy('id')
            ->eachById(function (object $category): void {
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['leave_type_id' => json_encode([(int) $category->leave_type_id])]);
            });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('leave_type_id')->nullable()->change();
            $table->foreign('leave_type_id')->references('id')->on('leave_types');
        });
    }
};
