<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Helpers\ApprovalProcessor;
use App\Models\Tenant\EmailTemplateAssignment;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\ManualPunchApplicationEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreateOvertimeApplicationApprovals implements ShouldQueue
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
        $application = OvertimeApplication::query()
            ->with('user')
            ->findOrFail($this->applicationId);

        ApprovalProcessor::CreateOvertimeApplicationApprovals(
            $application,
            $this->approvalFlowIds,
            $this->actorId,
        );

        $this->sendInitialEmails($application);
    }

    private function sendInitialEmails(OvertimeApplication $application): void
    {
        $assignment = EmailTemplateAssignment::query()
            ->with('emailTemplate')
            ->where('application_type', 'overtime_application')
            ->where('application_action', 'initial')
            ->where('status', 'active')
            ->first();

        if ($assignment === null
            || $assignment->emailTemplate === null
            || $assignment->emailTemplate->status !== 'active') {
            return;
        }

        $approvalStatuses = $application->approval_statuses()
            ->with('approval_status_details.approval_flow_detail.users')
            ->get();
        $approvers = $approvalStatuses->flatMap(
            static fn ($status) => $status->approval_status_details->flatMap(
                static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection(),
            ),
        )->unique('id')->values();
        $employeeRecipients = $application->user === null
            ? collect()
            : ManualPunchApplicationEmailSender::recipientsWithEmail(collect([$application->user]));
        $approverRecipients = $assignment->send_to_approver
            ? ManualPunchApplicationEmailSender::recipientsWithEmail($approvers)
            : collect();

        if ($employeeRecipients->isEmpty() && $approverRecipients->isEmpty()) {
            return;
        }

        $mailer = ManualPunchApplicationEmailSender::mailer();
        $applicationVariables = ManualPunchApplicationEmailSender::overtimeApplicationVariables($application);
        $approvalVariables = $this->approvalVariables($approvalStatuses, $approvers);
        $employee = $application->user;

        foreach ($employeeRecipients as $recipient) {
            ManualPunchApplicationEmailSender::sendTemplateEmail(
                $mailer,
                $assignment->emailTemplate->title,
                $assignment->emailTemplate->email_content,
                [
                    ...$applicationVariables,
                    ...$approvalVariables,
                    'employee_name' => $recipient->name ?? '',
                    'employee_code' => $recipient->code ?? '',
                ],
                (string) $recipient->email,
            );
        }

        $approverVariables = [
            ...$applicationVariables,
            ...$approvalVariables,
            'employee_name' => $employee?->name ?? '',
            'employee_code' => $employee?->code ?? '',
        ];
        foreach ($approverRecipients as $approver) {
            ManualPunchApplicationEmailSender::sendTemplateEmail(
                $mailer,
                $assignment->emailTemplate->title,
                $assignment->emailTemplate->email_content,
                $approverVariables,
                (string) $approver->email,
            );
        }
    }

    /**
     * @param  Collection<int, \App\Models\Tenant\ApprovalStatus>  $approvalStatuses
     * @param  Collection<int, User>  $approvers
     * @return array<string, string>
     */
    private function approvalVariables(Collection $approvalStatuses, Collection $approvers): array
    {
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
            'total_approvers' => (string) $approvers->count(),
            'current_approval_level' => $firstLevelApprovers->isEmpty() ? '' : '1',
            'current_level_approver_names' => $firstLevelApprovers->pluck('name')->filter()->unique()->implode(', '),
            'previous_level_approval_history' => 'No previous approvals',
            'approval_history' => 'No approvals yet',
        ];
    }
}
