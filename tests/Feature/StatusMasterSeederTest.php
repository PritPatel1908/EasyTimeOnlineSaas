<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\Tenant\StatusMasterSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StatusMasterSeederTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        config([
            'database.connections.status_master_seeder_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'status_master_seeder_test',
        ]);
        DB::purge('status_master_seeder_test');

        Schema::create('leave_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('description')->nullable();
            $table->softDeletes();
        });
        Schema::create('status_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('alias');
            $table->string('description');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('status_master_seeder_test');
        config(['database.default' => $this->originalDefaultConnection]);
        config()->offsetUnset('database.connections.status_master_seeder_test');

        parent::tearDown();
    }

    public function test_it_seeds_all_half_day_status_codes_for_each_leave_type_idempotently(): void
    {
        DB::table('leave_types')->insert([
            ['code' => 'SL', 'description' => 'Sick leave'],
            ['code' => 'L1', 'description' => 'Custom leave'],
        ]);

        (new StatusMasterSeeder)->run();
        (new StatusMasterSeeder)->run();

        $this->assertSame(
            [
                'AHL',
                'AL1',
                'ASL',
                'AWO',
                'HLA',
                'HLP',
                'L1A',
                'L1P',
                'PHL',
                'PL1',
                'PSL',
                'PWO',
                'SLA',
                'SLP',
                'WOA',
                'WOP',
            ],
            DB::table('status_masters')->orderBy('code')->pluck('code')->all(),
        );
        $this->assertSame(
            'AL1',
            DB::table('status_masters')->where('code', 'AL1')->value('alias'),
        );
        $this->assertSame(
            'Custom leave',
            DB::table('status_masters')->where('code', 'AL1')->value('description'),
        );
    }
}
