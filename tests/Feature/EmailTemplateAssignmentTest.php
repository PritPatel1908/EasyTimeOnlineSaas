<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SettingsController;
use App\Models\Tenant\EmailTemplate;
use App\Models\Tenant\EmailTemplateAssignment;
use Database\Seeders\Tenant\EmailTemplateDefaultsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmailTemplateAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('template_type');
            $table->text('email_content')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::create('email_template_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('application_type', 100);
            $table->string('application_action', 100);
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->boolean('send_to_approver')->default(false);
            $table->boolean('send_to_another_approver')->default(false);
            $table->boolean('send_to_employee')->default(false);
            $table->unique(
                ['application_type', 'application_action'],
                'email_template_assignment_type_action_unique'
            );
            $table->timestamps();
        });

        DB::table('email_templates')->insert([
            [
                'title' => 'Leave application',
                'template_type' => 'leave_application_approve_reject',
                'email_content' => 'Leave application notification',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Manual punch application',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => 'Manual punch application notification',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_assignments_are_saved_and_only_the_application_type_action_pair_must_be_unique(): void
    {
        $controller = app(SettingsController::class);
        $leaveTemplateId = DB::table('email_templates')
            ->where('template_type', 'leave_application_approve_reject')
            ->value('id');
        $punchTemplateId = DB::table('email_templates')
            ->where('template_type', 'manual_punch_application_approve_reject')
            ->value('id');

        $response = $controller->storeEmailTemplateAssignment(Request::create(
            '/assign-email-templates',
            'POST',
            [
                'application_type' => 'leave_application',
                'application_action' => 'initial',
                'email_template_id' => $leaveTemplateId,
                'status' => 'active',
                'send_to_approver' => '1',
                'send_to_another_approver' => '1',
                'send_to_employee' => '1',
            ]
        ));
        $anotherTypeResponse = $controller->storeEmailTemplateAssignment(Request::create(
            '/assign-email-templates',
            'POST',
            [
                'application_type' => 'manual_punch_application',
                'application_action' => 'initial',
                'email_template_id' => $punchTemplateId,
                'status' => 'active',
            ]
        ));
        $approvalResponse = $controller->storeEmailTemplateAssignment(Request::create(
            '/assign-email-templates',
            'POST',
            [
                'application_type' => 'leave_application',
                'application_action' => 'approve',
                'email_template_id' => $leaveTemplateId,
                'status' => 'active',
                'send_to_approver' => '1',
                'send_to_another_approver' => '1',
                'send_to_employee' => '1',
            ]
        ));
        $cancellationResponse = $controller->storeEmailTemplateAssignment(Request::create(
            '/assign-email-templates',
            'POST',
            [
                'application_type' => 'leave_application',
                'application_action' => 'cancel',
                'email_template_id' => $leaveTemplateId,
                'status' => 'active',
                'send_to_another_approver' => '1',
                'send_to_employee' => '1',
            ]
        ));

        $this->assertSame(url('assign-email-templates'), $response->getTargetUrl());
        $this->assertSame(url('assign-email-templates'), $anotherTypeResponse->getTargetUrl());
        $this->assertSame(url('assign-email-templates'), $approvalResponse->getTargetUrl());
        $this->assertSame(url('assign-email-templates'), $cancellationResponse->getTargetUrl());
        $this->assertDatabaseCount('email_template_assignments', 4);
        $this->assertDatabaseHas('email_template_assignments', [
            'application_type' => 'leave_application',
            'application_action' => 'initial',
            'send_to_approver' => true,
            'send_to_another_approver' => false,
            'send_to_employee' => false,
        ]);
        $this->assertDatabaseHas('email_template_assignments', [
            'application_type' => 'leave_application',
            'application_action' => 'approve',
            'send_to_approver' => false,
            'send_to_another_approver' => true,
            'send_to_employee' => true,
        ]);
        $this->assertDatabaseHas('email_template_assignments', [
            'application_type' => 'leave_application',
            'application_action' => 'cancel',
            'send_to_approver' => false,
            'send_to_another_approver' => true,
            'send_to_employee' => true,
        ]);

        try {
            $controller->storeEmailTemplateAssignment(Request::create(
                '/assign-email-templates',
                'POST',
                [
                    'application_type' => 'leave_application_approve_reject',
                    'application_action' => 'initial',
                    'email_template_id' => $leaveTemplateId,
                    'status' => 'active',
                ]
            ));
            $this->fail('A duplicate application type and action pair should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('application_action', $exception->errors());
        }
    }

    public function test_default_templates_and_assignments_are_seeded_for_each_application_action_idempotently(): void
    {
        $seeder = app(EmailTemplateDefaultsSeeder::class);

        $seeder->run();

        $expectedCount = count(EmailTemplate::APPLICATION_VARIABLES) * 4;
        $this->assertSame($expectedCount, EmailTemplateAssignment::query()->count());
        $this->assertSame($expectedCount + 2, EmailTemplate::query()->count());
        $this->assertDatabaseHas('email_template_assignments', [
            'application_type' => 'leave_application',
            'application_action' => 'initial',
            'send_to_approver' => true,
        ]);
        $this->assertDatabaseHas('email_template_assignments', [
            'application_type' => 'leave_application',
            'application_action' => 'cancel',
            'send_to_another_approver' => true,
            'send_to_employee' => true,
        ]);

        $cancelTemplate = EmailTemplate::query()
            ->where('template_type', 'leave_application_approve_reject')
            ->where('title', 'Leave Application - Cancel')
            ->firstOrFail();
        $this->assertStringContainsString('{{from_date}}', $cancelTemplate->email_content);
        $this->assertStringContainsString('{{cancellation_reason}}', $cancelTemplate->email_content);
        $this->assertStringContainsString('{{cancelled_at}}', $cancelTemplate->email_content);

        $cancelTemplate->update(['email_content' => 'Customized by tenant']);
        EmailTemplateAssignment::query()
            ->where('application_type', 'leave_application')
            ->where('application_action', 'cancel')
            ->update(['status' => 'inactive']);

        $seeder->run();

        $this->assertSame($expectedCount, EmailTemplateAssignment::query()->count());
        $this->assertSame($expectedCount + 2, EmailTemplate::query()->count());
        $this->assertDatabaseHas('email_templates', [
            'id' => $cancelTemplate->id,
            'email_content' => 'Customized by tenant',
        ]);
        $this->assertDatabaseHas('email_template_assignments', [
            'application_type' => 'leave_application',
            'application_action' => 'cancel',
            'status' => 'inactive',
        ]);
    }
}
