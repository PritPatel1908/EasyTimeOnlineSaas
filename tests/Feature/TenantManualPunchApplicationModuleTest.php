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
use App\Jobs\Tenant\SendManualPunchApprovalEmails;
use App\Jobs\Tenant\SendApplicationCancellationEmails;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\ApprovedApplicationEffectSnapshot;
use App\Models\Tenant\User;
use App\Services\Tenant\ManualPunchApplicationWorkflow;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Carbon;
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

    public function test_show_groups_employees_by_their_manual_punch_approval_flow(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                ['id' => 10, 'approval_flow_code' => 'first-flow', 'status' => 1],
                ['id' => 11, 'approval_flow_code' => 'second-flow', 'status' => 1],
            ]);
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'manual_punch_approval_flow_id' => 10],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'manual_punch_approval_flow_id' => 11],
            ]);
            DB::table('manual_punch_applications')->insert([
                'id' => 1,
                'punch_type' => '1',
                'punch_date' => '2026-10-24',
                'punch_time' => '09:30:00',
            ]);
            DB::table('manual_punch_application_users')->insert([
                ['manual_punch_application_id' => 1, 'user_id' => 101],
                ['manual_punch_application_id' => 1, 'user_id' => 102],
            ]);
            DB::table('approval_statuses')->insert([
                [
                    'approvable_id' => 1,
                    'approvable_type' => ManualPunchApplication::class,
                    'approval_status' => 'pending',
                    'approval_flow_id' => 10,
                ],
                [
                    'approvable_id' => 1,
                    'approvable_type' => ManualPunchApplication::class,
                    'approval_status' => 'pending',
                    'approval_flow_id' => 11,
                ],
            ]);

            $route = Mockery::mock();
            $route->shouldReceive('parameter')->with('manual_punch_application', null)->andReturn(1);
            $request = Request::create('/');
            $request->setRouteResolver(static fn () => $route);

            $view = app(ManualPunchApplicationController::class)->show($request);
            $employeesByFlow = $view->getData()['employeesByApprovalFlow'];

            $this->assertSame('Employee One', $employeesByFlow->get(10)->first()->name);
            $this->assertSame('Employee Two', $employeesByFlow->get(11)->first()->name);
        } finally {
            $this->dropWorkflowTables();
        }
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
                'created_by' => 101,
                'updated_by' => 101,
            ]);
            $this->assertDatabaseHas('approval_statuses', [
                'approvable_id' => $applicationId,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 11,
                'created_by' => 101,
                'updated_by' => 101,
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
                'created_by' => 101,
                'updated_by' => 101,
            ]);
            $this->assertDatabaseHas('approval_status_details', [
                'approval_status_id' => DB::table('approval_statuses')
                    ->where('approvable_id', $applicationId)
                    ->where('approval_flow_id', 10)
                    ->value('id'),
                'level' => 2,
                'approval_flow_detail_id' => 22,
                'approval_status' => 'pending',
                'created_by' => 101,
                'updated_by' => 101,
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
            ], [102], 102);
            Bus::assertDispatched(CreateManualPunchApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === $applicationId
                && $job->approvalFlowIds === [11]
                && $job->actorId === 102
            ));
            (new CreateManualPunchApplicationApprovals($applicationId, [11], 102))->handle();

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
                'created_by' => 101,
                'updated_by' => 102,
            ]);
            $this->assertDatabaseHas('approval_status_details', [
                'approval_status_id' => DB::table('approval_statuses')
                    ->where('approvable_id', $applicationId)
                    ->where('approval_flow_id', 11)
                    ->value('id'),
                'approval_flow_detail_id' => 21,
                'approval_status' => 'pending',
                'created_by' => 102,
                'updated_by' => 102,
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

    public function test_initial_and_edited_application_emails_use_the_submission_time_and_reach_approvers(): void
    {
        $this->createWorkflowTables();

        try {
            Bus::fake();
            Carbon::setTestNow('2026-11-12 12:00:00');
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'manual-punch-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                ['id' => 20, 'level' => 1, 'approval_flow_id' => 10],
                ['id' => 22, 'level' => 2, 'approval_flow_id' => 10],
            ]);
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'email' => 'employee@example.test', 'manual_punch_approval_flow_id' => 10],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'email' => null, 'manual_punch_approval_flow_id' => 10],
                ['id' => 201, 'name' => 'Approver One', 'code' => 'A001', 'email' => 'approver-one@example.test', 'manual_punch_approval_flow_id' => null],
                ['id' => 202, 'name' => 'Approver Two', 'code' => 'A002', 'email' => 'approver-two@example.test', 'manual_punch_approval_flow_id' => null],
            ]);
            DB::table('approval_flow_detail_user')->insert([
                ['approval_flow_detail_id' => 20, 'user_id' => 201, 'created_at' => now(), 'updated_at' => now()],
                ['approval_flow_detail_id' => 22, 'user_id' => 202, 'created_at' => now(), 'updated_at' => now()],
            ]);
            DB::table('email_templates')->insert([
                'id' => 1,
                'title' => 'Punch {{ punch_date }}',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => 'Hello {{ employee_name }} ({{ employee_code }}): {{ punch_type }} {{ punch_date }} {{ punch_time }} {{ reason }}; Submitted {{ submitted_on }}; {{ application_status }}; {{ total_approval_levels }}; {{ total_approvers }}; {{ current_approval_level }}; {{ current_level_approver_names }}; {{ previous_level_approval_history }}; {{ approval_history }}; {{ unknown_value }}'."\nSecond template line",
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('email_template_assignments')->insert([
                'application_type' => 'manual_punch_application',
                'application_action' => 'initial',
                'email_template_id' => 1,
                'status' => 'active',
                'send_to_approver' => true,
                'send_to_employee' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('email_configurations')->insert([
                ['key' => 'transport', 'value' => 'smtp'],
                ['key' => 'host', 'value' => 'smtp.example.test'],
                ['key' => 'port', 'value' => '587'],
                ['key' => 'username', 'value' => 'mailer'],
                ['key' => 'password', 'value' => 'secret'],
                ['key' => 'encryption', 'value' => 'tls'],
                ['key' => 'from_address', 'value' => 'noreply@example.test'],
                ['key' => 'from_name', 'value' => 'EasyTime'],
            ]);

            $workflow = app(ManualPunchApplicationWorkflow::class);
            $application = $workflow->create([
                'punch_type' => '1',
                'punch_date' => '2026-10-20',
                'punch_time' => '09:15:00',
                'reason' => 'Correction',
            ], [101, 102], 101)->firstOrFail();

            $sentEmails = [];
            $mailer = Mockery::mock(Mailer::class);
            $mailer->shouldReceive('html')
                ->times(6)
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
                ->twice()
                ->with('noreply@example.test', 'EasyTime');

            $mailManager = Mockery::mock(MailManager::class);
            $mailManager->shouldReceive('build')
                ->twice()
                ->with(Mockery::on(static fn (array $config): bool => (
                    $config['transport'] === 'smtp'
                    && $config['host'] === 'smtp.example.test'
                    && $config['port'] === 587
                    && $config['from']['address'] === 'noreply@example.test'
                )))
                ->andReturn($mailer);
            app()->instance('mail.manager', $mailManager);

            (new CreateManualPunchApplicationApprovals((int) $application->id, [10], 101))->handle();

            $this->assertEqualsCanonicalizing([
                'employee@example.test',
                'approver-one@example.test',
                'approver-two@example.test',
            ], array_keys($sentEmails));
            $employeeEmail = $sentEmails['employee@example.test'][0];
            $this->assertSame('Punch 2026-10-20', $employeeEmail['subject']);
            $this->assertStringContainsString('Employee One (E001)', $employeeEmail['body']);
            $this->assertStringContainsString('In Punch 2026-10-20 09:15:00 Correction', $employeeEmail['body']);
            $this->assertStringContainsString('Submitted 2026-11-12 12:00:00', $employeeEmail['body']);
            $this->assertStringContainsString("<br />\nSecond template line", $employeeEmail['body']);
            $this->assertStringNotContainsString('{{', $employeeEmail['body']);
            $this->assertSame(
                $sentEmails['approver-one@example.test'][0]['body'],
                $sentEmails['approver-two@example.test'][0]['body'],
            );
            $this->assertStringContainsString('Employee One, Employee Two (E001, E002)', $sentEmails['approver-one@example.test'][0]['body']);
            $this->assertStringContainsString('pending; 2; 2; 1; Approver One', $sentEmails['approver-one@example.test'][0]['body']);
            $this->assertStringNotContainsString('{{', $sentEmails['approver-one@example.test'][0]['body']);
            $this->assertDatabaseCount('approval_statuses', 1);
            $this->assertDatabaseCount('approval_status_details', 2);

            Carbon::setTestNow('2026-11-12 12:15:00');
            $workflow->update($application, [
                'punch_type' => '2',
                'punch_date' => '2026-10-21',
                'punch_time' => '18:00:00',
                'reason' => 'Updated correction',
            ], [101, 102], 202);
            Bus::assertDispatched(CreateManualPunchApplicationApprovals::class, static fn ($job): bool => (
                $job->applicationId === (int) $application->id
                && $job->actorId === 202
            ));

            (new CreateManualPunchApplicationApprovals((int) $application->id, [10], 202))->handle();

            $this->assertCount(2, $sentEmails['approver-one@example.test']);
            $this->assertCount(2, $sentEmails['approver-two@example.test']);
            $this->assertStringContainsString('Submitted 2026-11-12 12:15:00', $sentEmails['approver-one@example.test'][1]['body']);
            $this->assertStringContainsString('Out Punch 2026-10-21 18:00:00 Updated correction', $sentEmails['approver-one@example.test'][1]['body']);
            $this->assertSame(
                $sentEmails['approver-one@example.test'][1]['body'],
                $sentEmails['approver-two@example.test'][1]['body'],
            );
        } finally {
            Carbon::setTestNow();
            $this->dropWorkflowTables();
        }
    }

    public function test_approval_emails_reach_employees_and_approvers_on_other_levels_with_rendered_history(): void
    {
        $this->createWorkflowTables();

        try {
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'manual-punch-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                ['id' => 20, 'level' => 1, 'approval_flow_id' => 10],
                ['id' => 21, 'level' => 2, 'approval_flow_id' => 10],
                ['id' => 22, 'level' => 3, 'approval_flow_id' => 10],
            ]);
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'email' => 'employee-one@example.test'],
                ['id' => 102, 'name' => 'Employee Two', 'code' => 'E002', 'email' => 'employee-two@example.test'],
                ['id' => 201, 'name' => 'Approver One', 'code' => 'A001', 'email' => 'approver-one@example.test'],
                ['id' => 202, 'name' => 'Approver Two', 'code' => 'A002', 'email' => 'approver-two@example.test'],
                ['id' => 203, 'name' => 'Approver Three', 'code' => 'A003', 'email' => 'approver-three@example.test'],
            ]);
            DB::table('manual_punch_applications')->insert([
                'id' => 1,
                'punch_type' => '1',
                'punch_date' => '2026-10-24',
                'punch_time' => '09:30:00',
                'reason' => 'Correction',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('manual_punch_application_users')->insert([
                ['manual_punch_application_id' => 1, 'user_id' => 101],
                ['manual_punch_application_id' => 1, 'user_id' => 102],
            ]);
            DB::table('approval_flow_detail_user')->insert([
                ['approval_flow_detail_id' => 20, 'user_id' => 201, 'created_at' => now(), 'updated_at' => now()],
                ['approval_flow_detail_id' => 21, 'user_id' => 202, 'created_at' => now(), 'updated_at' => now()],
                ['approval_flow_detail_id' => 22, 'user_id' => 203, 'created_at' => now(), 'updated_at' => now()],
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 100,
                'approvable_id' => 1,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'pending',
                'approval_flow_id' => 10,
            ]);
            DB::table('approval_status_details')->insert([
                [
                    'id' => 201,
                    'level' => 1,
                    'approval_status_id' => 100,
                    'approval_flow_detail_id' => 20,
                    'approval_status' => 'approved',
                    'user_id' => 201,
                    'timestamp' => '2026-11-12 10:00:00',
                ],
                [
                    'id' => 202,
                    'level' => 2,
                    'approval_status_id' => 100,
                    'approval_flow_detail_id' => 21,
                    'approval_status' => 'approved',
                    'user_id' => 202,
                    'timestamp' => '2026-11-12 11:00:00',
                ],
                [
                    'id' => 203,
                    'level' => 3,
                    'approval_status_id' => 100,
                    'approval_flow_detail_id' => 22,
                    'approval_status' => 'pending',
                    'user_id' => null,
                    'timestamp' => null,
                ],
            ]);
            DB::table('email_templates')->insert([
                'id' => 1,
                'title' => 'Punch {{ punch_date }}: {{ application_status }}',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => '{{ employee_name }} ({{ employee_code }}); {{ punch_type }} {{ punch_date }} {{ punch_time }} {{ reason }}; {{ application_status }}; {{ total_approval_levels }}; {{ total_approvers }}; {{ current_approval_level }}; {{ current_level_approver_names }}; {{ previous_level_approval_history }}; {{ approval_history }}',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('email_template_assignments')->insert([
                'application_type' => 'manual_punch_application',
                'application_action' => 'approve',
                'email_template_id' => 1,
                'status' => 'active',
                'send_to_another_approver' => true,
                'send_to_employee' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('email_configurations')->insert([
                ['key' => 'from_address', 'value' => 'noreply@example.test'],
                ['key' => 'from_name', 'value' => 'EasyTime'],
            ]);

            $sentEmails = [];
            $mailer = Mockery::mock(Mailer::class);
            $mailer->shouldReceive('html')
                ->times(8)
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
                    $sentEmails[$recipient] = ['subject' => $subject, 'body' => $body];
                });
            $mailer->shouldReceive('alwaysFrom')->twice()->with('noreply@example.test', 'EasyTime');

            $mailManager = Mockery::mock(MailManager::class);
            $mailManager->shouldReceive('build')
                ->twice()
                ->with(Mockery::on(static fn (array $config): bool => (
                    $config['transport'] === 'mail'
                    && $config['from']['address'] === 'noreply@example.test'
                )))
                ->andReturn($mailer);
            app()->instance('mail.manager', $mailManager);

            (new SendManualPunchApprovalEmails(1, 202, 202))->handle();

            $this->assertEqualsCanonicalizing([
                'employee-one@example.test',
                'employee-two@example.test',
                'approver-one@example.test',
                'approver-three@example.test',
            ], array_keys($sentEmails));
            $this->assertStringContainsString('Employee One (E001)', $sentEmails['employee-one@example.test']['body']);
            $this->assertStringContainsString('pending; 3; 3; 2; Approver Two', $sentEmails['employee-one@example.test']['body']);
            $this->assertStringContainsString('Level 1: Approver One approved on 2026-11-12 10:00:00', $sentEmails['employee-one@example.test']['body']);
            $this->assertStringContainsString('Level 2: Approver Two approved on 2026-11-12 11:00:00', $sentEmails['employee-one@example.test']['body']);
            $this->assertSame('Punch 2026-10-24: pending', $sentEmails['approver-one@example.test']['subject']);
            $this->assertStringContainsString('Employee One, Employee Two (E001, E002)', $sentEmails['approver-three@example.test']['body']);

            DB::table('email_template_assignments')->insert([
                'application_type' => 'manual_punch_application',
                'application_action' => 'reject',
                'email_template_id' => 1,
                'status' => 'active',
                'send_to_another_approver' => true,
                'send_to_employee' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('approval_status_details')->where('id', 202)->update([
                'approval_status' => 'rejected',
                'rejection_reason' => 'Incorrect punch details.',
            ]);
            DB::table('approval_status_details')->where('id', 203)->update(['approval_status' => 'cancelled']);
            DB::table('approval_statuses')->where('id', 100)->update(['approval_status' => 'rejected']);
            $sentEmails = [];

            (new SendManualPunchApprovalEmails(1, 202, 202, 'rejected'))->handle();

            $this->assertEqualsCanonicalizing([
                'employee-one@example.test',
                'employee-two@example.test',
                'approver-one@example.test',
                'approver-three@example.test',
            ], array_keys($sentEmails));
            $this->assertSame('Punch 2026-10-24: rejected', $sentEmails['employee-one@example.test']['subject']);
            $this->assertStringContainsString('Incorrect punch details.', $sentEmails['employee-one@example.test']['body']);
        } finally {
            $this->dropWorkflowTables();
        }
    }

    public function test_cancellation_email_uses_cancel_assignment_and_notifies_employee_and_other_approvers(): void
    {
        $this->createWorkflowTables();
        Schema::create('approved_application_effect_snapshots', function ($table): void {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->json('snapshot')->nullable();
            $table->string('cancellation_status')->nullable();
            $table->unsignedBigInteger('cancellation_requested_by')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->text('cancellation_error')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        try {
            DB::table('users')->insert([
                ['id' => 101, 'name' => 'Employee One', 'code' => 'E001', 'email' => 'employee@example.test'],
                ['id' => 202, 'name' => 'Cancelling Approver', 'code' => 'A002', 'email' => 'actor@example.test'],
                ['id' => 203, 'name' => 'Other Approver', 'code' => 'A003', 'email' => 'other@example.test'],
            ]);
            DB::table('approval_flows')->insert([
                'id' => 10,
                'approval_flow_code' => 'manual-punch-flow',
                'status' => 1,
            ]);
            DB::table('approval_flow_details')->insert([
                'id' => 20,
                'level' => 1,
                'approval_flow_id' => 10,
            ]);
            DB::table('approval_flow_detail_user')->insert([
                ['approval_flow_detail_id' => 20, 'user_id' => 202, 'created_at' => now(), 'updated_at' => now()],
                ['approval_flow_detail_id' => 20, 'user_id' => 203, 'created_at' => now(), 'updated_at' => now()],
            ]);
            DB::table('manual_punch_applications')->insert([
                'id' => 1,
                'punch_type' => '1',
                'punch_date' => '2026-11-13',
                'punch_time' => '09:30:00',
                'reason' => 'Correction',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('manual_punch_application_users')->insert([
                'manual_punch_application_id' => 1,
                'user_id' => 101,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('approval_statuses')->insert([
                'id' => 100,
                'approvable_id' => 1,
                'approvable_type' => ManualPunchApplication::class,
                'approval_status' => 'cancelled',
                'approval_flow_id' => 10,
            ]);
            DB::table('approval_status_details')->insert([
                'id' => 201,
                'level' => 1,
                'approval_status_id' => 100,
                'approval_flow_detail_id' => 20,
                'approval_status' => 'cancelled',
                'user_id' => 202,
            ]);
            DB::table('email_templates')->insert([
                'id' => 1,
                'title' => 'Cancelled punch {{ punch_date }}',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => '{{ employee_name }} {{ application_status }} {{ cancellation_reason }} {{ cancelled_by }} {{ cancelled_at }}',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('email_template_assignments')->insert([
                'application_type' => 'manual_punch_application',
                'application_action' => 'cancel',
                'email_template_id' => 1,
                'status' => 'active',
                'send_to_another_approver' => true,
                'send_to_employee' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('email_configurations')->insert([
                ['key' => 'from_address', 'value' => 'noreply@example.test'],
                ['key' => 'from_name', 'value' => 'EasyTime'],
            ]);
            ApprovedApplicationEffectSnapshot::query()->create([
                'approvable_type' => ManualPunchApplication::class,
                'approvable_id' => 1,
                'cancellation_status' => 'completed',
                'cancelled_by' => 202,
                'cancellation_reason' => 'Duplicate request',
                'cancelled_at' => '2026-11-13 12:00:00',
            ]);

            $sentEmails = [];
            $mailer = Mockery::mock(Mailer::class);
            $mailer->shouldReceive('html')
                ->twice()
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
                    $sentEmails[$recipient] = ['subject' => $subject, 'body' => $body];
                });
            $mailer->shouldReceive('alwaysFrom')->once()->with('noreply@example.test', 'EasyTime');

            $mailManager = Mockery::mock(MailManager::class);
            $mailManager->shouldReceive('build')
                ->once()
                ->andReturn($mailer);
            app()->instance('mail.manager', $mailManager);

            (new SendApplicationCancellationEmails(ManualPunchApplication::class, 1, 202))->handle();

            $this->assertEqualsCanonicalizing([
                'employee@example.test',
                'other@example.test',
            ], array_keys($sentEmails));
            $this->assertSame('Cancelled punch 2026-11-13', $sentEmails['employee@example.test']['subject']);
            $this->assertStringContainsString('Employee One cancelled Duplicate request Cancelling Approver 2026-11-13 12:00:00', $sentEmails['employee@example.test']['body']);
            $this->assertStringContainsString('Employee One cancelled Duplicate request Cancelling Approver 2026-11-13 12:00:00', $sentEmails['other@example.test']['body']);
        } finally {
            Schema::dropIfExists('approved_application_effect_snapshots');
            $this->dropWorkflowTables();
        }
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
            $table->string('email')->nullable();
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
        Schema::create('approval_flow_detail_user', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('approval_flow_detail_id');
            $table->unsignedBigInteger('user_id');
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
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
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
            $table->string('value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function dropWorkflowTables(): void
    {
        Schema::dropIfExists('email_configurations');
        Schema::dropIfExists('email_template_assignments');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('approval_status_details');
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('manual_punch_application_users');
        Schema::dropIfExists('manual_punch_applications');
        Schema::dropIfExists('approval_flow_detail_user');
        Schema::dropIfExists('approval_flow_details');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('users');
    }
}
