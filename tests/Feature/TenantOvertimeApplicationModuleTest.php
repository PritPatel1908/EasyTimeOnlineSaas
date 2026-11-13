<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Helpers\ApprovalProcessor;
use App\Http\Controllers\Tenant\OvertimeApplicationController;
use App\Http\Requests\Tenant\StoreOvertimeApplicationRequest;
use App\Jobs\Tenant\CreateOvertimeApplicationApprovals;
use App\Jobs\Tenant\FinalizeApprovedApplication;
use App\Jobs\Tenant\GenerateOvertimeApplicationExport;
use App\Jobs\Tenant\ProcessOvertimeApplicationImport;
use App\Jobs\Tenant\SendOvertimeApprovalEmails;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\User;
use App\Notifications\Tenant\OvertimeApplicationImportExportCompleted;
use App\Services\Tenant\OvertimeApplicationWorkflow;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantOvertimeApplicationModuleTest extends TestCase
{
    public function test_overtime_application_model_and_form_use_the_requested_fields(): void
    {
        $application = new OvertimeApplication;
        $rules = (new StoreOvertimeApplicationRequest)->rules();

        $this->assertSame('overtime_applications', $application->getTable());
        $this->assertSame('date', $application->getCasts()['date']);
        $this->assertSame('int', $application->getCasts()['overtime_minutes']);
        $this->assertContains('user_id', $application->getFillable());
        $this->assertContains('date', $application->getFillable());
        $this->assertContains('overtime_minutes', $application->getFillable());
        $this->assertSame(['required', 'integer', 'exists:users,id'], $rules['user_id']);
        $this->assertSame(['required', 'date'], $rules['date']);
        $this->assertSame(['required', 'integer', 'min:1', 'max:4294967295'], $rules['overtime_minutes']);
        $this->assertTrue(method_exists($application, 'approval_statuses'));
        $this->assertTrue(method_exists($application, 'user'));
    }

    public function test_import_sample_uses_overtime_application_columns(): void
    {
        $response = app(OvertimeApplicationController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(OvertimeApplication::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(OvertimeApplication::IMPORT_EXPORT_COLUMNS), $rows[1]);
        $this->assertStringContainsString('employee codes', $rows[1][0]);
        $this->assertStringContainsString('YYYY-MM-DD', $rows[1][1]);
    }

    public function test_only_unapproved_positive_attendance_overtime_is_available(): void
    {
        Schema::create('attendances2026', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('total_overtime_minutes')->nullable();
            $table->unsignedInteger('approved_overtime_minutes')->nullable();
        });

        try {
            DB::table('attendances2026')->insert([
                ['user_id' => 1, 'date' => '2026-11-03', 'total_overtime_minutes' => 75, 'approved_overtime_minutes' => 0],
                ['user_id' => 2, 'date' => '2026-11-03', 'total_overtime_minutes' => 60, 'approved_overtime_minutes' => null],
                ['user_id' => 3, 'date' => '2026-11-03', 'total_overtime_minutes' => 90, 'approved_overtime_minutes' => 15],
                ['user_id' => 4, 'date' => '2026-11-03', 'total_overtime_minutes' => 0, 'approved_overtime_minutes' => 0],
                ['user_id' => 5, 'date' => '2026-11-03', 'total_overtime_minutes' => null, 'approved_overtime_minutes' => 0],
            ]);

            $workflow = app(OvertimeApplicationWorkflow::class);
            $this->assertSame(75, $workflow->availableOvertimeMinutes(1, '2026-11-03'));
            $this->assertSame(60, $workflow->availableOvertimeMinutes(2, '2026-11-03'));
            $this->assertNull($workflow->availableOvertimeMinutes(3, '2026-11-03'));
            $this->assertNull($workflow->availableOvertimeMinutes(4, '2026-11-03'));
            $this->assertNull($workflow->availableOvertimeMinutes(5, '2026-11-03'));
            $this->assertNull($workflow->availableOvertimeMinutes(1, '2026-11-04'));
            $this->assertSame(
                'No overtime was recorded for this employee on the selected date. Please choose a date with recorded overtime.',
                $workflow->unavailableOvertimeMessage(4, '2026-11-03'),
            );
            $this->assertSame(
                'Overtime for this date has already been approved and cannot be submitted again.',
                $workflow->unavailableOvertimeMessage(3, '2026-11-03'),
            );
        } finally {
            Schema::dropIfExists('attendances2026');
        }
    }

    public function test_overtime_minutes_form_input_is_readonly_and_autofilled_by_attendance_lookup(): void
    {
        view()->share('errors', new ViewErrorBag);
        $html = view('time-management.overtime-applications.add', [
            'users' => collect(),
        ])->render();

        $this->assertMatchesRegularExpression('/<input[^>]*id="overtime_minutes"[^>]*readonly/', $html);
        $this->assertStringContainsString('attendance-overtime', $html);
        $this->assertStringContainsString('employeeInput.addEventListener(\'change\', lookupOvertime)', $html);
        $this->assertStringContainsString('dateInput.addEventListener(\'change\', lookupOvertime)', $html);
    }

    public function test_workflow_queues_and_creates_overtime_approval_statuses(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'overtime-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 20,
                'level' => 1,
                'approval_flow_id' => 10,
            ]);
            DB::table('users')->insert([
                'id' => 101,
                'name' => 'Employee One',
                'code' => 'E001',
                'overtime_approval_flow_id' => 10,
            ]);
            DB::table('attendances2026')->insert([
                'user_id' => 101,
                'date' => '2026-11-03',
                'total_overtime_minutes' => 90,
                'approved_overtime_minutes' => 0,
            ]);

            Bus::fake();
            $application = app(OvertimeApplicationWorkflow::class)->create([
                'user_id' => 101,
                'date' => '2026-11-03 00:00:00',
                'overtime_minutes' => 5,
                'reason' => 'Project work',
            ], 101);

            $this->assertDatabaseHas('overtime_applications', [
                'id' => $application->id,
                'user_id' => 101,
                'date' => '2026-11-03 00:00:00',
                'overtime_minutes' => 90,
                'reason' => 'Project work',
            ]);
            Bus::assertDispatched(CreateOvertimeApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === (int) $application->id
                && $job->approvalFlowIds === [10]
                && $job->actorId === 101
            ));

            (new CreateOvertimeApplicationApprovals((int) $application->id, [10], 101))->handle();

            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $application->id,
                'approvable_type' => OvertimeApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 10,
                'created_by' => 101,
                'updated_by' => 101,
            ]);
            $this->assertDatabaseHas('approval_status_details', [
                'approval_status_id' => DB::table('approval_statuses')
                    ->where('approvable_type', OvertimeApplication::class)
                    ->where('approvable_id', $application->id)
                    ->value('id'),
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_overtime_application_emails_are_sent_for_submission_approval_and_rejection(): void
    {
        $this->createWorkflowTables();
        $this->createEmailTables();

        try {
            Bus::fake();
            Carbon::setTestNow('2026-11-12 12:00:00');
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'overtime-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                ['id' => 20, 'level' => 1, 'approval_flow_id' => 10],
                ['id' => 21, 'level' => 2, 'approval_flow_id' => 10],
            ]);
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'email' => 'employee@example.test', 'overtime_approval_flow_id' => 10],
                ['id' => 201, 'name' => 'Approver One', 'code' => 'A001', 'email' => 'approver-one@example.test'],
                ['id' => 202, 'name' => 'Approver Two', 'code' => 'A002', 'email' => 'approver-two@example.test'],
            ]);
            DB::table('approval_flow_detail_user')->insert([
                ['approval_flow_detail_id' => 20, 'user_id' => 201],
                ['approval_flow_detail_id' => 21, 'user_id' => 202],
            ]);
            DB::table('attendances2026')->insert([
                'user_id' => 101,
                'date' => '2026-11-03',
                'total_overtime_minutes' => 90,
                'approved_overtime_minutes' => 0,
            ]);
            DB::table('email_templates')->insert([
                'id' => 1,
                'title' => 'Overtime {{ date }}: {{ application_status }}',
                'template_type' => 'overtime_application_approve_reject',
                'email_content' => '{{ employee_name }} ({{ employee_code }}); {{ date }} {{ overtime_minutes }} {{ reason }}; {{ application_status }}; {{ total_approval_levels }}; {{ total_approvers }}; {{ current_approval_level }}; {{ current_level_approver_names }}; {{ previous_level_approval_history }}; {{ approval_history }}',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach (['initial', 'approve', 'reject'] as $action) {
                DB::table('email_template_assignments')->insert([
                    'application_type' => 'overtime_application',
                    'application_action' => $action,
                    'email_template_id' => 1,
                    'status' => 'active',
                    'send_to_approver' => true,
                    'send_to_another_approver' => true,
                    'send_to_employee' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('email_configurations')->insert([
                ['key' => 'from_address', 'value' => 'noreply@example.test'],
                ['key' => 'from_name', 'value' => 'EasyTime'],
            ]);

            $sentEmails = [];
            $mailer = Mockery::mock(Mailer::class);
            $mailer->shouldReceive('html')
                ->times(10)
                ->andReturnUsing(function (string $body, callable $callback) use (&$sentEmails): void {
                    $recipient = null;
                    $subject = null;
                    $message = Mockery::mock(Message::class);
                    $message->shouldReceive('to')->once()->andReturnUsing(function (string $address) use (&$recipient, $message) {
                        $recipient = $address;

                        return $message;
                    });
                    $message->shouldReceive('subject')->once()->andReturnUsing(function (string $value) use (&$subject, $message) {
                        $subject = $value;

                        return $message;
                    });
                    $callback($message);
                    $sentEmails[$recipient][] = ['subject' => $subject, 'body' => $body];
                });
            $mailer->shouldReceive('alwaysFrom')
                ->times(4)
                ->with('noreply@example.test', 'EasyTime');
            $mailManager = Mockery::mock(MailManager::class);
            $mailManager->shouldReceive('build')
                ->times(4)
                ->andReturn($mailer);
            app()->instance('mail.manager', $mailManager);

            $workflow = app(OvertimeApplicationWorkflow::class);
            $approvedApplication = $workflow->create([
                'user_id' => 101,
                'date' => '2026-11-03',
                'overtime_minutes' => 5,
                'reason' => 'Project work',
            ], 101);
            (new CreateOvertimeApplicationApprovals((int) $approvedApplication->id, [10], 101))->handle();

            $this->assertSame(
                'Overtime 2026-11-03: pending',
                $sentEmails['employee@example.test'][0]['subject'],
            );
            $this->assertStringContainsString('Employee One (E001); 2026-11-03 90 Project work; pending; 2; 2; 1; Approver One', $sentEmails['employee@example.test'][0]['body']);
            $this->assertStringContainsString('No approvals yet', $sentEmails['approver-one@example.test'][0]['body']);
            $this->assertCount(1, $sentEmails['approver-two@example.test']);

            $approvedDetailId = (int) DB::table('approval_status_details')
                ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
                ->where('approval_statuses.approvable_type', OvertimeApplication::class)
                ->where('approval_statuses.approvable_id', $approvedApplication->id)
                ->where('approval_status_details.level', 1)
                ->value('approval_status_details.id');
            ApprovalProcessor::processDecision($approvedDetailId, 201, 'approved', null);
            Bus::assertDispatched(SendOvertimeApprovalEmails::class, static fn (SendOvertimeApprovalEmails $job): bool => (
                $job->applicationId === (int) $approvedApplication->id
                && $job->approvalStatusDetailId === $approvedDetailId
                && $job->actorId === 201
                && $job->decision === 'approved'
            ));
            $approvedJob = new SendOvertimeApprovalEmails((int) $approvedApplication->id, $approvedDetailId, 201);
            $approvedJob->handle();

            $this->assertStringContainsString('pending; 2; 2; 1; Approver One', $sentEmails['employee@example.test'][1]['body']);
            $this->assertStringContainsString('Level 1: Approver One approved on 2026-11-12 12:00:00', $sentEmails['employee@example.test'][1]['body']);
            $this->assertCount(2, $sentEmails['approver-two@example.test']);

            $rejectedApplication = $workflow->create([
                'user_id' => 101,
                'date' => '2026-11-03',
                'overtime_minutes' => 5,
                'reason' => 'Another project task',
            ], 101);
            (new CreateOvertimeApplicationApprovals((int) $rejectedApplication->id, [10], 101))->handle();
            $rejectedDetailId = (int) DB::table('approval_status_details')
                ->join('approval_statuses', 'approval_status_details.approval_status_id', '=', 'approval_statuses.id')
                ->where('approval_statuses.approvable_type', OvertimeApplication::class)
                ->where('approval_statuses.approvable_id', $rejectedApplication->id)
                ->where('approval_status_details.level', 1)
                ->value('approval_status_details.id');
            ApprovalProcessor::processDecision($rejectedDetailId, 201, 'rejected', 'Insufficient details.');
            Bus::assertDispatched(SendOvertimeApprovalEmails::class, static fn (SendOvertimeApprovalEmails $job): bool => (
                $job->applicationId === (int) $rejectedApplication->id
                && $job->approvalStatusDetailId === $rejectedDetailId
                && $job->actorId === 201
                && $job->decision === 'rejected'
            ));
            (new SendOvertimeApprovalEmails(
                (int) $rejectedApplication->id,
                $rejectedDetailId,
                201,
                'rejected',
            ))->handle();

            $this->assertSame(
                'Overtime 2026-11-03: rejected',
                $sentEmails['employee@example.test'][2]['subject'],
            );
            $this->assertStringContainsString('Insufficient details.', $sentEmails['employee@example.test'][2]['body']);
        } finally {
            Carbon::setTestNow();
            Schema::dropIfExists('email_configurations');
            Schema::dropIfExists('email_template_assignments');
            Schema::dropIfExists('email_templates');
            $this->dropWorkflowTables();
        }
    }

    public function test_final_overtime_approval_updates_attendance_with_approved_minutes(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'overtime-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 20,
                'level' => 1,
                'approval_flow_id' => 10,
            ]);
            DB::table('users')->insert([
                'id' => 101,
                'name' => 'Approver',
                'code' => 'A001',
            ]);
            DB::table('approval_flow_detail_user')->insert([
                'approval_flow_detail_id' => 20,
                'user_id' => 101,
            ]);
            DB::table('overtime_applications')->insert([
                'id' => 1,
                'user_id' => 101,
                'date' => '2026-11-03',
                'overtime_minutes' => 90,
            ]);
            DB::table('attendances2026')->insert([
                'user_id' => 101,
                'date' => '2026-11-03',
                'total_overtime_minutes' => 90,
                'approved_overtime_minutes' => 0,
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 30,
                'approvable_id' => 1,
                'approvable_type' => OvertimeApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 10,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 40,
                'level' => 1,
                'approval_status_id' => 30,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'pending',
            ]);

            Bus::fake();
            ApprovalProcessor::processDecision(40, 101, 'approved', null);

            $this->assertDatabaseHas('approval_status_details', [
                'id' => 40,
                'approval_status' => 'approved',
                'user_id' => 101,
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'id' => 30,
                'approval_status' => 'approved',
            ]);
            Bus::assertDispatched(FinalizeApprovedApplication::class, static fn (FinalizeApprovedApplication $job): bool => (
                $job->approvableType === OvertimeApplication::class
                && $job->approvableId === 1
            ));

            (new FinalizeApprovedApplication(OvertimeApplication::class, 1))->handle();

            $this->assertDatabaseHas('attendances2026', [
                'user_id' => 101,
                'date' => '2026-11-03',
                'approved_overtime_minutes' => 90,
            ]);
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_import_and_export_jobs_process_overtime_application_csv(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'overtime-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 20,
                'level' => 1,
                'approval_flow_id' => 10,
            ]);
            DB::table('users')->insert([
                'id' => 1,
                'name' => 'Employee One',
                'code' => 'E001',
                'overtime_approval_flow_id' => 10,
            ]);
            DB::table('attendances2026')->insert([
                'user_id' => 1,
                'date' => '2026-11-03',
                'total_overtime_minutes' => 90,
                'approved_overtime_minutes' => null,
            ]);

            Notification::fake();
            Bus::fake();
            Storage::fake('local');
            Storage::disk('local')->put(
                'overtime-import.csv',
                "employee_codes,date,overtime_minutes,reason,approval_status\nE001,2026-11-03,5,Project work,pending\n",
            );

            (new ProcessOvertimeApplicationImport(1, 'overtime-import.csv'))->handle();

            $applicationId = (int) DB::table('overtime_applications')->value('id');
            $this->assertDatabaseHas('overtime_applications', [
                'id' => $applicationId,
                'user_id' => 1,
                'date' => '2026-11-03 00:00:00',
                'overtime_minutes' => 90,
                'reason' => 'Project work',
            ]);
            Bus::assertDispatched(CreateOvertimeApplicationApprovals::class);
            Notification::assertSentTo(
                User::query()->findOrFail(1),
                OvertimeApplicationImportExportCompleted::class,
            );

            (new GenerateOvertimeApplicationExport(1, 'overtime_applications_20261104_110000.csv'))->handle();

            $export = Storage::disk('local')->get('overtime-application-exports/overtime_applications_20261104_110000.csv');
            $rows = array_map('str_getcsv', explode("\n", trim($export)));
            $this->assertSame(OvertimeApplication::IMPORT_EXPORT_COLUMNS, $rows[0]);
            $this->assertSame(['E001', '2026-11-03', '90', 'Project work', 'unknown'], $rows[1]);
        } finally {
            $this->dropWorkflowTables();
        }
    }

    private function createWorkflowTables(): void
    {
        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('overtime_approval_flow_id')->nullable();
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
        Schema::create('approval_flow_detail_user', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('overtime_applications', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('overtime_minutes');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('attendances2026', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('total_overtime_minutes')->nullable();
            $table->unsignedInteger('approved_overtime_minutes')->nullable();
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
        Schema::create('approved_application_effect_snapshots', function ($table): void {
            $table->id();
            $table->morphs('approvable');
            $table->json('snapshot');
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
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function createEmailTables(): void
    {
        Schema::create('email_templates', function ($table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('template_type');
            $table->text('email_content')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('email_template_assignments', function ($table): void {
            $table->id();
            $table->string('application_type');
            $table->string('application_action');
            $table->unsignedBigInteger('email_template_id');
            $table->string('status')->default('active');
            $table->boolean('send_to_approver')->default(false);
            $table->boolean('send_to_another_approver')->default(false);
            $table->boolean('send_to_employee')->default(false);
            $table->timestamps();
        });
        Schema::create('email_configurations', function ($table): void {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->softDeletes();
        });
    }

    private function dropWorkflowTables(): void
    {
        Schema::dropIfExists('approval_status_details');
        Schema::dropIfExists('approved_application_effect_snapshots');
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('approval_flow_detail_user');
        Schema::dropIfExists('attendances2026');
        Schema::dropIfExists('overtime_applications');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
