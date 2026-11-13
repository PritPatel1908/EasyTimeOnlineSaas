<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\EmailTemplate;
use App\Models\Tenant\EmailTemplateAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailTemplateDefaultsSeeder extends Seeder
{
    private const APPLICATION_ACTIONS = ['initial', 'approve', 'reject', 'cancel'];

    public function run(): void
    {
        foreach (array_keys(EmailTemplate::APPLICATION_VARIABLES) as $applicationType) {
            foreach (self::APPLICATION_ACTIONS as $action) {
                $templateType = $applicationType.'_approve_reject';
                $title = Str::headline($applicationType).' - '.Str::headline($action);
                $template = EmailTemplate::query()->firstOrCreate(
                    [
                        'template_type' => $templateType,
                        'title' => $title,
                    ],
                    [
                        'email_content' => $this->templateContent($applicationType, $action),
                        'status' => 'active',
                    ],
                );

                EmailTemplateAssignment::query()->firstOrCreate(
                    [
                        'application_type' => $applicationType,
                        'application_action' => $action,
                    ],
                    [
                        'email_template_id' => $template->id,
                        'status' => 'active',
                        'send_to_approver' => $action === 'initial',
                        'send_to_another_approver' => $action !== 'initial',
                        'send_to_employee' => $action !== 'initial',
                    ],
                );
            }
        }
    }

    private function templateContent(string $applicationType, string $action): string
    {
        $applicationName = Str::headline($applicationType);
        $actionText = match ($action) {
            'initial' => 'submitted',
            'approve' => 'approved',
            'reject' => 'rejected',
            'cancel' => 'cancelled',
        };
        $lines = [
            'Hello {{employee_name}},',
            '',
            "Your {$applicationName} has been {$actionText}.",
            '',
            'Application details:',
            ...array_map(
                static fn (array $variable): string => '- '.$variable['label'].': {{'.$variable['key'].'}}',
                EmailTemplate::APPLICATION_VARIABLES[$applicationType],
            ),
            '- Employee code: {{employee_code}}',
            '- Application status: {{application_status}}',
        ];

        if ($action === 'cancel') {
            $lines[] = '- Cancellation reason: {{cancellation_reason}}';
            $lines[] = '- Cancelled by: {{cancelled_by}}';
            $lines[] = '- Cancelled at: {{cancelled_at}}';
        } else {
            $lines[] = '- Approval level: {{current_approval_level}} / {{total_approval_levels}}';
            $lines[] = '- Current approvers: {{current_level_approver_names}}';
            $lines[] = '- Previous approval history: {{previous_level_approval_history}}';
            $lines[] = '- Approval history: {{approval_history}}';
        }

        return implode("\n", $lines);
    }
}
