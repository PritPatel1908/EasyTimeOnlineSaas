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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_eligible_for_c_off')->default(false);
            $table->integer('c_off_lapse_in_days')->nullable();
            $table->boolean('allow_halfday_c_off')->default(false);
            $table->boolean('allow_backdated_leave')->default(false);
            $table->integer('backdated_day_limit')->nullable();
            $table->integer('advance_day_limit')->nullable();
            $table->integer('maximum_accumulation')->nullable();
            $table->integer('maximum_request_in_a_month')->nullable();
            $table->integer('maximum_request_in_a_year')->nullable();
            $table->foreignId('leave_type_id')->nullable()->constrained('leave_types');
            $table->decimal('min_avail', 10, 2)->nullable();
            $table->decimal('max_avail', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_eligible_for_c_off');
            $table->dropColumn('c_off_lapse_in_days');
            $table->dropColumn('allow_halfday_c_off');
            $table->dropColumn('allow_backdated_leave');
            $table->dropColumn('backdated_day_limit');
            $table->dropColumn('advance_day_limit');
            $table->dropColumn('maximum_accumulation');
            $table->dropColumn('maximum_request_in_a_month');
            $table->dropColumn('maximum_request_in_a_year');
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
            $table->dropColumn('min_avail');
            $table->dropColumn('max_avail');
        });
    }
};
