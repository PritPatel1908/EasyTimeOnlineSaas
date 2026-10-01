<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        DB::table('blood_groups')->insert(array_map(
            static fn(string $group): array => [
                'code' => str_replace(['+', '-'], ['_POS', '_NEG'], $group),
                'name' => $group,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
        ));

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('blood_group_id')->nullable()->constrained('blood_groups');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blood_group_id']);
            $table->dropColumn('blood_group_id');
        });

        Schema::dropIfExists('blood_groups');
    }
};
