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
use App\Models\Tenant\LateComingRule;
use App\Models\Tenant\Machine;
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
            $table->boolean('is_flaxible')->default(false);
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('set_cutoff')->default(false);
            $table->string('cutoff_time', 8)->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
        Schema::create('attendance_logs', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_code')->nullable();
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->boolean('is_out')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->boolean('is_calculated')->default(false);
            $table->boolean('is_staged')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('debug_me')->default(false);
            $table->boolean('has_error')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shift_musters', function ($table): void {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_auto')->default(false);
            $table->json('shift')->nullable();
            $table->unsignedBigInteger('calculated_shift')->nullable();
            $table->boolean('is_calculated')->default(false);
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
        Schema::create('short_leave_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
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
        Schema::dropIfExists('short_leave_applications');
        Schema::dropIfExists('coffs');
        Schema::dropIfExists('leave_application_users');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendances2025');
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

    public function test_automatic_shift_outside_its_muster_window_stays_unassigned(): void
    {
        $shift = Shift::create([
            'auto_from' => '10:00:00',
            'auto_to' => '18:00:00',
        ]);
        Shift::create([
            'auto_from' => '08:00:00',
            'auto_to' => '16:00:00',
        ]);
        DB::table('shift_musters')->insert([
            'date' => '2026-01-01',
            'user_id' => 22,
            'is_locked' => false,
            'is_auto' => true,
            'shift' => json_encode([$shift->id], JSON_THROW_ON_ERROR),
        ]);

        $user = new User;
        $user->setAttribute('id', 22);
        $user->setAttribute('shift_type', 'auto');

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-01 09:00:00'));
        $log->setRelation('user', $user);

        $calculator = new AttCalculator($log, true);
        $calculator->retriveAssignedShift();

        $this->assertNull($calculator->shift);
        $this->assertStringContainsString('No automatic shift matched; leaving shift unassigned', implode(PHP_EOL, $calculator->getReturnLogs()));
    }

    public function test_fixed_shift_without_a_muster_does_not_use_a_global_shift(): void
    {
        Shift::create([
            'in_time' => '09:00:00',
            'out_time' => '18:00:00',
        ]);

        $user = new User;
        $user->setAttribute('id', 23);
        $user->setAttribute('shift_type', 'fixed');

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-01 09:00:00'));
        $log->setRelation('user', $user);

        $calculator = new AttCalculator($log, true);
        $calculator->retriveAssignedShift();

        $this->assertNull($calculator->shift);
    }

    public function test_unmatched_shift_resets_calculated_shift_on_unlocked_muster(): void
    {
        DB::table('shift_musters')->insert([
            [
                'date' => '2026-01-01',
                'user_id' => 24,
                'is_locked' => false,
                'is_auto' => true,
                'shift' => json_encode([1, 2], JSON_THROW_ON_ERROR),
                'calculated_shift' => 4,
                'is_calculated' => true,
            ],
            [
                'date' => '2026-01-01',
                'user_id' => 25,
                'is_locked' => true,
                'is_auto' => true,
                'shift' => json_encode([1, 2], JSON_THROW_ON_ERROR),
                'calculated_shift' => 4,
                'is_calculated' => true,
            ],
        ]);

        $user = new User;
        $user->setAttribute('id', 24);
        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-01 09:00:00'));
        $log->setRelation('user', $user);

        (new AttCalculator($log))->resetCalculatedShiftMuster();

        $unlockedMuster = DB::table('shift_musters')->where('user_id', 24)->first();
        $lockedMuster = DB::table('shift_musters')->where('user_id', 25)->first();
        $this->assertNull($unlockedMuster->calculated_shift);
        $this->assertFalse((bool) $unlockedMuster->is_calculated);
        $this->assertSame(4, (int) $lockedMuster->calculated_shift);
        $this->assertTrue((bool) $lockedMuster->is_calculated);
    }

    public function test_attendance_persists_late_early_and_overtime_minutes(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-01-01';
        $attendance->shift_in_time = '2026-01-01 09:00:00';
        $attendance->in_time = '2026-01-01 09:17:00';
        $attendance->shift_out_time = '2026-01-01 18:00:00';
        $attendance->out_time = '2026-01-01 17:45:00';
        $attendance->is_late = true;
        $attendance->is_early = true;
        $attendance->row_overtime_minutes = 75;
        $attendance->total_overtime_minutes = 75;

        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();

        $savedAttendance = $attendance->fresh();
        $this->assertSame(17, $savedAttendance->late_minutes);
        $this->assertSame(15, $savedAttendance->early_minutes);
        $this->assertSame(75, $savedAttendance->row_overtime_minutes);
        $this->assertSame(75, $savedAttendance->total_overtime_minutes);
        $this->assertSame(0, $savedAttendance->approved_overtime_minutes);

        $savedAttendance->is_early = false;
        $savedAttendance->save();
        $this->assertSame(0, $savedAttendance->fresh()->early_minutes);
    }

    public function test_late_coming_calculates_when_half_day_setting_is_disabled(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $statusMaster = StatusMaster::create([
            'code' => 'PP',
            'alias' => 'Present',
            'description' => 'Present',
        ]);
        $rule = new LateComingRule;
        $rule->setAttribute('status', 1);
        $rule->setAttribute('ignore_late_coming_minutes', 0);

        $user = new User;
        $user->setAttribute('id', 7);
        $user->setRelation('late_coming_rule', $rule);

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-01 09:30:00'));
        $log->setRelation('user', $user);

        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-01-01';
        $attendance->shift_in_time = '2026-01-01 09:00:00';
        $attendance->in_time = '2026-01-01 09:30:00';
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();

        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        $calculator->checkLateComingRule();

        $savedAttendance = $attendance->fresh();
        $this->assertTrue($savedAttendance->is_late);
        $this->assertSame(30, $savedAttendance->late_minutes);
        $this->assertSame($statusMaster->id, $savedAttendance->status_master_id);
    }

    public function test_marking_overtime_persists_total_minutes_without_approving_them(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $overtimeStatus = StatusMaster::create([
            'code' => 'PPO',
            'alias' => 'Present + Overtime',
            'description' => 'Present with overtime',
        ]);
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-01-01';
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();

        $user = new User;
        $user->setAttribute('id', 7);
        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-01-01 18:00:00'));
        $log->setRelation('user', $user);
        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        (new \ReflectionMethod(AttCalculator::class, 'markOvertime'))->invoke($calculator, 75);

        $savedAttendance = $attendance->fresh();
        $this->assertSame($overtimeStatus->id, $savedAttendance->status_master_id);
        $this->assertTrue($savedAttendance->is_over_time);
        $this->assertSame(0, $savedAttendance->row_overtime_minutes);
        $this->assertSame(75, $savedAttendance->total_overtime_minutes);
        $this->assertSame(0, $savedAttendance->approved_overtime_minutes);
    }

    public function test_attendance_duration_migration_upgrades_existing_year_tables(): void
    {
        Schema::dropIfExists('attendances2025');
        Schema::create('attendances2025', function ($table): void {
            $table->id();
        });
        $migration = require database_path('migrations/tenant/2026_10_28_000001_add_attendance_duration_fields.php');

        $migration->up();

        foreach (['late_minutes', 'early_minutes', 'total_overtime_minutes', 'approved_overtime_minutes'] as $column) {
            $this->assertTrue(Schema::hasColumn('attendances2025', $column));
        }
    }

    public function test_row_overtime_migration_upgrades_existing_year_tables(): void
    {
        Schema::dropIfExists('attendances2025');
        Schema::create('attendances2025', function ($table): void {
            $table->id();
        });
        $migration = require database_path('migrations/tenant/2026_10_28_000002_add_row_overtime_minutes_to_attendances.php');

        $migration->up();

        $this->assertTrue(Schema::hasColumn('attendances2025', 'row_overtime_minutes'));
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
            '2026-01-01 08:00:00',
            false,
            '2026-01-02 08:00:00',
        );
        $this->createPreviousAttendanceForPunch($calculator, $shift, '2026-01-01');
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
        $this->createPreviousAttendanceForPunch($calculator, $shift, '2026-01-01', 8);
        $calculator->checkInOrOut();

        $this->assertTrue($this->getCalculatorLog($calculator)->is_out);
        $this->assertSame('2026-01-01', (new \ReflectionProperty(AttCalculator::class, 'punchAttendanceDate'))->getValue($calculator));
    }

    public function test_fixed_direction_punch_uses_previous_attendance_cutoff_before_new_shift(): void
    {
        $nightShift = Shift::create([
            'in_time' => '20:00:00',
            'out_time' => '08:00:00',
            'set_cutoff' => true,
            'cutoff_time' => '24:00:00',
            'is_night_shift' => true,
        ]);
        $dayShift = Shift::create([
            'in_time' => '09:00:00',
            'out_time' => '21:00:00',
            'set_cutoff' => true,
            'cutoff_time' => '24:00:00',
            'is_night_shift' => false,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 8,
            'date' => '2026-01-01',
            'is_locked' => false,
            'shift' => json_encode([$nightShift->id]),
            'calculated_shift' => null,
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 8,
            'date' => '2026-01-02',
            'is_locked' => false,
            'shift' => json_encode([$dayShift->id]),
            'calculated_shift' => null,
        ]);
        DB::table('users')->insert(['id' => 8]);

        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-02 02:00:00',
            false,
            '2026-01-02 18:35:00',
            8,
        );
        $log = $this->getCalculatorLog($calculator);
        $log->setAttribute('is_manual', true);
        $this->createPreviousAttendanceForPunch($calculator, $nightShift, '2026-01-01', 8);

        $calculator->setFyears();
        $calculator->checkInOrOut();
        Schema::disableForeignKeyConstraints();
        try {
            $attendance = $calculator->queryCrrAttendance($calculator->f_years);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->assertFalse($log->is_out);
        $this->assertSame('2026-01-01', (new \ReflectionProperty(AttCalculator::class, 'punchAttendanceDate'))->getValue($calculator));
        $this->assertSame('2026-01-01', $calculator->attendance_date);
        $this->assertSame(2026, $calculator->f_years);
        $this->assertSame('2026-01-01', $attendance->date->format('Y-m-d'));
        $this->assertDatabaseMissing('attendances2026', [
            'user_id' => 8,
            'date' => '2026-01-02',
        ]);

        $automaticCalculator = $this->calculatorWithPreviousPunch(
            '2026-01-02 02:00:00',
            false,
            '2026-01-02 18:36:00',
            8,
        );
        $this->createPreviousAttendanceForPunch($automaticCalculator, $nightShift, '2026-01-01', 8);
        $automaticLog = $this->getCalculatorLog($automaticCalculator);
        $automaticCalculator->setFyears();
        $automaticCalculator->checkInOrOut();

        $this->assertTrue($automaticLog->is_out);
        $this->assertSame('2026-01-01', $automaticCalculator->attendance_date);
    }

    public function test_cutoff_is_not_applied_when_the_previous_attendance_cutoff_has_expired(): void
    {
        $shift = Shift::create([
            'in_time' => '08:00:00',
            'out_time' => '20:00:00',
            'set_cutoff' => true,
            'cutoff_time' => '24:00:00',
            'is_night_shift' => false,
        ]);
        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-01 08:00:00',
            false,
            '2026-01-04 08:00:00',
        );
        $this->getCalculatorLog($calculator)->setAttribute('is_manual', true);
        $this->createPreviousAttendanceForPunch($calculator, $shift, '2026-01-01');

        $calculator->setFyears();
        $calculator->checkInOrOut();

        $this->assertSame('2026-01-04', $calculator->attendance_date);
        $this->assertNull((new \ReflectionProperty(AttCalculator::class, 'punchAttendanceDate'))->getValue($calculator));
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

    public function test_minimum_gap_ignores_a_quick_in_punch_and_accepts_in_after_gap(): void
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
        $this->assertFalse($this->getCalculatorLog($tooSoon)->is_out);
        $this->assertTrue($this->getCalculatorLog($tooSoon)->is_ignored);
        $this->assertDatabaseHas('process_tags', [
            'taggable_type' => AttendanceLog::class,
            'name' => 'Punch Within Minimum Gap',
        ]);

        $gapElapsed = $this->calculatorWithPreviousPunch(
            '2026-01-01 20:00:00',
            true,
            '2026-01-02 00:00:00',
        );
        $gapElapsed->checkInOrOut();
        $this->assertFalse($this->getCalculatorLog($gapElapsed)->is_out);
    }

    public function test_minimum_gap_applies_to_fixed_in_devices(): void
    {
        GeneralConfiguration::create([
            'key' => 'min_gap_btwn_prv_out_and_curtt_in',
            'value' => '00:30',
        ]);
        $shift = Shift::create([
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
            '2026-01-01 16:00:00',
            true,
            '2026-01-01 16:10:00',
        );
        $machine = new Machine;
        $machine->setAttribute('access_direction', 'in');
        (new \ReflectionProperty(AttCalculator::class, 'machine'))->setValue($calculator, $machine);

        $calculator->checkInOrOut();

        $this->assertTrue($this->getCalculatorLog($calculator)->is_ignored);
        $this->assertFalse($this->getCalculatorLog($calculator)->is_out);
    }

    public function test_minimum_gap_is_skipped_when_assigned_shift_has_cutoff_enabled(): void
    {
        GeneralConfiguration::create([
            'key' => 'min_gap_btwn_prv_out_and_curtt_in',
            'value' => '00:30',
        ]);
        $shift = Shift::create([
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
            '2026-01-01 16:00:00',
            true,
            '2026-01-01 16:10:00',
        );
        $machine = new Machine;
        $machine->setAttribute('access_direction', 'in');
        (new \ReflectionProperty(AttCalculator::class, 'machine'))->setValue($calculator, $machine);

        $calculator->checkInOrOut();

        $this->assertNotTrue($this->getCalculatorLog($calculator)->is_ignored);
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
        $this->assertDatabaseHas('process_tags', [
            'taggable_type' => AttendanceLog::class,
            'name' => 'Missed Out Punch',
        ]);
    }

    public function test_maximum_gap_applies_to_fixed_out_devices(): void
    {
        GeneralConfiguration::create([
            'key' => 'max_gap_btwn_prv_in_and_curtt_out',
            'value' => '16:00',
        ]);
        $shift = Shift::create([
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
            '2026-01-01 22:00:00',
            false,
            '2026-01-02 17:00:00',
        );
        $machine = new Machine;
        $machine->setAttribute('access_direction', 'out');
        (new \ReflectionProperty(AttCalculator::class, 'machine'))->setValue($calculator, $machine);

        $calculator->checkInOrOut();

        $this->assertFalse($this->getCalculatorLog($calculator)->is_out);
        $this->assertDatabaseHas('process_tags', [
            'taggable_type' => AttendanceLog::class,
            'name' => 'Missed Out Punch',
        ]);
    }

    public function test_maximum_gap_is_skipped_when_assigned_shift_has_cutoff_enabled(): void
    {
        GeneralConfiguration::create([
            'key' => 'max_gap_btwn_prv_in_and_curtt_out',
            'value' => '16:00',
        ]);
        $shift = Shift::create([
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
            '2026-01-01 22:00:00',
            false,
            '2026-01-02 17:00:00',
        );
        $machine = new Machine;
        $machine->setAttribute('access_direction', 'out');
        (new \ReflectionProperty(AttCalculator::class, 'machine'))->setValue($calculator, $machine);

        $calculator->checkInOrOut();

        $this->assertNotTrue($this->getCalculatorLog($calculator)->is_ignored);
        $this->assertDatabaseMissing('process_tags', [
            'taggable_type' => AttendanceLog::class,
            'name' => 'Missed Out Punch',
        ]);
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

    public function test_manual_punches_with_null_device_ids_are_not_duplicate_device_punches(): void
    {
        GeneralConfiguration::create([
            'key' => 'punch_direction_consider_same',
            'value' => '30',
        ]);
        $calculator = $this->calculatorWithPreviousPunch(
            '2026-01-01 21:00:00',
            false,
            '2026-01-01 21:25:00',
        );
        $log = $this->getCalculatorLog($calculator);
        $log->is_manual = true;

        $previousLog = (new \ReflectionProperty(AttCalculator::class, 'prv_log'))
            ->getValue($calculator);
        $previousLog->is_manual = true;
        $previousLog->dms_device_id = null;
        $log->dms_device_id = null;

        $this->assertFalse($calculator->checkIsDuplicatePunch());
        $this->assertNotTrue($log->is_ignored);
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

    public function test_flexible_shift_boundaries_follow_the_first_and_last_punches(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $shift = new Shift;
        $shift->is_flaxible = true;
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-10-26';
        $attendance->in_time = '2026-10-26 09:15:00';
        $attendance->out_time = '2026-10-26 17:45:00';
        $attendance->setRelation('shift', $shift);

        $calculator = new AttCalculator(new AttendanceLog);
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);
        (new \ReflectionProperty(AttCalculator::class, 'shift'))->setValue($calculator, $shift);

        Schema::disableForeignKeyConstraints();
        try {
            (new \ReflectionMethod(AttCalculator::class, 'applyFlexibleShiftTimesToAttendance'))->invoke($calculator);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->assertSame('2026-10-26 09:15:00', $attendance->fresh()->shift_in_time->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-26 17:45:00', $attendance->fresh()->shift_out_time->format('Y-m-d H:i:s'));
    }

    public function test_flexible_shift_cutoff_uses_attendance_boundaries_when_shift_times_are_not_configured(): void
    {
        $shift = new Shift;
        $shift->is_flaxible = true;
        $attendance = new Attendance;
        $attendance->shift_in_time = '2026-10-26 09:15:00';
        $attendance->shift_out_time = '2026-10-26 17:45:00';
        $attendance->is_night_shift = false;
        $attendance->setRelation('shift', $shift);

        $calculator = new AttCalculator(new AttendanceLog);
        $cutoffWindow = (new \ReflectionMethod(AttCalculator::class, 'getAttendanceCutoffWindow'))
            ->invoke($calculator, $attendance, 3600);

        $this->assertSame('2026-10-26 09:15:00', $cutoffWindow['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-26 18:45:00', $cutoffWindow['end']->format('Y-m-d H:i:s'));
    }

    public function test_flexible_shift_skips_late_early_and_overtime_rules(): void
    {
        DB::table('users')->insert(['id' => 7]);
        $user = new User;
        $user->setAttribute('id', 7);
        $shift = new Shift;
        $shift->is_flaxible = true;
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-10-26';
        $attendance->shift_in_time = '2026-10-26 09:00:00';
        $attendance->shift_out_time = '2026-10-26 17:00:00';
        $attendance->in_time = '2026-10-26 09:00:00';
        $attendance->out_time = '2026-10-26 17:00:00';
        $attendance->is_late = true;
        $attendance->is_early = true;
        $attendance->is_over_time = true;
        $attendance->row_overtime_minutes = 60;
        $attendance->total_overtime_minutes = 60;
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();
        $attendance->setRelation('shift', $shift);

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-10-26 17:00:00'));
        $log->setRelation('user', $user);
        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        $calculator->checkLateComingRule();
        $calculator->checkEarlyGoingRule();
        $calculator->checkOverTimeRule();

        $savedAttendance = $attendance->fresh();
        $this->assertFalse($savedAttendance->is_late);
        $this->assertFalse($savedAttendance->is_early);
        $this->assertFalse($savedAttendance->is_over_time);
        $this->assertSame(0, $savedAttendance->row_overtime_minutes);
        $this->assertSame(0, $savedAttendance->total_overtime_minutes);
    }

    public function test_flexible_shift_half_day_rule_uses_work_hours_and_ignores_late_and_early_settings(): void
    {
        $halfDayStatus = StatusMaster::create([
            'code' => 'PA',
            'alias' => 'Present + Absent',
            'description' => 'Half-day present',
        ]);
        DB::table('users')->insert(['id' => 7]);
        $user = new User;
        $user->setAttribute('id', 7);
        $rule = new HalfDayRule;
        $rule->setAttribute('status', 1);
        $rule->setAttribute('single_punch_allowed', false);
        $rule->setAttribute('work_hr_less_than_minutes', 360);
        $rule->setAttribute('late_coming_minutes', 1);
        $rule->setAttribute('no_of_late', 1);
        $rule->setAttribute('consecutive_late_coming', 1);
        $rule->setAttribute('early_going_minutes', 1);
        $rule->setAttribute('no_of_early', 1);
        $rule->setAttribute('consecutive_early_going', 1);
        $user->setRelation('half_day_rule', $rule);

        $shift = new Shift;
        $shift->is_flaxible = true;
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-10-26';
        $attendance->shift_in_time = '2026-10-26 09:00:00';
        $attendance->shift_out_time = '2026-10-26 13:00:00';
        $attendance->in_time = '2026-10-26 09:00:00';
        $attendance->out_time = '2026-10-26 13:00:00';
        $attendance->setRelation('shift', $shift);
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-10-26 13:00:00'));
        $log->setRelation('user', $user);
        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        $calculator->checkHalfDayRule();

        $savedAttendance = $attendance->fresh();
        $this->assertTrue($savedAttendance->is_half_day);
        $this->assertSame($halfDayStatus->id, $savedAttendance->status_master_id);
        $this->assertDatabaseHas('process_tags', [
            'taggable_type' => Attendance::class,
            'taggable_id' => $attendance->id,
            'name' => 'Half Day',
        ]);
    }

    public function test_flexible_shift_absent_rule_uses_minimum_work_time_without_late_or_early_rules(): void
    {
        $absentStatus = StatusMaster::create([
            'code' => 'AA',
            'alias' => 'Absent',
            'description' => 'Absent',
        ]);
        DB::table('users')->insert(['id' => 7]);
        $user = new User;
        $user->setAttribute('id', 7);
        $rule = new AbsentRule;
        $rule->setAttribute('status', 1);
        $rule->setAttribute('allow_single_punch', false);
        $rule->setAttribute('work_hr_less_than_minutes', 360);
        $rule->setAttribute('late_coming_minutes', 1);
        $rule->setAttribute('consecutive_late_coming', 1);
        $rule->setAttribute('early_going_minutes', 1);
        $rule->setAttribute('consecutive_early_going', 1);
        $user->setRelation('absent_rule', $rule);

        $shift = new Shift;
        $shift->is_flaxible = true;
        $attendance = Attendance::year(2026);
        $attendance->user_id = 7;
        $attendance->date = '2026-10-26';
        $attendance->shift_in_time = '2026-10-26 09:00:00';
        $attendance->shift_out_time = '2026-10-26 14:00:00';
        $attendance->in_time = '2026-10-26 09:00:00';
        $attendance->out_time = '2026-10-26 14:00:00';
        $attendance->setRelation('shift', $shift);
        Schema::disableForeignKeyConstraints();
        $attendance->save();
        Schema::enableForeignKeyConstraints();

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-10-26 14:00:00'));
        $log->setRelation('user', $user);
        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'curatt'))->setValue($calculator, $attendance);

        $calculator->checkAbsentRule();

        $savedAttendance = $attendance->fresh();
        $this->assertTrue($savedAttendance->is_absent);
        $this->assertFalse($savedAttendance->is_half_day);
        $this->assertSame($absentStatus->id, $savedAttendance->status_master_id);
    }

    public function test_night_shift_end_time_moves_to_next_day_without_changing_attendance_date(): void
    {
        $calculator = new AttCalculator(new AttendanceLog);
        $attendance = new Attendance;
        $shift = new Shift;
        $shift->in_time = Carbon::parse('21:00:00');
        $shift->out_time = Carbon::parse('09:00:00');
        $shift->is_night_shift = true;

        $currentAttendance = new \ReflectionProperty(AttCalculator::class, 'curatt');
        $currentAttendance->setValue($calculator, $attendance);
        $assignedShift = new \ReflectionProperty(AttCalculator::class, 'shift');
        $assignedShift->setValue($calculator, $shift);
        $attendanceDate = new \ReflectionProperty(AttCalculator::class, 'attendance_date');
        $attendanceDate->setValue($calculator, '2026-10-25');

        $applyShift = new \ReflectionMethod(AttCalculator::class, 'applyCurrentShiftToAttendance');
        $applyShift->invoke($calculator);

        $this->assertSame('2026-10-25', $attendanceDate->getValue($calculator));
        $this->assertSame('2026-10-25 21:00:00', $attendance->shift_in_time->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-26 09:00:00', $attendance->shift_out_time->format('Y-m-d H:i:s'));

        $attendance->shift_out_time = Carbon::parse('2026-10-25 09:00:00');
        $normalizeShiftEnd = new \ReflectionMethod(AttCalculator::class, 'normalizeNightShiftEndTime');
        $normalizeShiftEnd->invoke($calculator);

        $this->assertSame('2026-10-26 09:00:00', $attendance->shift_out_time->format('Y-m-d H:i:s'));
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

            $calculator = new AttCalculator($log);
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

        $calculator = new AttCalculator($log);
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

        $calculator = new AttCalculator($log);
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
        $previousLog->setAttribute('id', 1000 + $userId);
        $previousLog->setAttribute('datetime', Carbon::parse($previousDatetime));
        $previousLog->setAttribute('is_out', $previousIsOut);

        $calculator = new AttCalculator($log, true);
        (new \ReflectionProperty(AttCalculator::class, 'prv_log'))->setValue($calculator, $previousLog);

        return $calculator;
    }

    private function createPreviousAttendanceForPunch(AttCalculator $calculator, Shift $shift, string $attendanceDate, int $userId = 7): void
    {
        $year = Carbon::parse($attendanceDate)->year;
        $table = 'attendances'.$year;

        if (! Schema::hasTable($table)) {
            Schema::create($table, function ($table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->date('date');
                $table->unsignedBigInteger('shift_id')->nullable();
                $table->dateTime('shift_in_time')->nullable();
                $table->dateTime('shift_out_time')->nullable();
                $table->boolean('is_night_shift')->default(false);
                $table->unsignedBigInteger('in_log_id')->nullable();
                $table->unsignedBigInteger('out_log_id')->nullable();
                $table->boolean('is_locked')->default(false);
            });
        }

        $shiftStart = Carbon::parse($attendanceDate)
            ->setTimeFromTimeString($shift->in_time->format('H:i:s'));
        $shiftEnd = Carbon::parse($attendanceDate)
            ->setTimeFromTimeString($shift->out_time->format('H:i:s'));

        if ($shift->is_night_shift && $shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        $previousLog = (new \ReflectionProperty(AttCalculator::class, 'prv_log'))->getValue($calculator);
        DB::table($table)->insert([
            'user_id' => $userId,
            'date' => $attendanceDate,
            'shift_id' => $shift->id,
            'shift_in_time' => $shiftStart,
            'shift_out_time' => $shiftEnd,
            'is_night_shift' => $shift->is_night_shift,
            'in_log_id' => $previousLog->id,
            'is_locked' => false,
        ]);
    }

    private function calculatorWithAbsentRule(
        int $userId = 7,
        bool $allowSinglePunch = false,
        bool $withPunchIn = false,
        ?string $punchInTime = null,
        ?string $punchOutTime = null,
        array $ruleAttributes = [],
        string $attendanceDate = '2026-04-15',
    ): array {
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

        $calculator = new AttCalculator($log);
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
