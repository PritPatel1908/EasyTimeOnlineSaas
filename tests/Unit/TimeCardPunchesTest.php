<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Tenant\AttendanceLogController;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class TimeCardPunchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('attendances2026');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('manual_punches');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable();
        });
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_code')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->unsignedBigInteger('dms_area_id')->nullable();
            $table->string('area_name')->nullable();
            $table->unsignedInteger('punch_type')->nullable();
            $table->unsignedInteger('verify_type')->nullable();
            $table->string('referance_type')->nullable();
            $table->unsignedBigInteger('referance_id')->nullable();
            $table->boolean('is_out')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->boolean('has_error')->default(false);
            $table->boolean('is_staged')->default(false);
            $table->boolean('is_calculated')->default(false);
            $table->softDeletes();
        });
        Schema::create('attendances2026', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->string('remarks')->nullable();
        });
        Schema::create('machines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->string('name')->nullable();
            $table->string('machine_serial_number')->nullable();
        });
        Schema::create('areas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dms_area_id')->nullable();
            $table->string('name')->nullable();
        });
        Schema::create('manual_punches', function (Blueprint $table): void {
            $table->id();
            $table->string('reason')->nullable();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('attendances2026');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('machines');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('manual_punches');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_clicking_attendance_date_returns_employee_punches_in_time_order_within_attendance_bounds(): void
    {
        DB::table('users')->insert([
            ['id' => 7, 'code' => 'EMP007'],
            ['id' => 8, 'code' => 'EMP008'],
        ]);
        DB::table('attendances2026')->insert([
            'id' => 108,
            'user_id' => 7,
            'date' => '2026-11-10',
            'in_time' => '2026-11-10 08:00:00',
            'out_time' => '2026-11-10 21:00:00',
        ]);
        DB::table('attendance_logs')->insert([
            ['id' => 1, 'user_id' => 7, 'user_code' => 'EMP007', 'datetime' => '2026-11-10 07:59:59', 'is_out' => false],
            ['id' => 2, 'user_id' => 7, 'user_code' => 'EMP007', 'datetime' => '2026-11-10 08:00:00', 'is_out' => false],
            ['id' => 3, 'user_id' => null, 'user_code' => 'EMP007', 'datetime' => '2026-11-10 12:00:00', 'is_out' => true],
            ['id' => 4, 'user_id' => 8, 'user_code' => 'EMP008', 'datetime' => '2026-11-10 13:00:00', 'is_out' => true],
            ['id' => 5, 'user_id' => 7, 'user_code' => 'EMP007', 'datetime' => '2026-11-10 21:00:00', 'is_out' => true],
            ['id' => 6, 'user_id' => 7, 'user_code' => 'EMP007', 'datetime' => '2026-11-10 21:00:01', 'is_out' => false],
        ]);

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(1);
        $guard->shouldReceive('user')->andReturn(new User);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(AttendanceLogController::class)->filterTimeCard(new Request([
            'year' => '2026',
            'punch_attendance_id' => '108',
        ]));
        $html = $response->getData(true)['html'];

        $this->assertSame(200, $response->getStatusCode());
        $this->assertLessThan(strpos($html, '#3'), strpos($html, '#2'));
        $this->assertLessThan(strpos($html, '#5'), strpos($html, '#3'));
        $this->assertStringNotContainsString('#1</td>', $html);
        $this->assertStringNotContainsString('#4</td>', $html);
        $this->assertStringNotContainsString('#6</td>', $html);
    }

    public function test_employee_cannot_view_another_employees_time_card_punches(): void
    {
        DB::table('users')->insert([
            ['id' => 7, 'code' => 'EMP007'],
            ['id' => 8, 'code' => 'EMP008'],
        ]);
        DB::table('attendances2026')->insert([
            'id' => 108,
            'user_id' => 8,
            'date' => '2026-11-10',
            'in_time' => '2026-11-10 08:00:00',
            'out_time' => '2026-11-10 17:00:00',
        ]);
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('super_admin')->andReturn(false);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(7);
        $guard->shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $this->expectException(NotFoundHttpException::class);

        app(AttendanceLogController::class)->filterTimeCard(new Request([
            'year' => '2026',
            'punch_attendance_id' => '108',
        ]));
    }

    public function test_punch_list_includes_device_source_reference_and_manual_reason(): void
    {
        DB::table('users')->insert(['id' => 7, 'code' => 'EMP007']);
        DB::table('attendances2026')->insert([
            'id' => 108,
            'user_id' => 7,
            'date' => '2026-11-10',
            'in_time' => '2026-11-10 08:00:00',
            'out_time' => '2026-11-10 21:00:00',
            'remarks' => 'Attendance was reviewed',
        ]);
        DB::table('machines')->insert([
            'dms_device_id' => 22,
            'name' => 'Main Gate Device',
            'machine_serial_number' => 'SN-2200',
        ]);
        DB::table('areas')->insert([
            'dms_area_id' => 5,
            'name' => 'Main Gate',
        ]);
        DB::table('manual_punches')->insert([
            'id' => 52,
            'reason' => 'Forgot to punch',
        ]);
        DB::table('attendance_logs')->insert([
            [
                'id' => 1,
                'user_id' => 7,
                'user_code' => 'EMP007',
                'datetime' => '2026-11-10 08:00:00',
                'dms_device_id' => 22,
                'dms_area_id' => 5,
                'punch_type' => 1,
                'verify_type' => 15,
                'referance_type' => null,
                'referance_id' => null,
                'is_manual' => false,
                'is_calculated' => true,
            ],
            [
                'id' => 2,
                'user_id' => 7,
                'user_code' => 'EMP007',
                'datetime' => '2026-11-10 21:00:00',
                'dms_device_id' => null,
                'dms_area_id' => null,
                'punch_type' => 2,
                'verify_type' => null,
                'referance_type' => ManualPunch::class,
                'referance_id' => 52,
                'is_manual' => true,
                'is_calculated' => false,
            ],
        ]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(1);
        $guard->shouldReceive('user')->andReturn(new User);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(AttendanceLogController::class)->filterTimeCard(new Request([
            'year' => '2026',
            'punch_attendance_id' => '108',
        ]));
        $html = $response->getData(true)['html'];

        $this->assertStringContainsString('Main Gate Device', $html);
        $this->assertStringContainsString('SN: SN-2200', $html);
        $this->assertStringContainsString('Main Gate', $html);
        $this->assertStringContainsString('Verify: 15', $html);
        $this->assertStringContainsString('Manual Punch', $html);
        $this->assertStringContainsString('Forgot to punch', $html);
        $this->assertStringContainsString('Attendance was reviewed', $html);
    }
}
