<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\AttendanceLogWriter;
use App\Models\Tenant\AttendanceLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceLogWriterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('referance_type');
            $table->unsignedBigInteger('referance_id');
            $table->string('user_code')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->unsignedTinyInteger('punch_type')->nullable();
            $table->boolean('is_out')->default(false);
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->boolean('is_staged')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('attendance_logs');

        parent::tearDown();
    }

    public function test_existing_locked_log_is_not_updated(): void
    {
        $id = DB::table('attendance_logs')->insertGetId([
            'user_id' => 9,
            'referance_type' => 'manual_punch',
            'referance_id' => 15,
            'user_code' => 'EMP009',
            'datetime' => '2026-10-20 09:00:00',
            'punch_type' => 1,
            'is_locked' => true,
        ]);

        $updated = AttendanceLogWriter::updateUnlessLocked(
            ['user_id' => 9, 'referance_type' => 'manual_punch', 'referance_id' => 15],
            ['datetime' => '2026-10-20 10:00:00', 'punch_type' => 2],
        );

        $this->assertFalse($updated);
        $this->assertSame('2026-10-20 09:00:00', DB::table('attendance_logs')->where('id', $id)->value('datetime'));
        $this->assertSame(1, DB::table('attendance_logs')->where('id', $id)->value('punch_type'));
        $this->assertSame(1, AttendanceLog::query()->count());
    }

    public function test_existing_unlocked_log_is_updated(): void
    {
        DB::table('attendance_logs')->insert([
            'user_id' => 9,
            'referance_type' => 'manual_attendance_start',
            'referance_id' => 15,
            'user_code' => 'EMP009',
            'datetime' => '2026-10-20 09:00:00',
            'punch_type' => 1,
            'is_locked' => false,
        ]);

        $updated = AttendanceLogWriter::updateUnlessLocked(
            ['user_id' => 9, 'referance_type' => 'manual_attendance_start', 'referance_id' => 15],
            ['datetime' => '2026-10-20 10:00:00', 'punch_type' => 2],
        );

        $this->assertTrue($updated);
        $this->assertSame('2026-10-20 10:00:00', DB::table('attendance_logs')->value('datetime'));
        $this->assertSame(2, DB::table('attendance_logs')->value('punch_type'));
        $this->assertSame(1, AttendanceLog::query()->count());
    }

    public function test_missing_log_is_created(): void
    {
        $created = AttendanceLogWriter::updateUnlessLocked(
            ['user_id' => 9, 'referance_type' => 'manual_punch', 'referance_id' => 15],
            ['user_code' => 'EMP009', 'datetime' => '2026-10-20 09:00:00', 'punch_type' => 1],
        );

        $this->assertTrue($created);
        $this->assertSame(1, AttendanceLog::query()->count());
        $this->assertFalse(AttendanceLog::query()->firstOrFail()->is_locked);
    }
}
