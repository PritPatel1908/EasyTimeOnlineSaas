<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant\LeaveApplication;
use App\Services\Tenant\LeaveAttendanceWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveAttendanceWriterTest extends TestCase
{
    public function test_final_approved_leave_writes_minimal_attendance_to_the_dynamic_year_tables(): void
    {
        config([
            'database.connections.central.driver' => 'sqlite',
            'database.connections.central.database' => ':memory:',
            'database.connections.central.url' => null,
        ]);
        DB::purge('central');

        $this->createTables();

        DB::table('users')->insert(['id' => 7]);
        DB::table('leave_types')->insert(['id' => 3, 'code' => 'PL']);
        DB::table('leave_applications')->insert([
            'id' => 12,
            'leave_type_id' => 3,
            'from_date' => '2026-12-31',
            'to_date' => '2027-01-01',
            'is_only_half' => false,
            'is_second_half' => true,
            'is_first_half' => true,
        ]);
        DB::table('leave_application_users')->insert([
            'leave_application_id' => 12,
            'user_id' => 7,
        ]);
        DB::table('status_masters')->insert([
            ['id' => 1, 'code' => 'APL'],
            ['id' => 2, 'code' => 'PLA'],
        ]);

        $application = LeaveApplication::query()->findOrFail(12);
        $writer = app(LeaveAttendanceWriter::class);

        $writer->createForApprovedApplication($application);
        $writer->createForApprovedApplication($application);

        $firstDay = DB::table('attendances2026')->whereDate('date', '2026-12-31')->first();
        $lastDay = DB::table('attendances2027')->whereDate('date', '2027-01-01')->first();

        $this->assertNotNull($firstDay);
        $this->assertSame(1, (int) $firstDay->status_master_id);
        $this->assertNull($firstDay->location_id);
        $this->assertNull($firstDay->company_id);
        $this->assertNull($firstDay->shift_id);
        $this->assertNotNull($lastDay);
        $this->assertSame(2, (int) $lastDay->status_master_id);
        $this->assertSame(1, DB::table('attendances2026')->count());
        $this->assertSame(1, DB::table('attendances2027')->count());
    }

    private function createTables(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->softDeletes();
        });
        Schema::create('leave_types', function ($table): void {
            $table->id();
            $table->string('code');
            $table->softDeletes();
        });
        Schema::create('leave_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('leave_type_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->boolean('is_only_half');
            $table->boolean('is_second_half');
            $table->boolean('is_first_half');
            $table->softDeletes();
        });
        Schema::create('leave_application_users', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('leave_application_id');
            $table->unsignedBigInteger('user_id');
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
