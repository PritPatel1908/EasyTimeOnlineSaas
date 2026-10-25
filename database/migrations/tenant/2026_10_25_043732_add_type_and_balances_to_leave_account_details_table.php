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
        Schema::table('leave_account_details', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->decimal('credit_balance', 10, 2)->default(0);
            $table->decimal('debit_balance', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_account_details', function (Blueprint $table) {
            $table->dropColumn(['type', 'type_id', 'credit_balance', 'debit_balance']);
        });
    }
};
