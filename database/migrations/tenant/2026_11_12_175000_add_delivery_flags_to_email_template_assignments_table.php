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
        Schema::table('email_template_assignments', function (Blueprint $table) {
            $table->boolean('send_to_approver')->default(false)->after('status');
            $table->boolean('send_to_another_approver')->default(false)->after('send_to_approver');
            $table->boolean('send_to_employee')->default(false)->after('send_to_another_approver');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_template_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'send_to_approver',
                'send_to_another_approver',
                'send_to_employee',
            ]);
        });
    }
};
