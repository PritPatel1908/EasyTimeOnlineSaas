<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\ManualPunchApplicationController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Requests\Tenant\StoreManualPunchApplicationRequest;
use App\Http\Requests\Tenant\UpdateManualPunchApplicationRequest;
use App\Jobs\Tenant\ApproveUserManualPunch;
use App\Jobs\Tenant\CreateManualPunchApplicationApprovals;
use App\Jobs\Tenant\GenerateManualPunchApplicationExport;
use App\Jobs\Tenant\ProcessManualPunchImport;
use App\Jobs\Tenant\ProcessPendingLogsBatch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\ManualPunchApplicationWorkflow;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantManualPunchApplicationModuleTest extends TestCase
{
    public function test_application_model_uses_existing_application_and_approval_relations(): void
    {
        $application = new ManualPunchApplication;

        $this->assertSame('manual_punch_applications', $application->getTable());
        $this->assertSame('date', $application->getCasts()['punch_date']);
        $this->assertSame('datetime', $application->getCasts()['punch_time']);
        $this->assertSame([
            'employee_codes',
            'punch_type',
            'punch_date',
            'punch_time',
            'reason',
            'approval_status',
        ], ManualPunchApplication::IMPORT_EXPORT_COLUMNS);
        $this->assertTrue(method_exists($application, 'Users'));
        $this->assertTrue(method_exists($application, 'approval_status'));
        $this->assertNotContains('status', $application->getFillable());
    }

    public function test_create_update_and_list_persist_application_and_employee_pivot_records(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'manual-punch-flow',
                'status' => 1,
            ]);
            DB::table('approval_flows')->insert([
                'id' => 11,
                'approval_flow_code' => 'second-manual-punch-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 20,
                'level' => 1,
                'approval_flow_id' => 10,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 22,
                'level' => 2,
                'approval_flow_id' => 10,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 21,
                'level' => 1,
                'approval_flow_id' => 11,
            ]);
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'manual_punch_approval_flow_id' => 10],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'manual_punch_approval_flow_id' => 11],
            ]);

            $workflow = app(ManualPunchApplicationWorkflow::class);
            Bus::fake();
            $created = $workflow->create([
                'punch_type' => '1',
                'punch_date' => '2026-10-20 00:00:00',
                'punch_time' => '09:15:00',
                'reason' => 'Correction',
            ], [101, 102], 101);

            $this->assertCount(1, $created);
            $applicationId = (int) $created->first()->id;
            $this->assertDatabaseHas('manual_punch_applications', [
                'id' => $applicationId,
                'punch_type' => '1',
                'punch_date' => '2026-10-20 00:00:00',
                'reason' => 'Correction',
            ]);
            $this->assertDatabaseHas('manual_punch_application_users', [
                'manual_punch_application_id' => $applicationId,
                'user_id' => 101,
            ]);
            $this->assertDatabaseHas('manual_punch_application_users', [
                'manual_punch_application_id' => $applicationId,
                'user_id' => 102,
            ]);
            Bus::assertDispatched(CreateManualPunchApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === $applicationId
                && $job->approvalFlowIds === [10, 11]
                && $job->actorId === 101
            ));
            (new CreateManualPunchApplicationApprovals($applicationId, [10, 11], 101))->handle();
            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $applicationId,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 10,
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $applicationId,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 11,
            ]);
            $this->assertDatabaseCount('manual_punch_applications', 1);
            $this->assertSame(2, DB::table('approval_statuses')
                ->where('approvable_id', $applicationId)
                ->where('approvable_type', ManualPunchApplication::class)
                ->count());
            $this->assertSame(3, DB::table('approval_status_details')
                ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
                ->where('approval_statuses.approvable_id', $applicationId)
                ->where('approval_statuses.approvable_type', ManualPunchApplication::class)
                ->count());
            $this->assertDatabaseHas('approval_status_details', [
                'approval_status_id' => DB::table('approval_statuses')
                    ->where('approvable_id', $applicationId)
                    ->where('approval_flow_id', 10)
                    ->value('id'),
                'level' => 1,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);
            $this->assertDatabaseHas('approval_status_details', [
                'approval_status_id' => DB::table('approval_statuses')
                    ->where('approvable_id', $applicationId)
                    ->where('approval_flow_id', 10)
                    ->value('id'),
                'level' => 2,
                'approval_flow_detail_id' => 22,
                'approval_status' => 'pending',
            ]);
            $this->assertSame(
                'pending',
                ManualPunchApplication::query()->with('approval_statuses')->findOrFail($applicationId)->overallApprovalStatus(),
            );
            Bus::fake();
            (new ApproveUserManualPunch($created->first()))->handle();
            Bus::assertNotDispatched(ProcessPendingLogsBatch::class);

            $application = ManualPunchApplication::query()->findOrFail($applicationId);
            $workflow->update($application, [
                'punch_type' => '2',
                'punch_date' => '2026-10-21 00:00:00',
                'punch_time' => '18:00:00',
                'reason' => 'Updated correction',
            ], [102], 101);
            Bus::assertDispatched(CreateManualPunchApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === $applicationId
                && $job->approvalFlowIds === [11]
                && $job->actorId === 101
            ));
            (new CreateManualPunchApplicationApprovals($applicationId, [11], 101))->handle();

            $this->assertDatabaseHas('manual_punch_applications', [
                'id' => $applicationId,
                'punch_type' => '2',
                'punch_date' => '2026-10-21 00:00:00',
                'reason' => 'Updated correction',
            ]);
            $this->assertDatabaseMissing('manual_punch_application_users', [
                'manual_punch_application_id' => $applicationId,
                'user_id' => 101,
            ]);
            $this->assertDatabaseHas('manual_punch_application_users', [
                'manual_punch_application_id' => $applicationId,
                'user_id' => 102,
            ]);
            $this->assertSame(1, DB::table('approval_statuses')
                ->where('approvable_id', $applicationId)
                ->where('approvable_type', ManualPunchApplication::class)
                ->count());
            $this->assertSame(1, DB::table('approval_status_details')
                ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
                ->where('approval_statuses.approvable_id', $applicationId)
                ->where('approval_statuses.approvable_type', ManualPunchApplication::class)
                ->count());
            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $applicationId,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 11,
            ]);

            $listView = app(ManualPunchApplicationController::class)->index();
            $this->assertSame($applicationId, (int) $listView->getData()['applications']->first()->id);
            $this->assertSame('Employee Two', $listView->getData()['applications']->first()->Users->first()->name);
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_application_requests_use_manual_punch_fields_and_require_users_on_update(): void
    {
        $storeRules = (new StoreManualPunchApplicationRequest)->rules();
        $updateRules = (new UpdateManualPunchApplicationRequest)->rules();

        $this->assertSame(['required', 'string', 'in:1,2,3,4'], $storeRules['punch_type']);
        $this->assertSame(['nullable', 'array'], $storeRules['users']);
        $this->assertSame(['nullable', 'array'], $updateRules['users']);
        $this->assertArrayNotHasKey('approval_status', $storeRules);
    }

    public function test_application_import_sample_contains_manual_punch_fields_and_generated_status(): void
    {
        $response = app(ManualPunchApplicationController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(ManualPunchApplication::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(ManualPunchApplication::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('ignored on import', $rows[1][5]);
    }

    public function test_application_edit_form_keeps_employee_selection_editable(): void
    {
        view()->share('errors', new ViewErrorBag);
        $application = new ManualPunchApplication;
        $application->setRawAttributes([
            'punch_type' => '1',
            'punch_date' => '2026-10-21',
            'punch_time' => '12:20:46',
            'reason' => 'Correction',
        ]);
        $employee = new User;
        $employee->setRawAttributes([
            'id' => 7,
            'name' => 'Test Employee',
            'code' => 'EMP007',
        ]);
        $application->setRelation('Users', collect([$employee]));

        $html = view('time-management.manual-punches.partials.form-fields', [
            'punch' => $application,
            'lockEmployees' => false,
            'users' => collect([$employee]),
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

        $this->assertStringContainsString('name="users[]"', $html);
        $this->assertStringContainsString('value="7"', $html);
        $this->assertDoesNotMatchRegularExpression('/<select[^>]*id="manual_punch_users"[^>]*disabled/', $html);
    }

    public function test_application_import_job_accepts_exported_status_column_without_applying_it(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'manual-punch-application-import-');
        $this->assertNotFalse($filePath);

        try {
            file_put_contents(
                $filePath,
                "employee_codes,punch_type,punch_date,punch_time,reason,approval_status\nE001,In,2026-10-20,09:15,Correction,approved\n",
            );
            $job = new ProcessManualPunchImport(1, 'unused', true);
            $parseCsv = new ReflectionMethod(ProcessManualPunchImport::class, 'parseCsv');
            $rows = $parseCsv->invoke($job, $filePath);

            $this->assertTrue($job->createApplications);
            $this->assertSame('approved', $rows[2]['approval_status']);
            $this->assertArrayNotHasKey('status', $rows[2]);
        } finally {
            unlink($filePath);
        }
    }

    public function test_application_import_dispatches_as_approval_requests(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);
        $file = UploadedFile::fake()->createWithContent(
            'manual_punch_applications.csv',
            implode(',', ManualPunchApplication::IMPORT_EXPORT_COLUMNS)."\nE001,In,2026-10-20,09:15,Correction,\n",
        );
        $request = Request::create(
            '/time-management/manual-punch-applications/import',
            'POST',
            [],
            [],
            ['file' => $file],
        );

        $response = app(ManualPunchApplicationController::class)->import($request);

        $this->assertStringContainsString('application import has started', $response->getSession()->get('success', ''));
        Bus::assertDispatched(ProcessManualPunchImport::class, function (ProcessManualPunchImport $job): bool {
            return $job->userId === 17
                && $job->createApplications
                && str_contains($job->filePath, 'manual-punch-application-imports/');
        });
    }

    public function test_application_export_dispatches_and_notification_preview_reads_application_exports(): void
    {
        Bus::fake();
        Storage::fake('local');
        $this->mockTenantUser(17);

        $response = app(ManualPunchApplicationController::class)->export();
        $this->assertSame(302, $response->getStatusCode());
        Bus::assertDispatched(GenerateManualPunchApplicationExport::class, function (GenerateManualPunchApplicationExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && str_starts_with($job->fileName, 'manual_punch_applications_');
        });

        $fileName = 'manual_punch_applications_20261020_123456.csv';
        Storage::disk('local')->put('manual-punch-application-exports/'.$fileName, "employee_codes,approval_status\nE001,pending\n");
        $readPreview = new ReflectionMethod(NotificationController::class, 'readExportPreview');
        [$preview, $error] = $readPreview->invoke(
            app(NotificationController::class),
            '/time-management/manual-punch-applications/export/download/'.$fileName,
        );

        $this->assertNull($error);
        $this->assertSame(['employee_codes', 'approval_status'], $preview['headers']);
        $this->assertSame([['E001', 'pending']], $preview['rows']);
    }

    private function mockTenantUser(int $id): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);
    }

    private function createWorkflowTables(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('manual_punch_approval_flow_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flows', function ($table): void {
            $table->id();
            $table->string('approval_flow_code');
            $table->unsignedTinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_flow_details', function ($table): void {
            $table->id();
            $table->unsignedInteger('level')->default(0);
            $table->unsignedBigInteger('approval_flow_id');
            $table->timestamps();
        });
        Schema::create('manual_punch_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('manual_punch_id')->nullable();
            $table->string('punch_type');
            $table->date('punch_date');
            $table->time('punch_time');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('manual_punch_application_users', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('manual_punch_application_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('approval_statuses', function ($table): void {
            $table->id();
            $table->morphs('approvable');
            $table->string('approval_status')->default('pending');
            $table->unsignedBigInteger('approval_flow_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('approval_status_details', function ($table): void {
            $table->id();
            $table->unsignedTinyInteger('level')->nullable();
            $table->unsignedBigInteger('approval_status_id');
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->timestamp('timestamp')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('approval_status')->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function dropWorkflowTables(): void
    {
        Schema::dropIfExists('approval_status_details');
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('manual_punch_application_users');
        Schema::dropIfExists('manual_punch_applications');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
