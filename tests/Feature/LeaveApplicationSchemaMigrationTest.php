<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveApplicationSchemaMigrationTest extends TestCase
{
    public function test_migration_moves_user_to_pivot_and_renames_half_day_column(): void
    {
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });

        Schema::create('leave_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->boolean('is_only_second_half')->default(false);
        });

        Schema::create('leave_application_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_application_id')->constrained('leave_applications');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 1]);
        DB::table('leave_applications')->insert([
            'id' => 1,
            'user_id' => 1,
            'is_only_second_half' => true,
        ]);

        $migration = require base_path('database/migrations/tenant/2026_10_25_080000_update_leave_applications_table.php');
        $migration->up();

        $this->assertFalse(Schema::hasColumn('leave_applications', 'user_id'));
        $this->assertTrue(Schema::hasColumn('leave_applications', 'is_only_half'));
        $this->assertFalse(Schema::hasColumn('leave_applications', 'is_only_second_half'));
        $this->assertDatabaseHas('leave_application_users', [
            'leave_application_id' => 1,
            'user_id' => 1,
        ]);
        $this->assertTrue((bool) DB::table('leave_applications')->value('is_only_half'));

        $migration->down();

        $this->assertTrue(Schema::hasColumn('leave_applications', 'user_id'));
        $this->assertTrue(Schema::hasColumn('leave_applications', 'is_only_second_half'));
        $this->assertFalse(Schema::hasColumn('leave_applications', 'is_only_half'));
        $this->assertSame(1, DB::table('leave_applications')->value('user_id'));
    }
}
