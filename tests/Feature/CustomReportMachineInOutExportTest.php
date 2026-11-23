<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Tenant\CustomReportRequestRules;
use App\Http\Requests\Tenant\StoreCustomReportRequest;
use App\Models\Tenant\User;
use App\Support\CustomReportTableBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CustomReportMachineInOutExportTest extends TestCase
{
    private string $originalDefaultConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config('database.default');
        config([
            'database.connections.custom_report_machine_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'custom_report_machine_test',
        ]);
        DB::purge('custom_report_machine_test');

        Schema::create('machines', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->string('access_direction')->nullable();
            $table->softDeletes();
        });
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_code')->nullable();
            $table->unsignedBigInteger('dms_user_id')->nullable();
            $table->unsignedBigInteger('dms_device_id')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->boolean('is_out')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->softDeletes();
        });
        Schema::create('attendances2026', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->dateTime('shift_in_time')->nullable();
            $table->dateTime('shift_out_time')->nullable();
        });
        Schema::create('status_masters', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
        });

        DB::table('machines')->insert([
            ['id' => 11, 'name' => 'Gate IN', 'dms_device_id' => 101, 'access_direction' => 'in'],
            ['id' => 12, 'name' => 'Gate OUT', 'dms_device_id' => 102, 'access_direction' => 'out'],
            ['id' => 13, 'name' => 'Common', 'dms_device_id' => 103, 'access_direction' => 'both'],
        ]);
        DB::table('attendances2026')->insert([
            ['user_id' => 7, 'date' => '2026-01-01', 'in_time' => '2026-01-01 07:55:00', 'out_time' => '2026-01-01 18:05:00'],
            ['user_id' => 7, 'date' => '2026-01-02', 'in_time' => '2026-01-02 09:00:00', 'out_time' => '2026-01-02 09:00:00'],
        ]);
        DB::table('attendance_logs')->insert([
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 101, 'datetime' => '2026-01-01 08:05:00', 'is_out' => false],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 101, 'datetime' => '2026-01-01 08:20:00', 'is_out' => false],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 102, 'datetime' => '2026-01-01 12:00:00', 'is_out' => true],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 102, 'datetime' => '2026-01-01 13:30:00', 'is_out' => false],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 103, 'datetime' => '2026-01-01 07:55:00', 'is_out' => false],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 103, 'datetime' => '2026-01-01 18:05:00', 'is_out' => true],
            ['user_id' => null, 'user_code' => 'E001', 'dms_user_id' => null, 'dms_device_id' => 101, 'datetime' => '2026-01-02 09:00:00', 'is_out' => false],
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('custom_report_machine_test');
        DB::purge('custom_report_machine_test');
        config(['database.default' => $this->originalDefaultConnection]);

        parent::tearDown();
    }

    public function test_machine_sequences_render_daily_pairs_in_vertical_and_horizontal_orientations(): void
    {
        $employee = new User;
        $employee->setRawAttributes(['id' => 7, 'code' => 'E001', 'dms_user_id' => 77], true);
        $fields = [
            ['field_name' => 'emp_code'],
            [
                'field_name' => 'machine_in_out',
                'machine_in_out' => [
                    ['machine_id' => 11, 'sequence' => 1, 'sub_sequence' => 1],
                    ['machine_id' => 12, 'sequence' => 1, 'sub_sequence' => 2],
                    ['machine_id' => 13, 'sequence' => 2, 'sub_sequence' => 1],
                ],
            ],
        ];
        $builder = new CustomReportTableBuilder;

        $vertical = $builder->buildForEmployees(
            $fields,
            '2026-01-01',
            '2026-01-02',
            'vertical',
            [$employee],
        );
        $horizontal = $builder->buildForEmployees(
            $fields,
            '2026-01-01',
            '2026-01-02',
            'horizontal',
            [$employee],
        );

        $sequenceOne = 'Device Seq 1 (Gate IN, Gate OUT)';
        $sequenceTwo = 'Device Seq 2 (Common)';
        $this->assertSame([
            ['Emp Code'],
            ['E001'],
            [],
            ['Date', $sequenceOne, $sequenceTwo],
            ['2026-01-01', '08:05 AM - 01:30 PM (05:25)', '07:55 AM - 06:05 PM (10:10)'],
            ['2026-01-02', '09:00 AM - ', ''],
        ], $vertical['rows']);
        $this->assertSame([
            ['Emp Code'],
            ['E001'],
            [],
            ['Date', '2026-01-01', '2026-01-02'],
            [$sequenceOne, '08:05 AM - 01:30 PM (05:25)', '09:00 AM - '],
            [$sequenceTwo, '07:55 AM - 06:05 PM (10:10)', ''],
        ], $horizontal['rows']);
    }

    public function test_machine_sequence_configuration_is_validated_and_retained(): void
    {
        $input = [
            'report_name' => 'Machine report',
            'date_range_preset' => 'today',
            'report_orientation' => 'vertical',
            'summary_orientation' => 'vertical',
            'status' => 1,
            'custom_report_fields' => [[
                'field_name' => 'machine_in_out',
                'date_time_format' => 'default',
                'minute_format' => 'default',
                'use_in_summary' => false,
                'machine_in_out' => [[
                    'machine_id' => '11',
                    'sequence' => '1',
                    'sub_sequence' => '1',
                ]],
            ]],
            'custom_report_filter' => [],
        ];
        $request = StoreCustomReportRequest::create('/', 'POST', $input);
        $validator = Validator::make($request->all(), CustomReportRequestRules::rules());
        CustomReportRequestRules::withValidator($request, $validator);

        $this->assertTrue($validator->passes(), $validator->errors()->toJson());
        $this->assertSame(
            $input['custom_report_fields'][0]['machine_in_out'],
            $validator->validated()['custom_report_fields'][0]['machine_in_out'],
        );

        $input['custom_report_fields'][0]['machine_in_out'] = [];
        $request = StoreCustomReportRequest::create('/', 'POST', $input);
        $validator = Validator::make($request->all(), CustomReportRequestRules::rules());
        CustomReportRequestRules::withValidator($request, $validator);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('custom_report_fields.0.machine_in_out', $validator->errors()->messages());
    }

    public function test_machine_punches_are_grouped_by_attendance_date_for_overnight_shifts(): void
    {
        DB::table('attendances2026')->insert([
            ['user_id' => 7, 'date' => '2026-01-10', 'in_time' => '2026-01-10 20:00:00', 'out_time' => '2026-01-11 06:00:00'],
            ['user_id' => 7, 'date' => '2026-01-11', 'in_time' => '2026-01-11 09:00:00', 'out_time' => '2026-01-11 18:00:00'],
        ]);
        DB::table('attendance_logs')->insert([
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 101, 'datetime' => '2026-01-10 20:00:00', 'is_out' => false],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 102, 'datetime' => '2026-01-11 06:00:00', 'is_out' => true],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 101, 'datetime' => '2026-01-11 09:00:00', 'is_out' => false],
            ['user_id' => 7, 'user_code' => null, 'dms_user_id' => null, 'dms_device_id' => 102, 'datetime' => '2026-01-11 18:00:00', 'is_out' => true],
        ]);

        $employee = new User;
        $employee->setRawAttributes(['id' => 7, 'code' => 'E001'], true);
        $report = (new CustomReportTableBuilder)->buildForEmployees(
            [
                ['field_name' => 'machine_in_out', 'machine_in_out' => [
                    ['machine_id' => 11, 'sequence' => 1, 'sub_sequence' => 1],
                    ['machine_id' => 12, 'sequence' => 1, 'sub_sequence' => 2],
                ]],
            ],
            '2026-01-11',
            '2026-01-11',
            'vertical',
            [$employee],
        );

        $this->assertSame([
            ['Employee'],
            ['E001'],
            [],
            ['Date', 'Device Seq 1 (Gate IN, Gate OUT)'],
            ['2026-01-11', '09:00 AM - 06:00 PM (09:00)'],
        ], $report['rows']);
    }
}
