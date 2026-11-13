<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\ApprovalStatusDetails;
use App\Models\Tenant\EmailTemplateAssignment;
use App\Models\Tenant\User;
use App\Services\Tenant\ApplicationEmailData;
use App\Services\Tenant\ManualPunchApplicationEmailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use RuntimeException;

class SendApplicationStatusEmails implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $applicationClass,
        public int $applicationId,
        public string $action,
        public ?int $approvalStatusDetailId = null,
        public ?int $actorId = null,
    ) {}

    public function handle(): void
    {
        if (! in_array($this->action, ['initial', 'approve', 'reject'], true)) {
            throw new RuntimeException('The application email action is invalid.');
        }

        $applicationType = ApplicationEmailData::applicationType($this->applicationClass);
        if ($applicationType === null) {
            throw new RuntimeException('The application type is not supported for email.');
        }

        $assignment = EmailTemplateAssignment::query()
            ->with('emailTemplate')
            ->where('application_type', $applicationType)
            ->where('application_action', $this->action)
            ->where('status', 'active')
            ->first();

        if ($assignment === null
            || $assignment->emailTemplate === null
            || $assignment->emailTemplate->status !== 'active') {
            return;
        }

        $applicationClass = ApplicationEmailData::applicationClass($applicationType);
        $application = $applicationClass::query()->findOrFail($this->applicationId);
        $statuses = $application->approval_statuses()
            ->with([
                'approval_status_details.user',
                'approval_status_details.approval_flow_detail.users',
            ])
            ->get();

        $currentDetail = null;
        if ($this->action !== 'initial') {
            $currentDetail = $statuses
                ->flatMap(static fn (ApprovalStatus $status) => $status->approval_status_details)
                ->firstWhere('id', $this->approvalStatusDetailId);

            if ($currentDetail === null
                || $currentDetail->approval_status !== ($this->action === 'approve' ? 'approved' : 'rejected')
                || (int) $currentDetail->user_id !== $this->actorId) {
                throw new RuntimeException('The application approval decision could not be loaded.');
            }
        }

        $employees = $this->employeeUsers($application);
        $approvers = $statuses->flatMap(
            static fn (ApprovalStatus $status) => $status->approval_status_details->flatMap(
                static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection(),
            ),
        )->unique('id')->values();

        if ($this->action === 'initial') {
            $employeeRecipients = ManualPunchApplicationEmailSender::recipientsWithEmail($employees);
            $approverRecipients = $assignment->send_to_approver
                ? ManualPunchApplicationEmailSender::recipientsWithEmail($approvers)
                : collect();
        } else {
            $employeeRecipients = $assignment->send_to_employee
                ? ManualPunchApplicationEmailSender::recipientsWithEmail($employees)
                : collect();
            $otherApprovers = $assignment->send_to_another_approver
                ? $statuses->flatMap(
                    static fn (ApprovalStatus $status) => $status->approval_status_details
                        ->where('id', '!=', $currentDetail->id)
                        ->flatMap(static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection()),
                )
                    ->reject(fn (User $user): bool => (int) $user->id === $this->actorId)
                    ->unique('id')
                    ->values()
                : collect();
            $approverRecipients = ManualPunchApplicationEmailSender::recipientsWithEmail($otherApprovers);
        }

        if ($employeeRecipients->isEmpty() && $approverRecipients->isEmpty()) {
            return;
        }

        $variables = [
            ...ApplicationEmailData::applicationVariables($application, $applicationType),
            ...$this->approvalVariables($application, $statuses, $approvers, $currentDetail),
        ];
        $mailer = ManualPunchApplicationEmailSender::mailer();
        $allEmployeeNames = $employees->pluck('name')->filter()->implode(', ');
        $allEmployeeCodes = $employees->pluck('code')->filter()->implode(', ');

        foreach ($employeeRecipients as $employee) {
            ManualPunchApplicationEmailSender::sendTemplateEmail(
                $mailer,
                $assignment->emailTemplate->title,
                $assignment->emailTemplate->email_content,
                [
                    ...$variables,
                    'employee_name' => $employee->name ?? '',
                    'employee_code' => $employee->code ?? '',
                ],
                (string) $employee->email,
            );
        }

        $approverVariables = [
            ...$variables,
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
     * @return Collection<int, User>
     */
    private function employeeUsers(Model $application): Collection
    {
        if (method_exists($application, 'Users')) {
            return $application->Users()->get();
        }

        if (method_exists($application, 'users')) {
            return $application->users()->get();
        }

        if (method_exists($application, 'user')) {
            $user = $application->user()->first();

            return $user === null ? collect() : collect([$user]);
        }

        return collect();
    }

    /**
     * @param  Collection<int, ApprovalStatus>  $statuses
     * @param  Collection<int, User>  $approvers
     * @return array<string, string>
     */
    private function approvalVariables(
        Model $application,
        Collection $statuses,
        Collection $approvers,
        ?ApprovalStatusDetails $currentDetail,
    ): array {
        if ($this->action === 'initial') {
            $firstLevelApprovers = $statuses->flatMap(static function (ApprovalStatus $status): Collection {
                $details = $status->approval_status_details;
                $firstLevel = $details->min('level');

                return $details
                    ->where('level', $firstLevel)
                    ->flatMap(static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection());
            })->unique('id');

            return [
                'application_status' => 'pending',
                'total_approval_levels' => (string) ($statuses
                    ->map(static fn (ApprovalStatus $status): int => (int) $status->approval_status_details->max('level'))
                    ->max() ?? 0),
                'total_approvers' => (string) $approvers->count(),
                'current_approval_level' => $firstLevelApprovers->isEmpty() ? '' : '1',
                'current_level_approver_names' => $firstLevelApprovers->pluck('name')->filter()->unique()->implode(', '),
                'previous_level_approval_history' => 'No previous approvals',
                'approval_history' => 'No approvals yet',
            ];
        }

        if ($currentDetail === null) {
            throw new RuntimeException('The application approval decision could not be loaded.');
        }

        $currentLevelApprovers = $currentDetail->approval_flow_detail?->users ?? new Collection();
        $previousDetails = $statuses
            ->firstWhere('id', $currentDetail->approval_status_id)
            ?->approval_status_details
            ->where('level', '<', $currentDetail->level)
            ?? new Collection();
        $history = $statuses
            ->flatMap(static fn (ApprovalStatus $status) => $status->approval_status_details)
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
            'application_status' => method_exists($application, 'overallApprovalStatus')
                ? $application->overallApprovalStatus()
                : $this->action,
            'total_approval_levels' => (string) ($statuses
                ->map(static fn (ApprovalStatus $status): int => (int) $status->approval_status_details->max('level'))
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
