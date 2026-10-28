<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualAttendanceController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Requests\Tenant\StoreManualAttendanceRequest;
use App\Jobs\Tenant\ApproveUserManualAttendance;
use App\Jobs\Tenant\ProcessManualAttendancePunches;
use App\Jobs\Tenant\GenerateManualAttendanceExport;
use App\Jobs\Tenant\ProcessManualAttendanceImport;
use App\Models\Tenant\ManualAttendance;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantManualAttendanceModuleTest extends TestCase
{
    public function test_import_export_columns_match_manual_attendance_fields(): void
    {
        $this->assertSame([
            'employee_codes',
            'in_time',
            'out_time',
            'reason',
        ], ManualAttendance::IMPORT_EXPORT_COLUMNS);
        $this->assertNotContains('location', ManualAttendance::IMPORT_EXPORT_COLUMNS);
        $this->assertNotContains('company', ManualAttendance::IMPORT_EXPORT_COLUMNS);
        $this->assertNotContains('status', ManualAttendance::IMPORT_EXPORT_COLUMNS);
    }

    public function test_manual_attendance_model_does_not_expose_shift_fields(): void
    {
        $attendance = new ManualAttendance();

        $this->assertSame([], array_intersect(
            ['shift_rotation_id', 'shift_type', 'shift_id'],
            $attendance->getFillable(),
        ));
        $this->assertArrayNotHasKey('shift_rotation_id', $attendance->getCasts());
        $this->assertArrayNotHasKey('shift_type', $attendance->getCasts());
        $this->assertArrayNotHasKey('shift_id', $attendance->getCasts());
        $this->assertNotContains('company_id', $attendance->getFillable());
        $this->assertNotContains('location_id', $attendance->getFillable());
        $this->assertArrayNotHasKey('company_id', $attendance->getCasts());
        $this->assertArrayNotHasKey('location_id', $attendance->getCasts());
        $this->assertFalse(method_exists($attendance, 'location'));
        $this->assertFalse(method_exists($attendance, 'company'));
        $this->assertSame('array', $attendance->getCasts()['company_ids']);
        $this->assertSame('array', $attendance->getCasts()['location_ids']);
        $this->assertContains('company_ids', $attendance->getFillable());
        $this->assertContains('location_ids', $attendance->getFillable());
    }

    public function test_manual_attendance_request_validates_datetime_values_without_shift_fields(): void
    {
        $rules = (new StoreManualAttendanceRequest())->rules();

        $this->assertSame(['nullable', 'array'], $rules['users']);
        $this->assertArrayHasKey('company_ids.*', $rules);
        $this->assertArrayHasKey('location_ids.*', $rules);
        $this->assertArrayNotHasKey('shift_type', $rules);
        $this->assertArrayNotHasKey('shift_id', $rules);
        $this->assertArrayNotHasKey('shift_rotation_id', $rules);
        $this->assertArrayHasKey('employee_filters.department_id.*', $rules);
        $this->assertArrayHasKey('employee_filters.bus_route_id.*', $rules);
        $this->assertContains('after_or_equal:in_time', $rules['out_time']);
        $this->assertTrue(collect($rules['out_time'])->contains(static fn ($rule): bool => $rule instanceof \Closure));
    }

    public function test_manual_attendance_time_range_allows_24_hours_and_rejects_more(): void
    {
        $request = StoreManualAttendanceRequest::create('/', 'POST', [
            'in_time' => '2026-10-20 09:00:00',
        ]);
        $rules = $request->rules();

        $valid = Validator::make([
            'in_time' => '2026-10-20 09:00:00',
            'out_time' => '2026-10-21 09:00:00',
        ], $rules);
        $invalid = Validator::make([
            'in_time' => '2026-10-20 09:00:00',
            'out_time' => '2026-10-21 09:00:01',
        ], $rules);

        $this->assertTrue($valid->passes());
        $this->assertTrue($invalid->fails());
        $this->assertSame(
            'The out time must be within 24 hours of the in time.',
            $invalid->errors()->first('out_time'),
        );
    }

    public function test_import_sample_uses_manual_attendance_columns(): void
    {
        $response = app(ManualAttendanceController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(ManualAttendance::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(ManualAttendance::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('employee codes', $rows[1][0]);
        $this->assertStringContainsString('YYYY-MM-DD HH:MM:SS', $rows[1][1]);
    }

    public function test_import_job_parses_attendance_headers_and_values(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'manual-attendance-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents(
                $filePath,
                "employee_codes,in_time,out_time,reason\nE001,2026-10-20 09:00:00,2026-10-20 18:00:00,Correction\n",
            );
            $job = new ProcessManualAttendanceImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessManualAttendanceImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);

            $this->assertSame('E001', $rows[2]['employee_codes']);
            $this->assertSame('2026-10-20 09:00:00', $rows[2]['in_time']);
        } finally {
            unlink($filePath);
        }
    }

    public function test_import_job_splits_comma_separated_relation_values(): void
    {
        $splitValues = new ReflectionMethod(ProcessManualAttendanceImport::class, 'splitValues');

        $this->assertSame(
            ['E001', 'E002'],
            $splitValues->invoke(new ProcessManualAttendanceImport(1, 'unused'), 'E001, E002'),
        );
    }

    public function test_attendance_import_ignores_legacy_company_and_location_columns(): void
    {
        $resolveRow = new ReflectionMethod(ProcessManualAttendanceImport::class, 'resolveRow');
        [$payload, , $errors] = $resolveRow->invoke(new ProcessManualAttendanceImport(1, 'unused'), [
            'employee_codes' => '',
            'location' => 'unknown-location',
            'company' => 'unknown-company',
            'in_time' => '2026-10-20 09:00:00',
            'out_time' => '2026-10-20 18:00:00',
        ]);

        $this->assertArrayNotHasKey('location_ids', $payload);
        $this->assertArrayNotHasKey('company_ids', $payload);
        $this->assertSame(['employee_codes is required'], $errors);
    }

    public function test_manual_attendance_import_notification_includes_the_page_path_for_refresh_polling(): void
    {
        $notification = new ManualPunchImportExportCompleted(
            'import',
            'Import completed.',
            url: '/time-management/manual-attendances',
        );

        $this->assertSame('/time-management/manual-attendances', $notification->toArray(new \stdClass)['url']);
    }

    public function test_manual_attendance_import_rejects_time_ranges_over_24_hours(): void
    {
        $job = new ProcessManualAttendanceImport(1, 'unused');
        $resolveRow = new ReflectionMethod(ProcessManualAttendanceImport::class, 'resolveRow');

        [, , $errors] = $resolveRow->invoke($job, [
            'employee_codes' => '',
            'in_time' => '2026-10-20 09:00:00',
            'out_time' => '2026-10-21 09:00:01',
        ]);

        $this->assertContains('The out time must be within 24 hours of the in time.', $errors);
    }

    public function test_saving_manual_attendance_dispatches_punch_processing_job(): void
    {
        Bus::fake();
        $attendance = new ManualAttendance();
        $attendance->id = 42;
        $dispatchProcessing = new ReflectionMethod(ManualAttendanceController::class, 'dispatchAttendanceProcessing');

        $dispatchProcessing->invoke(app(ManualAttendanceController::class), $attendance);

        Bus::assertDispatched(ProcessManualAttendancePunches::class, static function (ProcessManualAttendancePunches $job): bool {
            return $job->manualAttendanceId === 42
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
        Bus::assertNotDispatched(ApproveUserManualAttendance::class);
    }

    public function test_manual_attendance_import_dispatches_one_punch_job_without_approval(): void
    {
        Bus::fake();
        $attendance = new ManualAttendance();
        $attendance->id = 42;
        $dispatchPunchProcessing = new ReflectionMethod(ProcessManualAttendanceImport::class, 'dispatchPunchProcessing');

        $dispatchPunchProcessing->invoke(new ProcessManualAttendanceImport(17, 'unused'), $attendance);

        Bus::assertDispatchedTimes(ProcessManualAttendancePunches::class, 1);
        Bus::assertDispatched(ProcessManualAttendancePunches::class, static function (ProcessManualAttendancePunches $job): bool {
            return $job->manualAttendanceId === 42
                && $job->connection === 'database_tenant'
                && $job->queue === 'processing';
        });
        Bus::assertNotDispatched(ApproveUserManualAttendance::class);
    }

    public function test_export_dispatches_manual_attendance_export_job(): void
    {
        Bus::fake();
        $this->mockTenantUser(17);

        $response = app(ManualAttendanceController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Manual attendance export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateManualAttendanceExport::class, function (GenerateManualAttendanceExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Amanual_attendances_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_export_download_url_falls_back_when_tenant_route_parameter_is_unavailable(): void
    {
        $fileName = 'manual_attendances_20261020_123456.csv';
        $downloadUrl = (new ReflectionMethod(GenerateManualAttendanceExport::class, 'downloadUrl'))
            ->invoke(new GenerateManualAttendanceExport(17, $fileName));

        $this->assertSame(
            '/time-management/manual-attendances/export/download/' . rawurlencode($fileName),
            $downloadUrl,
        );
    }

    public function test_import_dispatches_manual_attendance_import_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);
        $file = UploadedFile::fake()->createWithContent(
            'manual_attendances_import_sample.csv',
            implode(',', ManualAttendance::IMPORT_EXPORT_COLUMNS) . "\nE001,,,fixed,DAY,,2026-10-20 09:00:00,2026-10-20 18:00:00,Correction\n",
        );
        $request = Request::create(
            '/time-management/manual-attendances/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(ManualAttendanceController::class)->import($request);

        $this->assertStringContainsString('Manual attendance import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessManualAttendanceImport::class, function (ProcessManualAttendanceImport $job): bool {
            return $job->userId === 17 && str_contains($job->filePath, 'manual-attendance-imports/');
        });
    }

    public function test_notification_preview_reads_manual_attendance_exports(): void
    {
        Storage::fake('local');
        $fileName = 'manual_attendances_20261020_123456.csv';
        Storage::disk('local')->put('manual-attendance-exports/' . $fileName, "employee_codes,in_time\nE001,2026-10-20 09:00:00\n");
        $readPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/manual-attendances/export/download/' . $fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'in_time'], $preview['headers']);
        $this->assertSame([['E001', '2026-10-20 09:00:00']], $preview['rows']);
    }

    public function test_manual_attendance_export_notification_includes_page_link_for_details_action(): void
    {
        $notification = new ManualPunchImportExportCompleted(
            'export',
            'Manual attendance export is ready.',
            '/time-management/manual-attendances/export/download/manual_attendances_20261020_123456.csv',
            '/time-management/manual-attendances',
        );

        $data = $notification->toArray(new \stdClass());

        $this->assertSame('/time-management/manual-attendances', $data['url']);
        $this->assertSame('export', $data['type']);
        $this->assertNotEmpty($data['download_url']);
    }

    public function test_manual_attendance_form_uses_schema_fields_without_status(): void
    {
        view()->share('errors', new ViewErrorBag());

        $html = view('time-management.manual-attendances.partials.form-fields', [
            'attendance' => new ManualAttendance(),
            'users' => collect(),
            'locations' => collect(),
            'companies' => collect(),
            'departments' => collect(),
            'subDepartments' => collect(),
            'categories' => collect(),
            'subCategories' => collect(),
            'designations' => collect(),
            'grades' => collect(),
            'units' => collect(),
            'busRoutes' => collect(),
        ])->render();

        $this->assertStringContainsString('name="in_time"', $html);
        $this->assertStringContainsString('name="out_time"', $html);
        $this->assertStringContainsString('manual_attendance_time_range_toast', $html);
        $this->assertStringContainsString('24 hours of In Time', $html);
        $this->assertStringNotContainsString('name="shift_rotation_id"', $html);
        $this->assertStringNotContainsString('name="shift_type"', $html);
        $this->assertStringNotContainsString('name="shift_id"', $html);
        $this->assertStringNotContainsString('name="status"', $html);
        $organizationPosition = strpos($html, 'Filtration and Employees');
        $this->assertNotFalse($organizationPosition);
        $this->assertGreaterThan($organizationPosition, strpos($html, 'name="company_ids[]"'));
        $this->assertGreaterThan($organizationPosition, strpos($html, 'name="location_ids[]"'));
        $this->assertGreaterThan($organizationPosition, strpos($html, 'name="users[]"'));
        $this->assertStringContainsString('name="employee_filters[department_id][]"', $html);
        $this->assertStringContainsString('name="employee_filters[bus_route_id][]"', $html);
    }

    public function test_manual_attendance_edit_disables_organization_fields_and_preserves_values(): void
    {
        view()->share('errors', new ViewErrorBag());

        $attendance = new ManualAttendance();
        $attendance->exists = true;
        $attendance->setAttribute('company_ids', [5, 6]);
        $attendance->setAttribute('location_ids', [7]);
        $users = collect([8, 9])->map(static function ($id) {
            return (new User())->forceFill(['id' => $id]);
        });
        $attendance->setRelation('Users', $users);

        $html = view('time-management.manual-attendances.partials.form-fields', [
            'attendance' => $attendance,
            'users' => collect(),
            'locations' => collect(),
            'companies' => collect(),
            'departments' => collect(),
            'subDepartments' => collect(),
            'categories' => collect(),
            'subCategories' => collect(),
            'designations' => collect(),
            'grades' => collect(),
            'units' => collect(),
            'busRoutes' => collect(),
        ])->render();

        $this->assertStringContainsString('manual-attendance-organization-fields border-0 p-0 m-0 w-100" disabled', $html);
        $this->assertStringContainsString('id="manual_attendance_companies" name="company_ids[]"', $html);
        $this->assertStringContainsString('name="company_ids[]" value="5"', $html);
        $this->assertStringContainsString('name="company_ids[]" value="6"', $html);
        $this->assertStringContainsString('name="location_ids[]" value="7"', $html);
        $this->assertStringContainsString('name="users[]" value="8"', $html);
        $this->assertStringContainsString('name="users[]" value="9"', $html);
        $this->assertStringContainsString('Leave empty to assign the attendance to all employees matching the selected filters', $html);
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }
}
