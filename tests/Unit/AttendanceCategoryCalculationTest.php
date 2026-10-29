<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Attendance\AttCalculator;
use App\Http\Requests\Tenant\CategoryRequestRules;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\Category;
use App\Models\Tenant\Shift;
use App\Models\Tenant\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class AttendanceCategoryCalculationTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        config([
            'database.connections.attendance_category_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'attendance_category_test',
        ]);
        DB::purge('attendance_category_test');

        DB::statement('PRAGMA foreign_keys = OFF');
        Schema::dropIfExists('attendances2025');
        Schema::dropIfExists('attendances2026');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('c_off_against_wo_hl_slabs');
        Schema::dropIfExists('overtime_slabs');
        Schema::dropIfExists('status_masters');
        Schema::dropIfExists('status_musters');
        Schema::dropIfExists('process_tags');
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->boolean('is_out')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->softDeletes();
        });
        Schema::create('c_off_against_wo_hl_slabs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->decimal('credit_days', 10, 2)->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('attendance_category_test');
        config(['database.default' => $this->originalDefaultConnection]);
        config()->offsetUnset('database.connections.attendance_category_test');

        parent::tearDown();
    }

    public function test_worked_time_on_paid_week_off_is_converted_to_c_off_credit_days(): void
    {
        $category = $this->category([
            'is_eligible_for_c_off' => true,
            'is_week_off_paid' => true,
        ]);
        DB::table('c_off_against_wo_hl_slabs')->insert([
            'category_id' => $category->id,
            'credit_days' => 1.25,
            'from_time' => '07:00:00',
            'to_time' => '09:00:00',
        ]);

        $attendance = $this->attendance([
            'is_weekoff' => true,
            'in_time' => '2026-10-25 09:00:00',
            'out_time' => '2026-10-25 17:00:00',
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $this->assertSame(480, $attendance->fresh()->work_time);
        $this->assertSame('1.25', $attendance->fresh()->c_off_days);
        $this->assertTrue(Schema::hasColumn('attendances2026', 'c_off_days'));
    }

    public function test_public_holiday_c_off_slab_credit_is_doubled_when_enabled(): void
    {
        $category = $this->category([
            'is_eligible_for_c_off' => true,
            'is_holiday_paid' => true,
            'give_double_coff_in_public_holiday' => true,
        ]);
        DB::table('c_off_against_wo_hl_slabs')->insert([
            'category_id' => $category->id,
            'credit_days' => 0.5,
            'from_time' => '04:00:00',
            'to_time' => '08:00:00',
        ]);

        $attendance = $this->attendance([
            'is_holiday' => true,
            'in_time' => '2026-10-26 09:00:00',
            'out_time' => '2026-10-26 17:00:00',
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $this->assertSame('1.00', $attendance->fresh()->c_off_days);
    }

    public function test_ignored_before_and_after_shift_punches_are_clipped_to_shift_boundaries(): void
    {
        $category = $this->category(['ignore_before_after_shift_punch' => true]);
        $attendance = $this->attendance([
            'in_time' => '2026-10-25 08:00:00',
            'out_time' => '2026-10-25 18:00:00',
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $attendance->refresh();
        $this->assertSame('2026-10-25 09:00:00', $attendance->in_time->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-25 17:00:00', $attendance->out_time->format('Y-m-d H:i:s'));
        $this->assertSame(480, $attendance->work_time);
    }

    public function test_fix_work_hours_value_is_used_only_when_punch_duration_exceeds_shift_duration(): void
    {
        $category = $this->category([
            'fix_work_hours' => true,
            'fix_work_hours_value' => '07:30',
        ]);
        $attendance = $this->attendance([
            'in_time' => '2026-10-25 08:00:00',
            'out_time' => '2026-10-25 18:00:00',
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $this->assertSame(450, $attendance->fresh()->work_time);
    }

    public function test_fix_work_hours_value_accepts_hhmm_and_decimal_hours(): void
    {
        $rules = CategoryRequestRules::rules();
        $valueRule = ['fix_work_hours_value' => $rules['fix_work_hours_value']];
        foreach (['07:30', '7.5'] as $value) {
            $validator = Validator::make([
                'fix_work_hours' => '1',
                'fix_work_hours_as_per_shift' => '0',
                'fix_work_hours_value' => $value,
            ], $valueRule);

            $this->assertTrue($validator->passes(), $value);
        }

        $invalid = Validator::make([
            'fix_work_hours' => '1',
            'fix_work_hours_as_per_shift' => '0',
            'fix_work_hours_value' => '07:90',
        ], $valueRule);
        $this->assertFalse($invalid->passes());
    }

    public function test_break_punches_override_the_scheduled_break_duration(): void
    {
        $category = $this->category(['ignore_break_in_attendance' => true]);
        $attendance = $this->attendance([
            'in_time' => '2026-10-25 09:00:00',
            'out_time' => '2026-10-25 17:00:00',
        ]);
        $shift = new Shift([
            'brack_start_time' => '13:00:00',
            'brack_end_time' => '14:00:00',
        ]);
        $attendance->setRelation('shift', $shift);
        DB::table('attendance_logs')->insert([
            ['user_id' => 25, 'datetime' => '2026-10-25 13:15:00', 'is_out' => true],
            ['user_id' => 25, 'datetime' => '2026-10-25 13:45:00', 'is_out' => false],
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $this->assertSame(450, $attendance->fresh()->work_time);
    }

    public function test_scheduled_break_is_deducted_when_no_break_punches_are_recorded(): void
    {
        $category = $this->category(['ignore_break_in_attendance' => true]);
        $attendance = $this->attendance([
            'in_time' => '2026-10-25 09:00:00',
            'out_time' => '2026-10-25 17:00:00',
        ]);
        $attendance->setRelation('shift', new Shift([
            'brack_start_time' => '13:00:00',
            'brack_end_time' => '14:00:00',
        ]));
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $this->assertSame(420, $attendance->fresh()->work_time);
    }

    public function test_fix_work_hours_as_per_shift_caps_long_work_time_to_shift_duration(): void
    {
        $category = $this->category([
            'fix_work_hours' => true,
            'fix_work_hours_as_per_shift' => true,
            'fix_work_hours_value' => '06:00',
        ]);
        $attendance = $this->attendance([
            'in_time' => '2026-10-25 08:00:00',
            'out_time' => '2026-10-25 18:00:00',
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->syncAttendanceWorkTime();

        $this->assertSame(480, $attendance->fresh()->work_time);
    }

    public function test_fix_work_hours_as_per_shift_skips_overtime(): void
    {
        Schema::create('process_tags', function (Blueprint $table): void {
            $table->id();
            $table->morphs('taggable');
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        $category = $this->category([
            'fix_work_hours' => true,
            'fix_work_hours_as_per_shift' => true,
        ]);
        $attendance = $this->attendance([
            'in_time' => '2026-10-25 08:00:00',
            'out_time' => '2026-10-25 18:00:00',
            'is_over_time' => true,
            'row_overtime_minutes' => 60,
            'total_overtime_minutes' => 60,
        ]);
        $attendance->process_tags()->create([
            'name' => 'Overtime',
            'color' => 'danger',
            'icon' => 'heroicon-o-document-duplicate',
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->checkOverTimeRule();

        $attendance->refresh();
        $this->assertFalse($attendance->is_over_time);
        $this->assertSame(0, $attendance->row_overtime_minutes);
        $this->assertSame(0, $attendance->total_overtime_minutes);
        $this->assertSame(0, $attendance->process_tags()->where('name', 'Overtime')->count());
    }

    public function test_week_off_work_time_is_overtime_and_public_holiday_ot_is_doubled(): void
    {
        Schema::create('overtime_slabs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('overtime_rule_id');
            $table->string('value_from')->nullable();
            $table->string('value_to')->nullable();
            $table->string('head_value')->nullable();
            $table->decimal('decimal_value', 10, 2)->nullable();
        });
        Schema::create('status_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('alias')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('status_musters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedBigInteger('status_master_id')->nullable();
            $table->decimal('day_count', 5, 2)->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
        Schema::create('process_tags', function (Blueprint $table): void {
            $table->id();
            $table->morphs('taggable');
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });
        DB::table('status_masters')->insert([
            'code' => 'PPO',
            'alias' => 'Overtime',
            'description' => 'Overtime',
        ]);
        DB::table('status_musters')->insert([
            'user_id' => 25,
            'date' => '2026-10-25',
            'day_count' => 0,
            'is_locked' => false,
        ]);

        $category = $this->category([
            'regular_ot_on_wo' => true,
            'give_double_ot_in_public_holiday' => true,
        ]);
        $attendance = $this->attendance([
            'is_weekoff' => true,
            'is_holiday' => true,
            'work_time' => 480,
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->checkOverTimeRule();

        $attendance->refresh();
        $this->assertSame(480, $attendance->row_overtime_minutes);
        $this->assertSame(960, $attendance->total_overtime_minutes);
        $this->assertTrue($attendance->is_over_time);
    }

    public function test_week_off_work_time_is_not_overtime_when_the_category_setting_is_disabled(): void
    {
        Schema::create('process_tags', function (Blueprint $table): void {
            $table->id();
            $table->morphs('taggable');
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        $category = $this->category(['regular_ot_on_wo' => false]);
        $attendance = $this->attendance([
            'is_weekoff' => true,
            'work_time' => 480,
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->checkOverTimeRule();

        $attendance->refresh();
        $this->assertFalse($attendance->is_over_time);
        $this->assertSame(0, $attendance->total_overtime_minutes);
    }

    public function test_fix_work_hours_skips_week_off_overtime(): void
    {
        Schema::create('process_tags', function (Blueprint $table): void {
            $table->id();
            $table->morphs('taggable');
            $table->string('name');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        $category = $this->category([
            'fix_work_hours' => true,
            'regular_ot_on_wo' => true,
        ]);
        $attendance = $this->attendance([
            'is_weekoff' => true,
            'work_time' => 480,
            'is_over_time' => true,
            'row_overtime_minutes' => 480,
            'total_overtime_minutes' => 480,
        ]);
        $calculator = $this->calculator($category, $attendance);

        $calculator->checkOverTimeRule();

        $attendance->refresh();
        $this->assertFalse($attendance->is_over_time);
        $this->assertSame(0, $attendance->row_overtime_minutes);
        $this->assertSame(0, $attendance->total_overtime_minutes);
    }

    public function test_existing_dynamic_attendance_tables_are_extended_with_c_off_column(): void
    {
        Schema::create('attendances2025', function (Blueprint $table): void {
            $table->id();
        });

        Attendance::year(2025);

        $this->assertTrue(Schema::hasColumn('attendances2025', 'c_off_days'));
    }

    public function test_timing_rules_are_bypassed_for_categories_with_the_setting_enabled(): void
    {
        $category = $this->category(['bypass_timing_rule' => true]);
        $attendance = $this->attendance();
        $calculator = $this->calculator($category, $attendance);

        $calculator->checkLateComingRule();
        $calculator->checkEarlyGoingRule();
        $calculator->checkHalfDayRule();
        $calculator->checkAbsentRule();

        $this->assertFalse((bool) $attendance->is_late);
        $this->assertFalse((bool) $attendance->is_early);
        $this->assertFalse((bool) $attendance->is_half_day);
        $this->assertFalse((bool) $attendance->is_absent);
    }

    public function test_halfday_rule_cycle_counts_only_events_after_the_last_halfday(): void
    {
        $category = $this->category(['reset_halfday_rule_cycle' => true]);
        foreach ([
            ['date' => '2026-10-01', 'is_late' => true],
            ['date' => '2026-10-05', 'is_half_day' => true],
            ['date' => '2026-10-10', 'is_late' => true],
        ] as $attributes) {
            $attendance = Attendance::year(2026);
            $attendance->user_id = 25;
            $attendance->date = $attributes['date'];
            $attendance->forceFill($attributes);
            $attendance->save();
        }

        $calculator = $this->calculator($category, $this->attendance());
        $method = new ReflectionMethod(AttCalculator::class, 'countAttendanceFlagBetweenDates');
        $method->setAccessible(true);

        $countAfterReset = $method->invoke(
            $calculator,
            25,
            'is_late',
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-11'),
            true,
        );
        $countWithoutReset = $method->invoke(
            $calculator,
            25,
            'is_late',
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-11'),
        );

        $this->assertSame(1, $countAfterReset);
        $this->assertSame(2, $countWithoutReset);
    }

    private function category(array $attributes = []): Category
    {
        $category = new Category($attributes);
        $category->id = 15;

        return $category;
    }

    private function attendance(array $attributes = []): Attendance
    {
        $attendance = Attendance::year(2026);
        $attendance->user_id = 25;
        $attendance->date = '2026-10-25';
        $attendance->shift_in_time = Carbon::parse('2026-10-25 09:00:00');
        $attendance->shift_out_time = Carbon::parse('2026-10-25 17:00:00');
        $attendance->in_time = null;
        $attendance->out_time = null;
        $attendance->forceFill($attributes);
        $attendance->save();

        return $attendance;
    }

    private function calculator(Category $category, Attendance $attendance): AttCalculator
    {
        $user = new User;
        $user->id = 25;
        $user->setRelation('category', $category);
        $user->setRelation('overtime_rule', null);

        $log = new AttendanceLog;
        $log->datetime = Carbon::parse('2026-10-25 12:00:00');
        $log->setRelation('user', $user);

        $calculator = new AttCalculator($log);
        $calculator->curatt = $attendance;

        return $calculator;
    }
}
