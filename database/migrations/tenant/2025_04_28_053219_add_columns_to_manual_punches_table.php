<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('manual_punches', function (Blueprint $table) {
            $table->dropColumn('punch_date');
            $table->dropForeign(['location_id']);
            $table->dropForeign(['company_id']);
            $table->dropColumn('location_id');
            $table->dropColumn('company_id');
            $table->json('location_id')->nullable()->after('punch_type');
            $table->json('company_id')->nullable()->after('location_id');
            $table->date('from_punch_date')->after('punch_type');
            $table->date('to_punch_date')->after('from_punch_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_punches', function (Blueprint $table) {
            $table->dropColumn('from_punch_date');
            $table->dropColumn('to_punch_date');
            $table->date('punch_date')->after('punch_type');
            $table->dropColumn('location_id');
            $table->dropColumn('company_id');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade')->after('punch_type');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade')->after('location_id');
        });
    }
};
