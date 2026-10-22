<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_rotation_id');
            $table->dropColumn('shift_type');
            $table->dropConstrainedForeignId('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('manual_attendances', function (Blueprint $table) {
            $table->foreignId('shift_rotation_id')->nullable()->constrained();
            $table->string('shift_type')->nullable();
            $table->foreignId('shift_id')->nullable()->constrained();
        });
    }
};
