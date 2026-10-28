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
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('in_out_musters2026');
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

    private function insertMuster(int $areaId, string $checkTime, ?string $inTime, ?string $outTime): int
    {
        return DB::table('in_out_musters2026')->insertGetId([
            'user_id' => 42,
            'area_id' => $areaId,
            'check_time' => $checkTime,
            'in_time' => $inTime,
            'out_time' => $outTime,
            'is_locked' => false,
        ]);
    }
}
