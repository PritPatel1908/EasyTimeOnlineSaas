<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ManualAttendancePunchProcessor;
use App\Helpers\ManualPunchProcessor;
use App\Jobs\Tenant\ProcessPendingLogsBatch;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualPunchProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.central.driver' => 'sqlite',
            'database.connections.central.database' => ':memory:',
            'database.connections.central.url' => null,
            'cache.default' => 'array',
        ]);
        DB::purge('central');

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
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_approved_manual_punch_application_logs_use_the_application_model_as_reference_type(): void
    {
        Bus::fake();

        $application = new ManualPunchApplication;
        $application->setRawAttributes([
            'id' => 25,
            'punch_type' => '1',
            'punch_date' => '2026-10-20',
            'punch_time' => '09:30:24',
        ]);
        $user = new User;
        $user->setRawAttributes([
            'id' => 2,
            'code' => '1067',
        ]);

        ManualPunchProcessor::createAttendanceLogs($application, [$user]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => 2,
            'user_code' => '1067',
            'referance_type' => ManualPunchApplication::class,
            'referance_id' => 25,
            'datetime' => '2026-10-20 09:30:24',
        ]);
    }

    public function test_approved_manual_attendance_application_creates_in_and_out_punch_logs_only(): void
    {
        Bus::fake();

        $application = new ManualAttendanceApplication;
        $application->setRawAttributes([
            'id' => 25,
            'in_time' => '2026-10-20 09:00:00',
            'out_time' => '2026-10-20 18:00:00',
        ]);
        $user = new User;
        $user->setRawAttributes([
            'id' => 2,
            'code' => '1067',
        ]);
        $application->setRelation('Users', new Collection([$user]));

        ManualAttendancePunchProcessor::process($application);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => 2,
            'user_code' => '1067',
            'referance_type' => 'manual_attendance_application_start',
            'referance_id' => 25,
            'datetime' => '2026-10-20 09:00:00',
            'punch_type' => 1,
            'is_out' => false,
            'is_manual' => true,
        ]);
        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => 2,
            'user_code' => '1067',
            'referance_type' => 'manual_attendance_application_end',
            'referance_id' => 25,
            'datetime' => '2026-10-20 18:00:00',
            'punch_type' => 2,
            'is_out' => true,
            'is_manual' => true,
        ]);
        $this->assertSame(2, DB::table('attendance_logs')->count());
        Bus::assertDispatched(ProcessPendingLogsBatch::class);
    }
}
