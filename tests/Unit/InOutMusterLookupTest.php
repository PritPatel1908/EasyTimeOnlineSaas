<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Attendance\AttCalculator;
use App\Models\Tenant\Area;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InOutMusterLookupTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = (string) config('database.default');
        config([
            'database.connections.in_out_muster_lookup_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'in_out_muster_lookup_test',
        ]);
        DB::purge('in_out_muster_lookup_test');

        Schema::create('in_out_musters2026', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->date('date')->nullable();
            $table->dateTime('check_time')->nullable();
            $table->dateTime('in_time')->nullable();
            $table->unsignedBigInteger('in_log_id')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->unsignedBigInteger('out_log_id')->nullable();
            $table->integer('work_time')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
        Schema::create('shifts', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->time('auto_from')->nullable();
            $table->time('auto_to')->nullable();
            $table->boolean('is_flaxible')->default(false);
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('set_cutoff')->default(false);
            $table->string('cutoff_time')->nullable();
            $table->softDeletes();
        });
        Schema::create('shift_musters', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_auto')->default(false);
            $table->json('shift')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('in_out_musters2026');
        Schema::dropIfExists('shift_musters');
        Schema::dropIfExists('shifts');
        DB::purge('in_out_muster_lookup_test');
        config(['database.default' => $this->originalDefaultConnection]);
        config()->offsetUnset('database.connections.in_out_muster_lookup_test');

        parent::tearDown();
    }

    public function test_in_lookup_ignores_musters_from_other_areas(): void
    {
        $calculator = $this->calculatorFor('2026-03-10 12:00:00', 1);
        $matchingMusterId = $this->insertMuster(1, '2026-03-10 13:00:00', null, null);
        $this->insertMuster(2, '2026-03-10 12:15:00', null, null);

        $muster = $calculator->queryFixedInEntry('2026');

        $this->assertSame($matchingMusterId, $muster->id);
    }

    public function test_out_lookup_can_find_future_out_musters_and_ignores_other_areas(): void
    {
        $calculator = $this->calculatorFor('2026-03-10 12:00:00', 1);
        $matchingMusterId = $this->insertMuster(
            1,
            '2026-03-10 17:00:00',
            '2026-03-10 09:00:00',
            '2026-03-10 17:00:00',
        );
        $this->insertMuster(
            2,
            '2026-03-10 11:50:00',
            '2026-03-10 09:00:00',
            '2026-03-10 11:50:00',
        );

        $muster = $calculator->queryFixedOutEntry('2026');

        $this->assertSame($matchingMusterId, $muster->id);
    }

    public function test_out_lookup_prefers_an_open_in_over_a_later_closed_muster(): void
    {
        $calculator = $this->calculatorFor('2026-03-10 19:30:00', 1);
        $openInMusterId = $this->insertMuster(
            1,
            '2026-03-10 08:45:00',
            '2026-03-10 08:45:00',
            null,
        );
        $this->insertMuster(
            1,
            '2026-03-10 15:25:00',
            '2026-03-10 14:22:00',
            '2026-03-10 15:25:00',
        );

        $muster = $calculator->queryFixedOutEntry('2026');

        $this->assertSame($openInMusterId, $muster->id);
    }

    public function test_automatic_outside_window_out_punch_reuses_shift_from_same_day_open_in(): void
    {
        $shiftId = DB::table('shifts')->insertGetId([
            'name' => 'Day Shift',
            'code' => 'SF1',
            'in_time' => '09:00:00',
            'out_time' => '21:00:00',
            'auto_from' => '07:00:00',
            'auto_to' => '10:00:00',
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 42,
            'date' => '2026-03-10',
            'is_locked' => false,
            'is_auto' => true,
            'shift' => json_encode([$shiftId]),
        ]);
        $this->insertMuster(
            1,
            '2026-03-10 08:45:00',
            '2026-03-10 08:45:00',
            null,
            $shiftId,
        );

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-03-10 22:15:00'));
        $log->setAttribute('is_out', true);
        $log->setRelation('user', (new User)->setAttribute('id', 42)->setAttribute('shift_type', 'auto'));

        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'area'))
            ->setValue($calculator, (new Area)->setAttribute('id', 1));
        $calculator->f_years = '2026';
        $calculator->attendance_date = '2026-03-10';

        $calculator->retriveAssignedShift();

        $this->assertSame($shiftId, $calculator->shift->id);
    }

    public function test_same_day_open_in_muster_overrides_a_matched_automatic_shift(): void
    {
        $automaticShiftId = DB::table('shifts')->insertGetId([
            'name' => 'Automatic Shift',
            'code' => 'SF1',
            'in_time' => '09:00:00',
            'out_time' => '21:00:00',
            'auto_from' => '07:00:00',
            'auto_to' => '10:00:00',
        ]);
        $musterShiftId = DB::table('shifts')->insertGetId([
            'name' => 'Open Muster Shift',
            'code' => 'SF2',
            'in_time' => '08:00:00',
            'out_time' => '16:00:00',
            'auto_from' => '12:00:00',
            'auto_to' => '16:00:00',
        ]);
        DB::table('shift_musters')->insert([
            'user_id' => 42,
            'date' => '2026-03-10',
            'is_locked' => false,
            'is_auto' => true,
            'shift' => json_encode([$automaticShiftId]),
        ]);
        $this->insertMuster(
            1,
            '2026-03-10 08:45:00',
            '2026-03-10 08:45:00',
            null,
            $musterShiftId,
        );

        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse('2026-03-10 09:30:00'));
        $log->setAttribute('is_out', true);
        $log->setRelation('user', (new User)->setAttribute('id', 42)->setAttribute('shift_type', 'auto'));

        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'area'))
            ->setValue($calculator, (new Area)->setAttribute('id', 1));
        $calculator->f_years = '2026';
        $calculator->attendance_date = '2026-03-10';

        $calculator->retriveAssignedShift();

        $this->assertSame($musterShiftId, $calculator->shift->id);
        $overridesCutoff = new \ReflectionProperty(AttCalculator::class, 'assignedShiftOverridesPunchCutoff');
        $this->assertTrue($overridesCutoff->getValue($calculator));
    }

    public function test_fixed_in_updates_existing_muster_when_its_log_time_changes(): void
    {
        $musterId = $this->insertMuster(1, '2026-03-10 18:30:00', '2026-03-10 09:30:00', '2026-03-10 18:30:00');
        DB::table('in_out_musters2026')->where('id', $musterId)->update([
            'date' => '2026-03-10',
            'in_log_id' => 16,
            'out_log_id' => 17,
            'work_time' => 540,
        ]);

        $log = new AttendanceLog;
        $log->setAttribute('id', 16);
        $log->setAttribute('datetime', Carbon::parse('2026-03-10 09:00:00'));
        $log->setRelation('user', (new User)->setAttribute('id', 42));

        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'area'))
            ->setValue($calculator, (new Area)->setAttribute('id', 1));
        $calculator->f_years = '2026';
        $calculator->attendance_date = '2026-03-10';

        $calculator->handelFixedIn();

        $this->assertSame(1, DB::table('in_out_musters2026')->count());
        $muster = DB::table('in_out_musters2026')->where('id', $musterId)->first();
        $this->assertSame('2026-03-10 09:00:00', $muster->in_time);
        $this->assertSame('2026-03-10 18:30:00', $muster->out_time);
        $this->assertSame(16, $muster->in_log_id);
        $this->assertSame(17, $muster->out_log_id);
        $this->assertSame(570, $muster->work_time);
    }

    private function calculatorFor(string $datetime, int $areaId): AttCalculator
    {
        $log = new AttendanceLog;
        $log->setAttribute('datetime', Carbon::parse($datetime));
        $log->setRelation('user', (new User)->setAttribute('id', 42));

        $calculator = new AttCalculator($log);
        (new \ReflectionProperty(AttCalculator::class, 'area'))
            ->setValue($calculator, (new Area)->setAttribute('id', $areaId));

        return $calculator;
    }

    private function insertMuster(
        int $areaId,
        string $checkTime,
        ?string $inTime,
        ?string $outTime,
        ?int $shiftId = null,
    ): int
    {
        return DB::table('in_out_musters2026')->insertGetId([
            'user_id' => 42,
            'area_id' => $areaId,
            'shift_id' => $shiftId,
            'date' => substr($checkTime, 0, 10),
            'check_time' => $checkTime,
            'in_time' => $inTime,
            'out_time' => $outTime,
            'is_locked' => false,
        ]);
    }
}
