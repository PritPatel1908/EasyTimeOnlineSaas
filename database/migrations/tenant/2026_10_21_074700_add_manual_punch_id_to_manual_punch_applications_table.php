<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_punch_applications', function (Blueprint $table) {
            $table->foreignId('manual_punch_id')
                ->nullable()
                ->after('id')
                ->constrained('manual_punches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('manual_punch_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manual_punch_id');
        });
    }
};
