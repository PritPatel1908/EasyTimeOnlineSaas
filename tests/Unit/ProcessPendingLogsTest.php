<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Attendance\AttCalculator;
use App\Jobs\Tenant\ProcessPendingLogs;
use App\Models\Tenant\AbsentRule;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\HalfDayRule;
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

        Schema::create('users', function ($table): void {
            $table->id();
            $table->softDeletes();
        });
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
            $table->time('first_half_end_time')->nullable();
            $table->time('second_half_start_time')->nullable();
            $table->time('auto_from')->nullable();
            $table->time('auto_to')->nullable();
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('set_cutoff')->default(false);
            $table->string('cutoff_time', 8)->nullable();
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
            $table->boolean('is_auto')->default(false);
            $table->json('shift')->nullable();
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
        Schema::create('leave_applications', function ($table): void {
            $table->id();
            $table->date('from_date');
            $table->date('to_date');
            $table->softDeletes();
        });
        Schema::create('leave_application_users', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('leave_application_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('coffs', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->softDeletes();
        });
        Schema::create('process_tags', function ($table): void {
            $table->id();
            $table->string('taggable_type');
            $table->unsignedBigInteger('taggable_id');
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('process_tags');
        Schema::dropIfExists('coffs');
        Schema::dropIfExists('leave_application_users');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('attendances2026');
        Schema::dropIfExists('status_musters');
        Schema::dropIfExists('status_masters');
        Schema::dropIfExists('shift_musters');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('general_configurations');
        Schema::dropIfExists('users');

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

    public function test_automatic_shift_without_a_complete_window_stays_unassigned(): void
    {
        $shift = Shift::create([
            'auto_from' => null,
            'auto_to' => null,
        ]);

        foreach (['auto', 'rotational'] as $index => $shiftType) {
            $userId = 20 + $index;
            DB::table('shift_musters')->insert([
                'date' => '2026-01-01',
                'user_id' => $userId,
                'is_locked' => false,
                'is_auto' => $shiftType === 'auto',
                'shift' => json_encode([$shift->id], JSON_THROW_ON_ERROR),
            ]);

            $user = new User;
            $user->setAttribute('id', $userId);
            $user->setAttribute('shift_type', $shiftType);

            $log = new AttendanceLog;
            $log->setAttribute('id', $userId);
            $log->setAttribute('datetime', Carbon::parse('2026-01-01 09:00:00'));
            $log->setRelation('user', $user);

            $calculator = new AttCalculator($log, true);
            $calculator->retriveAssignedShift();

            if ($shiftType === 'auto') {
                $this->assertNull($calculator->shift);
                $this->assertStringContainsString('No automatic shift matched; leaving shift unassigned', implode(PHP_EOL, $calculator->getReturnLogs()));
            } else {
                $this->assertSame($shift->id, $calculator->shift?->id);
            }
            $this->assertStringContainsString('automatic shift window is incomplete', implode(PHP_EOL, $calculator->getReturnLogs()));
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

    public function test_half_day_check_skips_when_attendance_context_is_incomplete(): void
    {
        $calculator = new AttCalculator(new AttendanceLog, true);

        $calculator->checkHalfDayRule();

        $this->assertStringContainsString('Skipping half-day check', implode(PHP_EOL, $calculator->getReturnLogs()));
    }

    public function test_month_end_check_uses_the_supplied_date(): void
    {
        $calculator = new AttCalculator(new AttendanceLog);

        $this->assertTrue($calculator->isMonthEnd('2026-03-31'));
        $this->assertTrue($calculator->isMonthEnd('2026-04-01'));
        $this->assertFalse($calculator->isMonthEnd('2026-04-15'));
    }

    public function test_half_day_work_period_classifies_first_second_and_unassigned_halves(): void
    {
        $this->assertSame(
            ['PA', 'Half Day | Is First Half'],
            $this->halfDayWorkPeriod('2026-04-15 08:00:00', '2026-04-15 12:00:00', '2026-04-15 08:00:00', '12:00:00', '13:00:00'),
        );
        $this->assertSame(
            ['AP', 'Half Day | Is Second Half'],
            $this->halfDayWorkPeriod('2026-04-15 13:00:00', '2026-04-15 17:00:00', '2026-04-15 08:00:00', '12:00:00', '13:00:00'),
        );
        $this->assertSame(
            ['PA', 'Half Day'],
            $this->halfDayWorkPeriod('2026-04-15 12:15:00', '2026-04-15 12:45:00', '2026-04-15 08:00:00', '12:00:00', '13:00:00'),
        );
    }

    public function test_half_day_work_period_handles_overnight_shift_boundaries(): void
    {
        $this->assertSame(
            ['PA', 'Half Day | Is First Half'],
            $this->halfDayWorkPeriod('2026-04-16 00:00:00', '2026-04-16 02:00:00', '2026-04-15 20:00:00', '02:00:00', '03:00:00'),
        );
        $this->assertSame(
            ['AP', 'Half Day | Is Second Half'],
            $this->halfDayWorkPeriod('2026-04-16 03:00:00', '2026-04-16 06:00:00', '2026-04-15 20:00:00', '02:00:00', '03:00:00'),
        );
    }

    public function test_assigned_half_day_rule_controls_single_punch_behavior(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $statusMaster = StatusMaster::create([
            'code' => 'PP',
            'alias' => 'Present',
            'description' => 'Present',
        ]);
        StatusMaster::create([
            'code' => 'PA',
            'alias' => 'Present + Absent',
            'description' => 'Half-day present',
        ]);
        $shift = new Shift;
        $shift->setAttribute('first_half_end_time', '12:00:00');
        $shift->setAttribute('second_half_start_time', '13:00:00');

        $scenarios = [
            ['rule_status' => 1, 'single_punch_allowed' => true, 'expected_half_day' => false],
            ['rule_status' => 1, 'single_punch_allowed' => false, 'expected_half_day' => true],
            ['rule_status' => 0, 'single_punch_allowed' => false, 'expected_half_day' => false],
        ];

        foreach ($scenarios as $scenario) {
            $rule = new HalfDayRule;
            $rule->setAttribute('status', $scenario['rule_status']);
            $rule->setAttribute('single_punch_allowed', $scenario['single_punch_allowed']);
            $rule->setAttribute('work_hr_less_than_minutes', 0);
            $rule->setAttribute('late_coming_minutes', 0);
            $rule->setAttribute('early_going_minutes', 0);

            $user = new User;
            $user->setAttribute('id', 7);
            $user->setRelation('half_day_rule', $rule);

            $log = new AttendanceLog;
            $log->setAttribute('datetime', Carbon::parse('2026-04-15 08:00:00'));
            $log->setRelation('user', $user);

            $attendance = Attendance::year(2026);
            $attendance->user_id = 7;
            $attendance->date = '2026-04-15';
            $attendance->shift_id = null;
            $attendance->shift_in_time = '2026-04-15 08:00:00';
            $attendance->shift_out_time = '2026-04-15 17:00:00';
            $attendance->in_time = '2026-04-15 08:00:00';
            $attendance->out_time = null;
            $attendance->is_night_shift = false;
            $attendance->is_half_day = false;
            Schema::disableForeignKeyConstraints();
            $attendance->save();
            Schema::enableForeignKeyConstraints();
            $attendance->setRelation('shift', $shift);

            $calculator = new class($log) extends AttCalculator
            {
                protected function isSettingEnabled(string $key): bool
                {
                    return $key === 'half_day_rules';
                }
            };
            (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

            $calculator->checkHalfDayRule();

            $this->assertSame($scenario['expected_half_day'], $attendance->fresh()->is_half_day);
        }
    }

    public function test_absent_rule_handles_missing_punches_without_crashing(): void
    {
        StatusMaster::create([
            'code' => 'AA',
            'alias' => 'Absent',
            'description' => 'Absent',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule();

        $calculator->checkAbsentRule();

        $this->assertTrue($attendance->fresh()->is_absent);
    }

    public function test_absent_rule_allows_single_punch_when_configured(): void
    {
        $statusMaster = StatusMaster::create([
            'code' => 'PP',
            'alias' => 'Present',
            'description' => 'Present',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(7, true, true);

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_absent);
        $this->assertSame($statusMaster->id, $attendance->fresh()->status_master_id);
    }

    public function test_absent_rule_restores_present_status_when_previous_absence_no_longer_applies(): void
    {
        $absentStatus = StatusMaster::create([
            'code' => 'AA',
            'alias' => 'Absent',
            'description' => 'Absent',
        ]);
        $presentStatus = StatusMaster::create([
            'code' => 'PP',
            'alias' => 'Present',
            'description' => 'Present',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            punchInTime: '2026-04-15 08:00:00',
            punchOutTime: '2026-04-15 17:00:00',
        );
        $attendance->is_absent = true;
        $attendance->status_master_id = $absentStatus->id;
        $attendance->save();
        $statusMuster = StatusMuster::create([
            'date' => '2026-04-15',
            'user_id' => 7,
            'status_master_id' => $absentStatus->id,
            'day_count' => 0,
            'year' => 2026,
            'is_locked' => false,
        ]);

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_absent);
        $this->assertSame($presentStatus->id, $attendance->fresh()->status_master_id);
        $this->assertSame($presentStatus->id, $statusMuster->fresh()->status_master_id);
        $this->assertSame(1.0, $statusMuster->fresh()->day_count);
    }

    public function test_absent_rule_clears_half_day_status_from_attendance_and_muster(): void
    {
        $presentStatus = StatusMaster::create([
            'code' => 'PP',
            'alias' => 'Present',
            'description' => 'Present',
        ]);
        $halfDayStatus = StatusMaster::create([
            'code' => 'PA',
            'alias' => 'Present + Absent',
            'description' => 'Half-day present',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            punchInTime: '2026-04-15 08:00:00',
            punchOutTime: '2026-04-15 17:00:00',
        );
        $attendance->is_half_day = true;
        $attendance->status_master_id = $halfDayStatus->id;
        $attendance->save();
        $statusMuster = StatusMuster::create([
            'date' => '2026-04-15',
            'user_id' => 7,
            'status_master_id' => $halfDayStatus->id,
            'day_count' => 0.5,
            'year' => 2026,
            'is_locked' => false,
        ]);

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_half_day);
        $this->assertSame($presentStatus->id, $attendance->fresh()->status_master_id);
        $this->assertSame($presentStatus->id, $statusMuster->fresh()->status_master_id);
        $this->assertSame(1.0, $statusMuster->fresh()->day_count);
    }

    public function test_absent_rule_does_not_count_early_arrival_as_late(): void
    {
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            7,
            punchInTime: '2026-04-15 07:30:00',
            punchOutTime: '2026-04-15 17:00:00',
            ruleAttributes: ['late_coming_minutes' => 5, 'consecutive_late_coming' => 1],
        );

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_absent);
    }

    public function test_absent_rule_does_not_count_late_departure_as_early(): void
    {
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            7,
            punchInTime: '2026-04-15 08:00:00',
            punchOutTime: '2026-04-15 17:30:00',
            ruleAttributes: ['early_going_minutes' => 5, 'consecutive_early_going' => 1],
        );

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_absent);
    }

    public function test_inactive_absent_rule_is_not_applied(): void
    {
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            ruleAttributes: ['status' => 0],
        );

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_absent);
    }

    public function test_consecutive_late_rule_skips_weekoffs_between_workdays(): void
    {
        StatusMaster::create([
            'code' => 'AA',
            'alias' => 'Absent',
            'description' => 'Absent',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            punchInTime: '2026-04-21 08:15:00',
            punchOutTime: '2026-04-21 17:00:00',
            ruleAttributes: ['late_coming_minutes' => 5, 'consecutive_late_coming' => 3],
            attendanceDate: '2026-04-21',
        );
        $this->seedAttendanceDays([
            ['date' => '2026-04-17', 'is_late' => true],
            ['date' => '2026-04-18', 'is_weekoff' => true],
            ['date' => '2026-04-19', 'is_weekoff' => true],
            ['date' => '2026-04-20', 'is_late' => true],
        ]);

        $calculator->checkAbsentRule();

        $this->assertTrue($attendance->fresh()->is_absent);
    }

    public function test_consecutive_early_rule_skips_holidays_between_workdays(): void
    {
        StatusMaster::create([
            'code' => 'AA',
            'alias' => 'Absent',
            'description' => 'Absent',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            punchInTime: '2026-04-21 08:00:00',
            punchOutTime: '2026-04-21 16:45:00',
            ruleAttributes: ['early_going_minutes' => 5, 'consecutive_early_going' => 3],
            attendanceDate: '2026-04-21',
        );
        $this->seedAttendanceDays([
            ['date' => '2026-04-17', 'is_early' => true],
            ['date' => '2026-04-18', 'is_holiday' => true],
            ['date' => '2026-04-19', 'is_weekoff' => true],
            ['date' => '2026-04-20', 'is_early' => true],
        ]);

        $calculator->checkAbsentRule();

        $this->assertTrue($attendance->fresh()->is_absent);
    }

    public function test_consecutive_late_rule_is_broken_by_an_on_time_workday(): void
    {
        StatusMaster::create([
            'code' => 'AA',
            'alias' => 'Absent',
            'description' => 'Absent',
        ]);
        [$calculator, $attendance] = $this->calculatorWithAbsentRule(
            punchInTime: '2026-04-21 08:15:00',
            punchOutTime: '2026-04-21 17:00:00',
            ruleAttributes: ['late_coming_minutes' => 5, 'consecutive_late_coming' => 3],
            attendanceDate: '2026-04-21',
        );
        $this->seedAttendanceDays([
            ['date' => '2026-04-17', 'is_late' => true],
            ['date' => '2026-04-18', 'is_weekoff' => true],
            ['date' => '2026-04-19', 'is_weekoff' => true],
            ['date' => '2026-04-20', 'is_late' => false],
        ]);

        $calculator->checkAbsentRule();

        $this->assertFalse($attendance->fresh()->is_absent);
    }

    public function test_absent_rule_is_skipped_when_leave_or_comp_off_is_active(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $leaveId = DB::table('leave_applications')->insertGetId([
            'from_date' => '2026-04-15',
            'to_date' => '2026-04-15',
        ]);
        DB::table('leave_application_users')->insert([
            'leave_application_id' => $leaveId,
            'user_id' => 7,
        ]);
        DB::table('coffs')->insert([
            'user_id' => 8,
            'from_date' => '2026-04-15',
            'to_date' => '2026-04-15',
        ]);

        [$leaveCalculator, $leaveAttendance] = $this->calculatorWithAbsentRule(7);
        [$coffCalculator, $coffAttendance] = $this->calculatorWithAbsentRule(8);

        $leaveCalculator->checkAbsentRule();
        $coffCalculator->checkAbsentRule();

        $this->assertFalse($leaveAttendance->fresh()->is_absent);
        $this->assertFalse($coffAttendance->fresh()->is_absent);
    }

    public function test_short_workday_sets_half_day_attendance_muster_and_first_half_tag(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $statusMaster = StatusMaster::create([
            'code' => 'PA',
            'alias' => 'Present + Absent',
            'description' => 'Half-day present',
        ]);
        $statusMuster = StatusMuster::create([
            'date' => '2026-04-15',
            'user_id' => 7,
            'day_count' => 1,
            'year' => 2026,
            'is_locked' => false,
        ]);

        $rule = new HalfDayRule;
        $rule->setAttribute('status', 1);
        $rule->setAttribute('single_punch_allowed', false);
        $rule->setAttribute('work_hr_less_than_minutes', 300);
        $rule->setAttribute('late_coming_minutes', 0);
        $rule->setAttribute('early_going_minutes', 0);
        $user = new User;
        $user->setAttribute('id', 7);
        $user->setRelation('half_day_rule', $rule);

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-04-15 08:00:00'));
        $log->setRelation('user', $user);

        $shift = new Shift;
        $shift->setAttribute('first_half_end_time', '12:00:00');
        $shift->setAttribute('second_half_start_time', '13:00:00');
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-04-15';
        $attendance->shift_in_time = '2026-04-15 08:00:00';
        $attendance->shift_out_time = '2026-04-15 17:00:00';
        $attendance->in_time = '2026-04-15 08:00:00';
        $attendance->out_time = '2026-04-15 12:00:00';
        $attendance->is_night_shift = false;
        $attendance->is_half_day = false;
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();
        $attendance->setRelation('shift', $shift);

        $calculator = new class($log) extends AttCalculator
        {
            protected function isSettingEnabled(string $key): bool
            {
                return $key === 'half_day_rules';
            }
        };
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        $calculator->checkHalfDayRule();

        $this->assertTrue($attendance->fresh()->is_half_day);
        $this->assertSame($statusMaster->id, $attendance->fresh()->status_master_id);
        $this->assertSame(0.5, $statusMuster->fresh()->day_count);
        $this->assertSame($statusMaster->id, $statusMuster->fresh()->status_master_id);
        $this->assertDatabaseHas('process_tags', [
            'taggable_type' => Attendance::class,
            'taggable_id' => $attendance->id,
            'name' => 'Half Day | Is First Half',
        ]);
    }

    public function test_late_occurrence_threshold_includes_current_unflagged_attendance(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $statusMaster = StatusMaster::create([
            'code' => 'PA',
            'alias' => 'Present + Absent',
            'description' => 'Half-day present',
        ]);
        Attendance::year(2026);
        Schema::disableForeignKeyConstraints();
        DB::table('attendances2026')->insert([
            'user_id' => 7,
            'date' => '2026-04-14',
            'is_late' => true,
            'is_locked' => false,
        ]);
        Schema::enableForeignKeyConstraints();

        $rule = new HalfDayRule;
        $rule->setAttribute('status', 1);
        $rule->setAttribute('single_punch_allowed', false);
        $rule->setAttribute('work_hr_less_than_minutes', 0);
        $rule->setAttribute('late_coming_minutes', 5);
        $rule->setAttribute('no_of_late', 1);
        $rule->setAttribute('consecutive_late_coming', 0);
        $rule->setAttribute('early_going_minutes', 0);
        $user = new User;
        $user->setAttribute('id', 7);
        $user->setRelation('half_day_rule', $rule);

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-04-15 08:30:00'));
        $log->setRelation('user', $user);

        $shift = new Shift;
        $shift->setAttribute('first_half_end_time', '12:00:00');
        $shift->setAttribute('second_half_start_time', '13:00:00');
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-04-15';
        $attendance->shift_in_time = '2026-04-15 08:00:00';
        $attendance->shift_out_time = '2026-04-15 17:00:00';
        $attendance->in_time = '2026-04-15 08:30:00';
        $attendance->out_time = '2026-04-15 17:00:00';
        $attendance->is_late = false;
        $attendance->is_early = false;
        $attendance->is_night_shift = false;
        $attendance->is_half_day = false;
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();
        $attendance->setRelation('shift', $shift);

        $calculator = new class($log) extends AttCalculator
        {
            protected function isSettingEnabled(string $key): bool
            {
                return $key === 'half_day_rules';
            }
        };
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        $calculator->checkHalfDayRule();

        $this->assertTrue($attendance->fresh()->is_half_day);
        $this->assertSame($statusMaster->id, $attendance->fresh()->status_master_id);
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

    private function calculatorWithAbsentRule(
        int $userId = 7,
        bool $allowSinglePunch = false,
        bool $withPunchIn = false,
        ?string $punchInTime = null,
        ?string $punchOutTime = null,
        array $ruleAttributes = [],
        string $attendanceDate = '2026-04-15',
    ): array
    {
        if (! DB::table('users')->where('id', $userId)->exists()) {
            DB::table('users')->insert(['id' => $userId]);
        }

        $user = new User;
        $user->setAttribute('id', $userId);
        $absentRule = new AbsentRule;
        $absentRule->setAttribute('status', 1);
        $absentRule->setAttribute('allow_single_punch', $allowSinglePunch);
        $absentRule->setAttribute('work_hr_less_than_minutes', 0);
        $absentRule->setAttribute('late_coming_minutes', 0);
        $absentRule->setAttribute('early_going_minutes', 0);
        foreach ($ruleAttributes as $attribute => $value) {
            $absentRule->setAttribute($attribute, $value);
        }
        $user->setRelation('absent_rule', $absentRule);

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse($attendanceDate.' 08:00:00'));
        $log->setRelation('user', $user);

        $attendance = Attendance::year(2026);
        $attendance->user_id = $userId;
        $attendance->date = $attendanceDate;
        $attendance->shift_in_time = $attendanceDate.' 08:00:00';
        $attendance->shift_out_time = $attendanceDate.' 17:00:00';
        $attendance->in_time = $withPunchIn ? ($punchInTime ?? $attendanceDate.' 08:00:00') : $punchInTime;
        $attendance->out_time = $punchOutTime;
        $attendance->is_absent = false;
        $attendance->is_half_day = false;
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();

        $shift = new Shift;
        $attendance->setRelation('shift', $shift);

        $calculator = new class($log) extends AttCalculator
        {
            protected function isSettingEnabled(string $key): bool
            {
                return $key === 'absent_rules';
            }
        };
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        return [$calculator, $attendance];
    }

    private function seedAttendanceDays(array $days, int $userId = 7): void
    {
        Schema::disableForeignKeyConstraints();
        try {
            foreach ($days as $day) {
                DB::table('attendances2026')->insert(array_merge([
                    'user_id' => $userId,
                    'is_locked' => false,
                    'is_weekoff' => false,
                    'is_holiday' => false,
                    'is_late' => false,
                    'is_early' => false,
                ], $day));
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function getCalculatorLog(AttCalculator $calculator): AttendanceLog
    {
        return (new \ReflectionProperty(AttCalculator::class, 'log'))->getValue($calculator);
    }

    private function halfDayWorkPeriod(
        string $punchIn,
        string $punchOut,
        string $shiftIn,
        string $firstHalfEnd,
        string $secondHalfStart,
    ): array {
        $calculator = new AttCalculator(new AttendanceLog);
        $shift = new Shift;
        $shift->setAttribute('first_half_end_time', $firstHalfEnd);
        $shift->setAttribute('second_half_start_time', $secondHalfStart);

        $attendanceContext = new \stdClass;
        $attendanceContext->shift = $shift;
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendanceContext);

        $method = new \ReflectionMethod(AttCalculator::class, 'halfDayWorkPeriod');

        return $method->invoke(
            $calculator,
            Carbon::parse($punchIn),
            Carbon::parse($punchOut),
            Carbon::parse($shiftIn),
        );
    }
}
