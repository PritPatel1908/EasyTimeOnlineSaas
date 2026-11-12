<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SettingsController;
use App\Models\Tenant\EmailTemplate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmailTemplatePageTest extends TestCase
{
    public function test_email_template_submission_persists_the_template(): void
    {
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

        $response = app(SettingsController::class)->storeEmailTemplate(Request::create(
            '/email-template',
            'POST',
            [
                'title' => 'Manual punch update',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => 'Request from {{ employee_name }}',
                'status' => 'active',
                'email_template_form' => 'add',
            ]
        ));
        $secondResponse = app(SettingsController::class)->storeEmailTemplate(Request::create(
            '/email-template',
            'POST',
            [
                'title' => 'Manual punch reminder',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => 'Another request from {{ employee_name }}',
                'status' => 'active',
                'email_template_form' => 'add',
            ]
        ));

        $this->assertSame(url('email-template'), $response->getTargetUrl());
        $this->assertSame(url('email-template'), $secondResponse->getTargetUrl());
        $this->assertSame(
            2,
            EmailTemplate::query()
                ->where('template_type', 'manual_punch_application_approve_reject')
                ->count()
        );
        $this->assertDatabaseHas('email_templates', [
            'title' => 'Manual punch update',
            'template_type' => 'manual_punch_application_approve_reject',
            'email_content' => 'Request from {{ employee_name }}',
            'status' => 'active',
        ]);
    }

    public function test_email_template_update_persists_changes_to_the_selected_template(): void
    {
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

        $template = EmailTemplate::query()->create([
            'title' => 'Manual punch update',
            'template_type' => 'manual_punch_application_approve_reject',
            'email_content' => 'Original content',
            'status' => 'active',
        ]);

        $response = app(SettingsController::class)->updateEmailTemplate(
            Request::create('/email-template/'.$template->id, 'PUT', [
                'title' => 'Updated manual punch',
                'template_type' => 'manual_punch_application_approve_reject',
                'email_content' => 'Updated content',
                'status' => 'inactive',
                'email_template_form' => 'edit',
                'email_template_id' => $template->id,
            ]),
            'tenant',
            (string) $template->id
        );

        $this->assertSame(url('email-template'), $response->getTargetUrl());
        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'title' => 'Updated manual punch',
            'email_content' => 'Updated content',
            'status' => 'inactive',
        ]);
    }

    public function test_application_templates_expose_form_and_approval_variables_only_for_supported_types(): void
    {
        $variablesByType = EmailTemplate::variablesByTemplateType();
        $approvalKeys = array_column(
            $variablesByType['leave_application_approve_reject'],
            'key'
        );

        foreach (array_keys(EmailTemplate::APPLICATION_VARIABLES) as $applicationType) {
            $this->assertNotContains($applicationType, EmailTemplate::TEMPLATE_TYPES);
            $this->assertContains($applicationType.'_approve_reject', EmailTemplate::TEMPLATE_TYPES);
            $this->assertArrayNotHasKey($applicationType, $variablesByType);
        }
        $this->assertContains('employee_name', $approvalKeys);
        $this->assertContains('employee_code', $approvalKeys);
        foreach ([
            'employee_names',
            'company_names',
            'location_names',
            'department_names',
            'sub_department_names',
            'category_names',
            'sub_category_names',
            'designation_names',
            'grade_names',
            'unit_names',
            'bus_route_names',
        ] as $filterVariable) {
            $this->assertNotContains($filterVariable, $approvalKeys);
        }
        $this->assertContains('from_date', $approvalKeys);
        $this->assertContains('approval_history', $approvalKeys);
        $this->assertContains('current_approval_level', $approvalKeys);
        $this->assertContains('total_approvers', $approvalKeys);
        $this->assertContains('previous_level_approval_history', $approvalKeys);
        $this->assertArrayNotHasKey('birthday_notification', $variablesByType);
        $this->assertArrayNotHasKey('joining_notification', $variablesByType);
        $this->assertArrayNotHasKey('onboarding_notification', $variablesByType);
        $this->assertContains('birthday_notification', EmailTemplate::TEMPLATE_TYPES);
        $this->assertContains('joining_notification', EmailTemplate::TEMPLATE_TYPES);
        $this->assertContains('onboarding_notification', EmailTemplate::TEMPLATE_TYPES);
    }

    public function test_email_template_page_loads_templates_from_the_database(): void
    {
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

        DB::table('email_templates')->insert([
            [
                'title' => 'Leave request',
                'template_type' => 'leave_application_approve_reject',
                'email_content' => 'Leave request notification',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Birthday notice',
                'template_type' => 'birthday_notification',
                'email_content' => 'Birthday notification',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $view = app(SettingsController::class)->show('tenant', 'email-template');
        $templates = $view->getData()['emailTemplates'];

        $this->assertSame(
            ['birthday_notification', 'leave_application_approve_reject'],
            $templates->pluck('template_type')->all()
        );
        $this->assertSame(
            ['Birthday notice', 'Leave request'],
            $templates->pluck('title')->all()
        );
    }
}
