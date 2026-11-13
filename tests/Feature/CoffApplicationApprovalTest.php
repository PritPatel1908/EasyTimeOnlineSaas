<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Models\Tenant\CoffApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoffApplicationApprovalTest extends TestCase
{
    public function test_final_approval_updates_application_and_writes_each_date_with_the_correct_coff_status(): void
    {
        config([
            'database.connections.central.driver' => 'sqlite',
            'database.connections.central.database' => ':memory:',
            'database.connections.central.url' => null,
        ]);
        DB::purge('central');

        $this->createTables();
        DB::table('users')->insert(['id' => 7]);
        DB::table('status_masters')->insert([
            ['id' => 1, 'code' => 'CO'],
            ['id' => 2, 'code' => 'COA'],
            ['id' => 3, 'code' => 'ACO'],
        ]);
        DB::table('coff_applications')->insert([
            'id' => 12,
            'user_id' => 7,
            'coff_against_date' => '2026-12-01',
            'coff_from_date' => '2026-12-31',
            'coff_to_date' => '2027-01-02',
            'is_half_day' => false,
            'is_first_half' => true,
            'is_second_half' => true,
            'status' => 'pending',
        ]);
        DB::table('approval_statuses')->insert([
            'id' => 20,
            'approvable_type' => CoffApplication::class,
            'approvable_id' => 12,
            'approval_status' => 'approved',
        ]);

        (new FinalizeApprovedApplication(CoffApplication::class, 12))->handle();

        $this->assertSame('approved', DB::table('coff_applications')->where('id', 12)->value('status'));
        $firstDate = DB::table('attendances2026')->whereDate('date', '2026-12-31')->first();
        $middleDate = DB::table('attendances2027')->whereDate('date', '2027-01-01')->first();
        $lastDate = DB::table('attendances2027')->whereDate('date', '2027-01-02')->first();
        $this->assertNotNull($firstDate);
        $this->assertNotNull($middleDate);
        $this->assertNotNull($lastDate);
        $this->assertSame(3, (int) $firstDate->status_master_id);
        $this->assertSame(1, (int) $middleDate->status_master_id);
        $this->assertSame(2, (int) $lastDate->status_master_id);

        (new FinalizeApprovedApplication(CoffApplication::class, 12))->handle();
        $this->assertSame(1, DB::table('attendances2026')->count());
        $this->assertSame(2, DB::table('attendances2027')->count());
    }

    private function createTables(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->softDeletes();
        });
        Schema::create('coff_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('coff_against_date');
            $table->date('coff_from_date');
            $table->date('coff_to_date');
            $table->boolean('is_half_day');
            $table->boolean('is_first_half');
            $table->boolean('is_second_half');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_statuses', function ($table): void {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->string('approval_status');
        });
        Schema::create('approved_application_effect_snapshots', function ($table): void {
            $table->id();
            $table->morphs('approvable');
            $table->json('snapshot');
            $table->timestamps();
        });
        Schema::create('status_masters', function ($table): void {
            $table->id();
            $table->string('code');
        });
        foreach (['locations', 'companies', 'shifts', 'areas', 'attendance_logs'] as $tableName) {
            Schema::create($tableName, function ($table): void {
                $table->id();
            });
        }
    }
}
