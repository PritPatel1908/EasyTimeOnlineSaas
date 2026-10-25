<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveAccountUniqueUserMigrationTest extends TestCase
{
    public function test_leave_accounts_allows_only_one_record_per_user(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('leave_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->noActionOnDelete();
            $table->string('code')->nullable();
            $table->timestamps();
        });

        $migration = require base_path('database/migrations/tenant/2026_10_25_100000_add_unique_user_id_to_leave_accounts_table.php');
        $migration->up();

        DB::table('users')->insert(['id' => 1]);
        DB::table('leave_accounts')->insert(['user_id' => 1]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('leave_accounts')->insert(['user_id' => 1]);
    }
}
