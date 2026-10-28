<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Attendance\AttCalculator;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\Shift;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\User;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProcessPendingLogsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('general_configurations', function ($table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('shifts', function ($table): void {
            $table->id();
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('set_cutoff')->default(false);
            $table->time('cutoff_time')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
        Schema::create('shift_musters', function ($table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('calculated_shift')->nullable();
            $table->timestamps();
        });
        Schema::create('status_masters', function ($table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('alias')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('status_musters', function ($table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('status_master_id')->nullable();
            $table->float('day_count');
            $table->unsignedInteger('year');
            $table->boolean('is_locked');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('status_musters');
        Schema::dropIfExists('status_masters');
        Schema::dropIfExists('shift_musters');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('general_configurations');

        parent::tearDown();
    }

    public function test_job_serializes_attendance_log_id_without_restoring_tenant_model(): void
    {
        $log = new AttendanceLog;
        $log->setAttribute('id', 42);

        $serializedJob = serialize(new ProcessPendingLogs($log));
        $restoredJob = unserialize($serializedJob);

        $this->assertStringNotContainsString(ModelIdentifier::class, $serializedJob);
        $this->assertSame('42', $restoredJob->uniqueId());
    }

    public function test_manual_punch_direction_does_not_require_a_machine(): void
    {
        foreach ([false, true] as $isOut) {
            $log = new AttendanceLog;
            $log->setAttribute('is_manual', true);
            $log->setAttribute('is_out', $isOut);

            $calculator = new AttCalculator($log);
            $calculator->checkInOrOut();

            $this->assertSame($isOut, $log->is_out);
        }
    }

    public function test_cutoff_duration_keeps_day_shift_punches_in_previous_shift_window(): void
    {
        $shift = Shift::create([
            'in_time' => '08:00:00',
            'out_time' => '20:00:00',
            'set_cutoff' => true,
            'cutoff_time' => '24:00:00',
            'is_night_shift' => false,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 7,
            'date' => '2026-01-01',
            'is_locked' => false,
            'calculated_shift' => $shift->id,
        ]);

        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-02 02:00:00',
            false,
            '2026-01-02 08:00:00',
        );
        $calculator->checkInOrOut();

        $this->assertTrue($this->getCalculatorLog($calculator)->is_out, implode(PHP_EOL, $calculator->getReturnLogs()));
        $this->assertSame('2026-01-01', (new \ReflectionProperty(AttCalculator::class, 'punchAttendanceDate'))->getValue($calculator));
    }

    public function test_cutoff_duration_also_keeps_night_shift_punches_in_previous_shift_window(): void
    {
        $shift = Shift::create([
            'in_time' => '20:00:00',
            'out_time' => '08:00:00',
            'set_cutoff' => true,
            'cutoff_time' => '24:00:00',
            'is_night_shift' => true,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 8,
            'date' => '2026-01-01',
            'is_locked' => false,
            'calculated_shift' => $shift->id,
        ]);

        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-02 02:00:00',
            false,
            '2026-01-02 07:00:00',
            8,
        );
        $calculator->checkInOrOut();

        $this->assertTrue($this->getCalculatorLog($calculator)->is_out);
        $this->assertSame('2026-01-01', (new \ReflectionProperty(AttCalculator::class, 'punchAttendanceDate'))->getValue($calculator));
    }

    public function test_maximum_gap_classifies_a_later_punch_as_out_within_configured_duration(): void
    {
        GeneralConfiguration::create([
            'key' => 'max_gap_btwn_prv_in_and_curtt_out',
            'value' => '24:00',
        ]);
        $shift = Shift::create([
            'in_time' => '08:00:00',
            'out_time' => '20:00:00',
            'set_cutoff' => false,
            'is_night_shift' => false,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 7,
            'date' => '2026-01-01',
            'is_locked' => false,
            'calculated_shift' => $shift->id,
        ]);

        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-01 08:00:00',
            false,
            '2026-01-02 07:59:00',
        );
        $calculator->checkInOrOut();

        $this->assertTrue($this->getCalculatorLog($calculator)->is_out, implode(PHP_EOL, $calculator->getReturnLogs()));
        $this->assertSame('2026-01-01', (new \ReflectionProperty(AttCalculator::class, 'punchAttendanceDate'))->getValue($calculator));
    }

    public function test_minimum_gap_keeps_a_quick_punch_as_out_and_accepts_in_after_gap(): void
    {
        GeneralConfiguration::create([
            'key' => 'min_gap_btwn_prv_out_and_curtt_in',
            'value' => '04:00',
        ]);
        $shift = Shift::create([
            'in_time' => '08:00:00',
            'out_time' => '20:00:00',
            'set_cutoff' => false,
            'is_night_shift' => false,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 7,
            'date' => '2026-01-01',
            'is_locked' => false,
            'calculated_shift' => $shift->id,
        ]);

        $tooSoon = $this->calculatorWithPreviousPunch(
            '2026-01-01 20:00:00',
            true,
            '2026-01-01 23:59:00',
        );
        $tooSoon->checkInOrOut();
        $this->assertTrue($this->getCalculatorLog($tooSoon)->is_out);

        $gapElapsed = $this->calculatorWithPreviousPunch(
            '2026-01-01 20:00:00',
            true,
            '2026-01-02 00:00:00',
        );
        $gapElapsed->checkInOrOut();
        $this->assertFalse($this->getCalculatorLog($gapElapsed)->is_out);
    }

    public function test_maximum_gap_expiration_starts_a_new_in_punch(): void
    {
        GeneralConfiguration::create([
            'key' => 'max_gap_btwn_prv_in_and_curtt_out',
            'value' => '24:00',
        ]);
        $shift = Shift::create([
            'in_time' => '08:00:00',
            'out_time' => '20:00:00',
            'set_cutoff' => false,
            'is_night_shift' => false,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 7,
            'date' => '2026-01-01',
            'is_locked' => false,
            'calculated_shift' => $shift->id,
        ]);

        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-01 08:00:00',
            false,
            '2026-01-02 08:01:00',
        );
        $calculator->checkInOrOut();

        $this->assertFalse($this->getCalculatorLog($calculator)->is_out);
    }

    public function test_same_direction_setting_remains_a_minute_threshold(): void
    {
        GeneralConfiguration::create([
            'key' => 'punch_direction_consider_same',
            'value' => '2',
        ]);

        $duplicate = $this->calculatorWithPreviousPunch(
            '2026-01-01 08:00:00',
            false,
            '2026-01-01 08:01:00',
        );
        $duplicate->checkInOrOut();
        $this->assertFalse($this->getCalculatorLog($duplicate)->is_out);

        $regularPunch = $this->calculatorWithPreviousPunch(
            '2026-01-01 08:00:00',
            false,
            '2026-01-01 08:03:00',
        );
        $regularPunch->checkInOrOut();
        $this->assertTrue($this->getCalculatorLog($regularPunch)->is_out);
    }

    public function test_attendance_shift_fields_remain_empty_when_no_shift_is_configured(): void
    {
        $calculator = new AttCalculator(new AttendanceLog);
        $attendance = new Attendance;

        $currentAttendance = new \ReflectionProperty(AttCalculator::class, 'curatt');
        $currentAttendance->setValue($calculator, $attendance);

        $attendanceDate = new \ReflectionProperty(AttCalculator::class, 'attendance_date');
        $attendanceDate->setValue($calculator, '2026-10-26');

        $applyShift = new \ReflectionMethod(AttCalculator::class, 'applyCurrentShiftToAttendance');
        $applyShift->invoke($calculator);

        $this->assertNull($attendance->shift_id);
        $this->assertNull($attendance->shift_code);
        $this->assertNull($attendance->shift_in_time);
        $this->assertNull($attendance->shift_out_time);
        $this->assertFalse($attendance->is_night_shift);
    }

    public function test_missing_setting_helper_disables_optional_attendance_rules_without_failing(): void
    {
        $calculator = new AttCalculator(new AttendanceLog);
        $isSettingEnabled = new \ReflectionMethod(AttCalculator::class, 'isSettingEnabled');

        $this->assertFalse($isSettingEnabled->invoke($calculator, 'half_day_rules'));
    }

    public function test_status_muster_half_day_increment_is_idempotent(): void
    {
        $statusMaster = StatusMaster::create([
            'code' => 'PCO',
            'alias' => 'Present + C-off',
            'description' => 'Present with compensatory off',
        ]);
        $statusMuster = StatusMuster::create([
            'date' => '2026-01-02',
            'user_id' => 7,
            'day_count' => 0,
            'year' => 2026,
            'is_locked' => false,
        ]);

        $user = new User;
        $user->setAttribute('id', 7);
        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-02 08:00:00'));
        $log->setRelation('user', $user);
        $calculator = new AttCalculator($log);
        $updateStatusMuster = new \ReflectionMethod(AttCalculator::class, 'updateStatusMuster');

        $updateStatusMuster->invoke($calculator, 'PCO', 0.5, true);
        $updateStatusMuster->invoke($calculator, 'PCO', 0.5, true);

        $this->assertSame(0.5, $statusMuster->fresh()->day_count);
        $this->assertSame($statusMaster->id, $statusMuster->fresh()->status_master_id);
    }

    public function test_status_muster_update_skips_when_unlocked_muster_is_missing(): void
    {
        StatusMaster::create([
            'code' => 'PCO',
            'alias' => 'Present + C-off',
            'description' => 'Present with compensatory off',
        ]);

        $user = new User;
        $user->setAttribute('id', 7);
        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-02 08:00:00'));
        $log->setRelation('user', $user);
        $calculator = new AttCalculator($log);
        $updateStatusMuster = new \ReflectionMethod(AttCalculator::class, 'updateStatusMuster');

        $updateStatusMuster->invoke($calculator, 'PCO', 0.5, true);

        $this->assertDatabaseCount('status_musters', 0);
    }

    private function calculatorWithPreviousPunch(string $previousDatetime, bool $previousIsOut, string $currentDatetime, int $userId = 7): AttCalculator
    {
        $user = new User;
        $user->setAttribute('id', $userId);
        $log = new AttendanceLog;
        $log->setAttribute('is_manual', false);
        $log->setAttribute('debug_me', true);
        $log->setAttribute('datetime', Carbon::parse($currentDatetime));
        $log->setRelation('user', $user);

        $previousLog = new AttendanceLog;
        $previousLog->setAttribute('datetime', Carbon::parse($previousDatetime));
        $previousLog->setAttribute('is_out', $previousIsOut);

        $calculator = new AttCalculator($log, true);
        (new \ReflectionProperty(AttCalculator::class, 'prv_log'))->setValue($calculator, $previousLog);

        return $calculator;
    }

    private function getCalculatorLog(AttCalculator $calculator): AttendanceLog
    {
        return (new \ReflectionProperty(AttCalculator::class, 'log'))->getValue($calculator);
    }
}
