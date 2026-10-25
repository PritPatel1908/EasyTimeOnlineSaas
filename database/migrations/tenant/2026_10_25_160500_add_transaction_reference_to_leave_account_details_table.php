<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_account_details', function (Blueprint $table): void {
            $table->string('transaction_year', 4)->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->index(
                ['leave_account_id', 'leave_type_id', 'transaction_year', 'transaction_id'],
                'leave_account_detail_transaction_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('leave_account_details', function (Blueprint $table): void {
            $table->dropIndex('leave_account_detail_transaction_idx');
            $table->dropColumn(['transaction_year', 'transaction_id']);
        });
    }
};
