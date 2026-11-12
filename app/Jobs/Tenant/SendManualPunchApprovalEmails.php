<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\EmailTemplateAssignment;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\ManualPunchApplicationEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use RuntimeException;

class SendManualPunchApprovalEmails implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $applicationId,
        public int $approvalStatusDetailId,
        public int $actorId,
    ) {}

    public function handle(): void
    {
        $assignment = EmailTemplateAssignment::query()
            ->with('emailTemplate')
            ->where('application_type', 'manual_punch_application')
            ->where('application_action', 'approve')
            ->where('status', 'active')
            ->first();

        if ($assignment === null
            || $assignment->emailTemplate === null
            || $assignment->emailTemplate->status !== 'active') {
            return;
        }

        $application = ManualPunchApplication::query()
            ->with([
                'Users',
                'approval_statuses.approval_status_details.user',
                'approval_statuses.approval_status_details.approval_flow_detail.users',
            ])
            ->findOrFail($this->applicationId);
        $statuses = $application->approval_statuses;
        $currentDetail = $statuses
            ->flatMap(static fn ($status) => $status->approval_status_details)
            ->firstWhere('id', $this->approvalStatusDetailId);

        if ($currentDetail === null
            || $currentDetail->approval_status !== 'approved'
            || (int) $currentDetail->user_id !== $this->actorId) {
            throw new RuntimeException('The manual punch approval decision could not be loaded.');
        }

        $employees = $application->Users;
        $approvers = $statuses->flatMap(
            static fn ($status) => $status->approval_status_details->flatMap(
                static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection(),
            ),
        )->unique('id')->values();
        $employeeRecipients = $assignment->send_to_employee
            ? ManualPunchApplicationEmailSender::recipientsWithEmail($employees)
            : collect();
        $otherApprovers = $assignment->send_to_another_approver
            ? $statuses->flatMap(
                static fn ($status) => $status->approval_status_details
                    ->where('id', '!=', $currentDetail->id)
                    ->flatMap(static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection()),
            )
                ->reject(fn (User $user): bool => (int) $user->id === $this->actorId)
                ->unique('id')
                ->values()
            : collect();
        $approverRecipients = ManualPunchApplicationEmailSender::recipientsWithEmail($otherApprovers);

        if ($employeeRecipients->isEmpty() && $approverRecipients->isEmpty()) {
            return;
        }

        $mailer = ManualPunchApplicationEmailSender::mailer();

        $allEmployeeNames = $employees->pluck('name')->filter()->implode(', ');
        $allEmployeeCodes = $employees->pluck('code')->filter()->implode(', ');
        $approvalVariables = $this->approvalVariables($application, $currentDetail, $statuses, $approvers);
        $applicationVariables = ManualPunchApplicationEmailSender::applicationVariables($application);

        foreach ($employeeRecipients as $employee) {
            ManualPunchApplicationEmailSender::sendTemplateEmail(
                $mailer,
                $assignment->emailTemplate->title,
                $assignment->emailTemplate->email_content,
                [
                    ...$applicationVariables,
                    ...$approvalVariables,
                    'employee_name' => $employee->name ?? '',
                    'employee_code' => $employee->code ?? '',
                ],
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
     * @param  Collection<int, \App\Models\Tenant\ApprovalStatus>  $statuses
     * @param  Collection<int, User>  $approvers
     * @return array<string, string>
     */
    private function approvalVariables(
        ManualPunchApplication $application,
        ApprovalStatusDetails $currentDetail,
        Collection $statuses,
        Collection $approvers,
    ): array {
        $currentLevelApprovers = $currentDetail->approval_flow_detail?->users ?? new Collection();
        $previousDetails = $statuses
            ->firstWhere('id', $currentDetail->approval_status_id)
            ?->approval_status_details
            ->where('level', '<', $currentDetail->level)
            ?? new Collection();
        $history = $statuses->flatMap(
            static fn ($status) => $status->approval_status_details,
        )
            ->filter(static fn ($detail): bool => in_array($detail->approval_status, ['approved', 'rejected'], true))
            ->sortBy([['level', 'asc'], ['id', 'asc']])
            ->map(static function ($detail): string {
                $decisionBy = $detail->user?->name ?? 'Unknown approver';
                $decisionDate = $detail->timestamp?->format('Y-m-d H:i:s') ?? '';
                $reason = trim((string) $detail->rejection_reason);
                $entry = "Level {$detail->level}: {$decisionBy} {$detail->approval_status} on {$decisionDate}";

                return $reason === '' ? $entry : "{$entry} (Reason: {$reason})";
            })
            ->implode('; ');

        return [
            'application_status' => $application->overallApprovalStatus(),
            'total_approval_levels' => (string) ($statuses
                ->map(static fn ($status): int => (int) $status->approval_status_details->max('level'))
                ->max() ?? 0),
            'total_approvers' => (string) $approvers->count(),
            'current_approval_level' => (string) $currentDetail->level,
            'current_level_approver_names' => $currentLevelApprovers
                ->pluck('name')->filter()->unique()->implode(', '),
            'previous_level_approval_history' => $previousDetails->isEmpty()
                ? 'No previous approvals'
                : $this->approvalHistory($previousDetails),
            'approval_history' => $history === '' ? 'No approvals yet' : $history,
        ];
    }

    private function approvalHistory(Collection $details): string
    {
        return $details
            ->filter(static fn ($detail): bool => in_array($detail->approval_status, ['approved', 'rejected'], true))
            ->sortBy('level')
            ->map(static function ($detail): string {
                $decisionBy = $detail->user?->name ?? 'Unknown approver';
                $decisionDate = $detail->timestamp?->format('Y-m-d H:i:s') ?? '';

                return "Level {$detail->level}: {$decisionBy} {$detail->approval_status} on {$decisionDate}";
            })
            ->implode('; ') ?: 'No previous approvals';
    }

}
