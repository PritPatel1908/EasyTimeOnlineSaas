<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualPunchController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Requests\Tenant\StoreManualPunchRequest;
use App\Http\Requests\Tenant\UpdateManualPunchRequest;
use App\Jobs\Tenant\GenerateManualPunchExport;
use App\Jobs\Tenant\ProcessManualPunchImport;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantManualPunchImportExportTest extends TestCase
{
    public function test_import_export_columns_match_manual_punch_and_employee_pivot_data(): void
    {
        $this->assertSame([
            'employee_codes',
            'punch_type',
            'punch_date',
            'punch_time',
            'reason',
        ], ManualPunch::IMPORT_EXPORT_COLUMNS);
    }

    public function test_manual_punch_model_does_not_store_organization_fields_or_relations(): void
    {
        $punch = new ManualPunch();

        $this->assertNotContains('location_id', $punch->getFillable());
        $this->assertNotContains('company_id', $punch->getFillable());
        $this->assertArrayNotHasKey('location_id', $punch->getCasts());
        $this->assertArrayNotHasKey('company_id', $punch->getCasts());
        $this->assertFalse(method_exists($punch, 'location'));
        $this->assertFalse(method_exists($punch, 'company'));
    }

    public function test_manual_punch_types_include_breaks_and_normalize_legacy_values(): void
    {
        $request = new StoreManualPunchRequest();

        $this->assertSame([
            '1' => 'In Punch',
            '2' => 'Out Punch',
            '3' => 'Break Start',
            '4' => 'Break End',
        ], ManualPunch::PUNCH_TYPES);
        $this->assertSame('1', ManualPunch::normalizePunchType('In'));
        $this->assertSame('2', ManualPunch::normalizePunchType('Out Punch'));
        $this->assertSame('Break Start', ManualPunch::punchTypeLabel('3'));
        $this->assertSame('Out Punch', ManualPunch::punchTypeLabel('2'));
        $this->assertTrue(ManualPunch::isOutDirection('2'));
        $this->assertFalse(ManualPunch::isOutDirection('3'));
        $this->assertTrue(ManualPunch::isOutDirection('4'));
        $this->assertSame(['required', 'string', 'in:1,2,3,4'], $request->rules()['punch_type']);
    }

    public function test_manual_punch_form_allows_no_employee_selection(): void
    {
        $request = new StoreManualPunchRequest();
        $updateRequest = new UpdateManualPunchRequest();

        $this->assertSame(['nullable', 'array'], $request->rules()['users']);
        $this->assertSame(['required', 'array', 'min:1'], $updateRequest->rules()['users']);
        $this->assertSame(['nullable', 'array'], $request->rules()['employee_filters.department_id']);
        $this->assertSame(['integer', 'distinct', 'exists:departments,id'], $request->rules()['employee_filters.department_id.*']);
        $this->assertArrayNotHasKey('status', $request->rules());
    }

    public function test_edit_form_disables_organization_fields_and_preserves_existing_selections(): void
    {
        view()->share('errors', new ViewErrorBag());
        $punch = new ManualPunch();
        $punch->setRawAttributes([
            'punch_type' => '1',
            'punch_date' => '2026-10-21',
            'punch_time' => '12:20:46',
            'reason' => 'Test',
        ]);
        $employee = new User();
        $employee->setRawAttributes(['id' => 7, 'name' => 'Test Employee', 'code' => 'EMP007']);
        $punch->setRelation('Users', collect([$employee]));

        $html = view('time-management.manual-punches.partials.form-fields', [
            'punch' => $punch,
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

        $this->assertStringContainsString('<fieldset class="manual-punch-organization-fields border-0 p-0 m-0 w-100" disabled>', $html);
        $this->assertMatchesRegularExpression('/<select[^>]*id="manual_punch_users"[^>]*disabled/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select[^>]*id="manual_punch_users"[^>]*required/', $html);
        $this->assertStringNotContainsString('name="company_ids[]" value="2"', $html);
        $this->assertStringNotContainsString('name="location_ids[]" value="3"', $html);
        $this->assertStringContainsString('name="users[]" value="7"', $html);
        $this->assertStringNotContainsString('name="status"', $html);
    }

    public function test_import_sample_uses_manual_punch_columns(): void
    {
        $response = app(ManualPunchController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(ManualPunch::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(ManualPunch::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('employee codes', $rows[1][0]);
        $this->assertStringContainsString('YYYY-MM-DD', $rows[1][2]);
    }

    public function test_import_job_parses_rows_and_normalizes_time_values(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'manual-punch-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents($filePath, "employee_codes,punch_type,punch_date,punch_time,reason\nE001,In,2026-10-20,09:15,Correction\n");
            $job = new ProcessManualPunchImport(1, 'unused');
            $parseCsv = new ReflectionMethod(ProcessManualPunchImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);
            $normalizeTime = new ReflectionMethod(ProcessManualPunchImport::class, 'normalizeTime');

            $this->assertSame('E001', $rows[2]['employee_codes']);
            $this->assertSame('09:15:00', $normalizeTime->invoke($job, $rows[2]['punch_time']));
            $this->assertArrayNotHasKey('status', $rows[2]);
        } finally {
            unlink($filePath);
        }
    }

    public function test_import_job_splits_comma_separated_employee_codes(): void
    {
        $splitValues = new ReflectionMethod(ProcessManualPunchImport::class, 'splitValues');

        $this->assertSame(['E001', 'E002'], $splitValues->invoke(new ProcessManualPunchImport(1, 'unused'), 'E001, E002'));
    }

    public function test_manual_punch_import_notification_includes_the_page_path_for_refresh_polling(): void
    {
        $notification = new ManualPunchImportExportCompleted(
            'import',
            'Import completed.',
            url: '/time-management/manual-punches',
        );

        $this->assertSame('/time-management/manual-punches', $notification->toArray(new \stdClass)['url']);
    }

    public function test_export_dispatches_manual_punch_export_job(): void
    {
        Bus::fake();
        $this->mockTenantUser(17);

        $response = app(ManualPunchController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('Manual punch export has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(GenerateManualPunchExport::class, function (GenerateManualPunchExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Amanual_punches_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_manual_punch_import_job(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);
        $file = UploadedFile::fake()->createWithContent(
            'manual_punches_import_sample.csv',
            implode(',', ManualPunch::IMPORT_EXPORT_COLUMNS) . "\nE001,In,2026-10-20,09:15,Correction\n",
        );
        $request = Request::create(
            '/time-management/manual-punches/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(ManualPunchController::class)->import($request);

        $this->assertStringContainsString('Manual punch import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessManualPunchImport::class, function (ProcessManualPunchImport $job): bool {
            return $job->userId === 17 && str_contains($job->filePath, 'manual-punch-imports/');
        });
    }

    public function test_notification_preview_reads_manual_punch_exports(): void
    {
        Storage::fake('local');
        $fileName = 'manual_punches_20261020_123456.csv';
        Storage::disk('local')->put('manual-punch-exports/' . $fileName, "employee_codes,punch_type\nE001,In Punch\n");
        $controller = app(NotificationController::class);
        $readPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');

        [$preview, $error] = $readPreview->invoke($controller, '/time-management/manual-punches/export/download/' . $fileName);

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'punch_type'], $preview['headers']);
        $this->assertSame([['E001', 'In Punch']], $preview['rows']);
    }

    public function test_manual_punch_export_notification_includes_page_link_for_details_action(): void
    {
        $notification = new ManualPunchImportExportCompleted(
            'export',
            'Manual punch export is ready.',
            '/time-management/manual-punches/export/download/manual_punches_20261020_123456.csv',
            '/time-management/manual-punches',
        );

        $data = $notification->toArray(new \stdClass());

        $this->assertSame('/time-management/manual-punches', $data['url']);
        $this->assertSame('export', $data['type']);
        $this->assertNotEmpty($data['download_url']);
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
