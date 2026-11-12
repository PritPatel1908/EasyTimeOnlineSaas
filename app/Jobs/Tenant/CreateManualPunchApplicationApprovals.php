<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ApprovalProcessor;
use App\Models\Tenant\EmailConfiguration;
use App\Models\Tenant\EmailTemplateAssignment;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use RuntimeException;

class CreateManualPunchApplicationApprovals implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, int>  $approvalFlowIds
     */
    public function __construct(
        public int $applicationId,
        public array $approvalFlowIds,
        public int $actorId,
    ) {}

    public function handle(): void
    {
        $application = ManualPunchApplication::query()->findOrFail($this->applicationId);

        ApprovalProcessor::CreateManualPunchApplicationApprovals(
            $application,
            $this->approvalFlowIds,
            $this->actorId,
        );

        $this->sendInitialEmails($application);
    }

    private function sendInitialEmails(ManualPunchApplication $application): void
    {
        $assignment = EmailTemplateAssignment::query()
            ->with('emailTemplate')
            ->where('application_type', 'manual_punch_application')
            ->where('application_action', 'initial')
            ->where('status', 'active')
            ->first();

        if ($assignment === null
            || $assignment->emailTemplate === null
            || $assignment->emailTemplate->status !== 'active') {
            return;
        }

        $employees = $application->Users()->orderBy('users.id')->get();
        $employeeRecipients = $this->recipientsWithEmail($employees);
        $approvalStatuses = $application->approval_statuses()
            ->with('approval_status_details.approval_flow_detail.users')
            ->get();
        $approvalDetails = $approvalStatuses->flatMap(
            static fn ($status) => $status->approval_status_details,
        );
        $approvers = $approvalDetails->flatMap(
            static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection(),
        );
        $approverRecipients = $assignment->send_to_approver
            ? $this->recipientsWithEmail($approvers)
            : collect();

        if ($employeeRecipients->isEmpty() && $approverRecipients->isEmpty()) {
            return;
        }

        $settings = EmailConfiguration::settings();
        $mailerConfig = $this->mailerConfig($settings);
        /** @var Mailer $mailer */
        $mailer = app('mail.manager')->build($mailerConfig);
        $mailer->alwaysFrom(
            $mailerConfig['from']['address'],
            $mailerConfig['from']['name'],
        );

        $allEmployeeNames = $employees->pluck('name')->filter()->implode(', ');
        $allEmployeeCodes = $employees->pluck('code')->filter()->implode(', ');
        $approvalVariables = $this->approvalVariables($approvalStatuses, $approvers);
        $applicationVariables = [
            'punch_type' => ManualPunch::punchTypeLabel($application->punch_type),
            'punch_date' => $application->punch_date?->format('Y-m-d') ?? '',
            'punch_time' => $application->punch_time?->format('H:i:s') ?? '',
            'reason' => $application->reason ?? '',
            'submitted_on' => $application->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];

        foreach ($employeeRecipients as $employee) {
            $variables = [
                ...$applicationVariables,
                ...$approvalVariables,
                'employee_name' => $employee->name ?? '',
                'employee_code' => $employee->code ?? '',
            ];
            $this->sendTemplateEmail(
                $mailer,
                $assignment->emailTemplate->title,
                $assignment->emailTemplate->email_content,
                $variables,
                (string) $employee->email,
            );
        }

        $approverVariables = [
            ...$applicationVariables,
            ...$approvalVariables,
            'employee_name' => $allEmployeeNames,
            'employee_code' => $allEmployeeCodes,
        ];

        foreach ($approverRecipients as $approver) {
            $this->sendTemplateEmail(
                $mailer,
                $assignment->emailTemplate->title,
                $assignment->emailTemplate->email_content,
                $approverVariables,
                (string) $approver->email,
            );
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @return Collection<int, User>
     */
    private function recipientsWithEmail(Collection $users): Collection
    {
        return $users
            ->filter(static fn (User $user): bool => trim((string) $user->email) !== '')
            ->unique(static fn (User $user): string => strtolower(trim((string) $user->email)))
            ->values();
    }

    /**
     * @param  array<string, string|null>  $settings
     * @return array<string, mixed>
     */
    private function mailerConfig(array $settings): array
    {
        $fromAddress = trim((string) ($settings['from_address'] ?? ''));
        $fromName = trim((string) ($settings['from_name'] ?? ''));

        if ($fromAddress === '' || $fromName === '') {
            throw new RuntimeException('Tenant email settings are missing a from address or name.');
        }

        $config = [
            'transport' => ($settings['transport'] ?? 'mail') === 'smtp' ? 'smtp' : 'mail',
            'from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
        ];

        if ($config['transport'] === 'smtp') {
            if (empty($settings['host']) || empty($settings['port'])) {
                throw new RuntimeException('Tenant SMTP settings are missing a host or port.');
            }

            $host = (string) $settings['host'];
            $password = (string) ($settings['password'] ?? '');
            $config += [
                'host' => $host,
                'port' => (int) $settings['port'],
                'username' => $settings['username'] ?? null,
                'password' => strtolower(trim($host)) === 'smtp.gmail.com'
                    ? (preg_replace('/\s+/', '', $password) ?? $password)
                    : $password,
                'scheme' => ($settings['encryption'] ?? null) === 'ssl' ? 'smtps' : 'smtp',
            ];
        }

        return $config;
    }

    /**
     * @param  Collection<int, \App\Models\Tenant\ApprovalStatus>  $approvalStatuses
     * @param  Collection<int, User>  $approvers
     * @return array<string, string>
     */
    private function approvalVariables(
        Collection $approvalStatuses,
        Collection $approvers,
    ): array {
        $firstLevelApprovers = $approvalStatuses->flatMap(static function ($status) {
            $details = $status->approval_status_details;
            $firstLevel = $details->min('level');

            return $details
                ->where('level', $firstLevel)
                ->flatMap(static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection());
        })->unique('id');

        $totalApprovalLevels = $approvalStatuses
            ->map(static fn ($status): int => (int) $status->approval_status_details->max('level'))
            ->max() ?? 0;

        return [
            'application_status' => 'pending',
            'total_approval_levels' => (string) $totalApprovalLevels,
            'total_approvers' => (string) $approvers->unique('id')->count(),
            'current_approval_level' => $firstLevelApprovers->isEmpty() ? '' : '1',
            'current_level_approver_names' => $firstLevelApprovers->pluck('name')->filter()->unique()->implode(', '),
            'previous_level_approval_history' => 'No previous approvals',
            'approval_history' => 'No approvals yet',
        ];
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function sendTemplateEmail(
        Mailer $mailer,
        ?string $subject,
        ?string $content,
        array $variables,
        string $recipient,
    ): void {
        $render = static function (?string $value) use ($variables): string {
            return preg_replace_callback(
                '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/',
                static fn (array $matches): string => e($variables[$matches[1]] ?? ''),
                $value ?? '',
            ) ?? '';
        };

        $mailer->html(nl2br($render($content)), static function ($message) use ($recipient, $render, $subject): void {
            $message->to($recipient)->subject($render($subject));
        });
    }
}
