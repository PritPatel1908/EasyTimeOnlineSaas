<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateUserIds = DB::table('leave_accounts')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        if ($duplicateUserIds->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot add the unique leave account user constraint. Duplicate user IDs: '.$duplicateUserIds->implode(', '),
            );
        }

        Schema::table('leave_accounts', function (Blueprint $table): void {
            $table->unique('user_id', 'leave_accounts_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('leave_accounts', function (Blueprint $table): void {
            $table->dropUnique('leave_accounts_user_id_unique');
        });
    }
};
