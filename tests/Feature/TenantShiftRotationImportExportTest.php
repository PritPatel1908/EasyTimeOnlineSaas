<?php

namespace Tests\Feature;

use App\Helpers\AfterShiftRotationProcessor;
use App\Http\Controllers\Tenant\ShiftRotationController;
use App\Http\Requests\Tenant\StoreShiftRotationRequest;
use App\Http\Requests\Tenant\UpdateShiftRotationRequest;
use App\Jobs\Tenant\GenerateShiftRotationExport;
use App\Jobs\Tenant\ProcessRotationMusters;
use App\Jobs\Tenant\ProcessShiftRotationImport;
use App\Models\Tenant\RotationValue;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantShiftRotationImportExportTest extends TestCase
{
    private const ROTATION_MUSTER_TEST_CONNECTION = 'rotation_muster_test';

    private const ROTATION_IMPORT_TEST_CONNECTION = 'rotation_import_test';

    public function test_rotation_import_export_columns_cover_rotation_and_step_fields(): void
    {
        $this->assertSame([
            'code',
            'start_date',
            'end_date',
            'is_forever',
            'skip_days_on_month_end',
            'skip_days_on_year_end',
            'shift_status',
            'status',
            'shift_code',
            'days',
        ], ShiftRotation::IMPORT_EXPORT_COLUMNS);
    }

    public function test_shift_rotation_model_persists_end_date_and_forever_flag(): void
    {
        $rotation = new ShiftRotation;

        $this->assertContains('end_date', $rotation->getFillable());
        $this->assertContains('is_forever', $rotation->getFillable());
        $this->assertSame('datetime', $rotation->getCasts()['end_date']);
        $this->assertSame('bool', $rotation->getCasts()['is_forever']);
    }

    public function test_end_date_is_only_validated_when_forever_is_disabled(): void
    {
        foreach ([StoreShiftRotationRequest::class, UpdateShiftRotationRequest::class] as $requestClass) {
            $foreverRequest = new $requestClass;
            $foreverRequest->merge(['is_forever' => true]);

            $this->assertSame(['nullable'], $foreverRequest->rules()['end_date']);

            $datedRequest = new $requestClass;
            $datedRequest->merge(['is_forever' => false]);

            $this->assertSame(
                ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                $datedRequest->rules()['end_date'],
            );
        }
    }

    public function test_rotation_muster_job_uses_database_tenant_queue_without_deduplicating_edits(): void
    {
        $job = new ProcessRotationMusters(42);

        $this->assertNotInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame('database_tenant', $job->connection);
    }

    public function test_rotation_muster_skip_boundaries_count_inclusive_calendar_days(): void
    {
        $job = new ProcessRotationMusters(42);
        $daysToBoundary = new ReflectionMethod(AfterShiftRotationProcessor::class, 'daysToCalendarBoundary');

        $this->assertSame(2, $daysToBoundary->invoke(null, CarbonImmutable::parse('2026-10-30'), true, false));
        $this->assertSame(2, $daysToBoundary->invoke(null, CarbonImmutable::parse('2026-12-30'), false, true));
        $this->assertNull($daysToBoundary->invoke(null, CarbonImmutable::parse('2026-10-20'), false, false));
    }

    public function test_rotation_muster_job_uses_rotation_steps_and_effective_end_dates(): void
    {
        $originalConnection = DB::getDefaultConnection();
        config([
            'database.connections.'.self::ROTATION_MUSTER_TEST_CONNECTION => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge(self::ROTATION_MUSTER_TEST_CONNECTION);
        DB::setDefaultConnection(self::ROTATION_MUSTER_TEST_CONNECTION);

        try {
            $this->createRotationMusterTestTables();
            DB::table('shift_rotations')->insert([
                [
                    'id' => 1,
                    'code' => 'FIXED',
                    'start_date' => '2026-10-01',
                    'end_date' => '2026-10-04',
                    'is_forever' => false,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'code' => 'FOREVER',
                    'start_date' => '2026-10-01',
                    'end_date' => '2026-10-31',
                    'is_forever' => true,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
            DB::table('rotation_values')->insert([
                ['shift_rotation_id' => 1, 'shift_id' => 11, 'days' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['shift_rotation_id' => 1, 'shift_id' => 22, 'days' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['shift_rotation_id' => 2, 'shift_id' => 33, 'days' => 2, 'created_at' => now(), 'updated_at' => now()],
            ]);
            DB::table('financial_years')->insert([
                [
                    'id' => 1,
                    'code' => 'FY26',
                    'name' => 'Financial Year 2026',
                    'is_started' => true,
                    'is_closed' => false,
                    'start_date' => '2026-04-01',
                    'end_date' => '2026-10-03',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
            DB::table('rotation_musters')->insert([
                [
                    'rotation_id' => 1,
                    'date' => '2026-10-05',
                    'shift_id' => 99,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            AfterShiftRotationProcessor::processAfterSave(1);
            AfterShiftRotationProcessor::processAfterSave(2);

            $fixedMusters = DB::table('rotation_musters')
                ->where('rotation_id', 1)
                ->orderBy('date')
                ->pluck('shift_id', 'date')
                ->all();
            $foreverMusters = DB::table('rotation_musters')
                ->where('rotation_id', 2)
                ->orderBy('date')
                ->pluck('shift_id', 'date')
                ->all();

            $this->assertSame([
                '2026-10-01' => 11,
                '2026-10-02' => 11,
                '2026-10-03' => 22,
                '2026-10-04' => 11,
            ], $fixedMusters);
            $this->assertSame([
                '2026-10-01' => 33,
                '2026-10-02' => 33,
                '2026-10-03' => 33,
            ], $foreverMusters);
        } finally {
            Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->dropIfExists('rotation_musters');
            Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->dropIfExists('rotation_values');
            Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->dropIfExists('shift_rotations');
            Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->dropIfExists('financial_years');
            DB::setDefaultConnection($originalConnection);
            DB::purge(self::ROTATION_MUSTER_TEST_CONNECTION);
        }
    }

    private function createRotationMusterTestTables(): void
    {
        Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->create('shift_rotations', function ($table): void {
            $table->id();
            $table->string('code');
            $table->boolean('skip_days_on_month_end')->default(false);
            $table->boolean('skip_days_on_year_end')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_forever')->default(false);
            $table->boolean('shift_status')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->create('rotation_values', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('shift_rotation_id');
            $table->unsignedBigInteger('shift_id');
            $table->integer('days');
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->create('financial_years', function ($table): void {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_started')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_MUSTER_TEST_CONNECTION)->create('rotation_musters', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('rotation_id')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_rotation_import_generates_musters_inline_without_dispatching_another_job(): void
    {
        $originalConnection = DB::getDefaultConnection();
        config([
            'database.connections.'.self::ROTATION_IMPORT_TEST_CONNECTION => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge(self::ROTATION_IMPORT_TEST_CONNECTION);
        DB::setDefaultConnection(self::ROTATION_IMPORT_TEST_CONNECTION);
        Storage::fake('local');
        Bus::fake();

        try {
            $this->createRotationImportTestTables();
            DB::table('users')->insert(['id' => 17]);
            DB::table('shifts')->insert([
                ['id' => 11, 'code' => 'DAY', 'deleted_at' => null],
                ['id' => 22, 'code' => 'NIGHT', 'deleted_at' => null],
            ]);
            Storage::disk('local')->put(
                'shift-rotation-imports/rotation.csv',
                implode(',', ShiftRotation::IMPORT_EXPORT_COLUMNS)."\n"
                ."NIGHT,2026-10-01,2026-10-02,No,No,No,No,Active,\"DAY,NIGHT\",\"3,2\"\n",
            );

            $job = new ProcessShiftRotationImport(17, 'shift-rotation-imports/rotation.csv');
            $job->handle();

            $this->assertDatabaseHas('shift_rotations', ['id' => 1, 'code' => 'NIGHT']);
            $this->assertDatabaseCount('rotation_values', 2);
            $this->assertSame(
                ['2026-10-01' => 11, '2026-10-02' => 11],
                DB::table('rotation_musters')->orderBy('date')->pluck('shift_id', 'date')->all(),
            );
            Bus::assertNotDispatched(ProcessRotationMusters::class);
            Bus::assertDispatched(SendQueuedNotifications::class);
        } finally {
            DB::setDefaultConnection($originalConnection);
            DB::purge(self::ROTATION_IMPORT_TEST_CONNECTION);
        }
    }

    public function test_import_sample_matches_rotation_import_columns(): void
    {
        $response = app(ShiftRotationController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('shift_rotations_import_sample.csv', $response->headers->get('Content-Disposition', ''));

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(ShiftRotation::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(ShiftRotation::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_rotation_import_parser_keeps_each_rotation_step_and_normalizes_headers(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'shift-rotation-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents(
                $filePath,
                "Rotation Code,Start Date,End Date,Is Forever,Skip Days On Month End,Skip Days On Year End,Shift Status,Status,Shift Code,Days\n"
                ."NIGHT,2026-10-01,,Yes,Yes,No,1,Active,N1,3\n"
                ."NIGHT,2026-10-01,,Yes,Yes,No,1,Active,D1,2\n",
            );

            $job = new ProcessShiftRotationImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessShiftRotationImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);

            $this->assertSame('NIGHT', $rows[2]['code']);
            $this->assertSame('Yes', $rows[2]['is_forever']);
            $this->assertSame('N1', $rows[2]['shift_code']);
            $this->assertSame('2', $rows[3]['days']);

            $validateImportRow = new ReflectionMethod(ProcessShiftRotationImport::class, 'validateImportRow');
            $this->assertSame([], $validateImportRow->invoke($job, $rows[2]));
            $this->assertSame(
                [],
                $validateImportRow->invoke($job, array_merge($rows[2], [
                    'shift_code' => 'N1,D1',
                    'days' => '3,2',
                ])),
            );
            $this->assertSame(
                ['shift_code and days must contain the same number of comma-separated values'],
                $validateImportRow->invoke($job, array_merge($rows[2], [
                    'shift_code' => 'N1,D1',
                    'days' => '3',
                ])),
            );
            $this->assertSame(
                ['end_date is required unless is_forever is Yes'],
                $validateImportRow->invoke($job, array_merge($rows[2], ['is_forever' => 'No'])),
            );
        } finally {
            unlink($filePath);
        }
    }

    public function test_export_dispatches_rotation_export_job(): void
    {
        Bus::fake();

        $response = app(ShiftRotationController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Shift rotation export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateShiftRotationExport::class, function (GenerateShiftRotationExport $job): bool {
            return $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Ashift_rotations_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    private function createRotationImportTestTables(): void
    {
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('users', function ($table): void {
            $table->id();
            $table->softDeletes();
        });
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('shifts', function ($table): void {
            $table->id();
            $table->string('code');
            $table->softDeletes();
        });
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('shift_rotations', function ($table): void {
            $table->id();
            $table->string('code');
            $table->boolean('skip_days_on_month_end')->default(false);
            $table->boolean('skip_days_on_year_end')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_forever')->default(false);
            $table->boolean('shift_status')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('rotation_values', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('shift_rotation_id');
            $table->unsignedBigInteger('shift_id');
            $table->integer('days');
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('financial_years', function ($table): void {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_started')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('rotation_musters', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('rotation_id')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->timestamps();
        });
        Schema::connection(self::ROTATION_IMPORT_TEST_CONNECTION)->create('notifications', function ($table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_import_dispatches_rotation_job_and_duplicate_toggle(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(User::class);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $headers = implode(',', ShiftRotation::IMPORT_EXPORT_COLUMNS);
        $file = UploadedFile::fake()->createWithContent('shift_rotations_import_sample.csv', $headers."\n");
        $request = Request::create('/time-management/shift-rotations/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], ['file' => $file]);

        $response = app(ShiftRotationController::class)->import($request);

        $this->assertStringContainsString('Shift rotation import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessShiftRotationImport::class, function (ProcessShiftRotationImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'shift-rotation-imports/');
        });
    }

    public function test_download_export_returns_file_response_when_rotation_export_exists(): void
    {
        Storage::fake('local');
        $fileName = 'shift_rotations_20261020_123456.csv';
        Storage::disk('local')->put('shift-rotation-exports/'.$fileName, "code,shift_code,days\nNIGHT,N1,3\n");

        $response = app(ShiftRotationController::class)->downloadExport($fileName);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('shift_rotations.csv', $response->headers->get('Content-Disposition', ''));
    }

    public function test_rotation_value_relations_follow_the_rotation_and_shift_foreign_keys(): void
    {
        $value = new RotationValue;
        $rotation = new ShiftRotation;

        $this->assertSame('shift_id', $value->shift()->getForeignKeyName());
        $this->assertSame('shift_rotation_id', $value->shift_rotation()->getForeignKeyName());
        $this->assertSame('rotation_id', $rotation->rotation_musters()->getForeignKeyName());
        $this->assertSame('shift_rotation_id', $rotation->rotation_values()->getForeignKeyName());
    }
}
