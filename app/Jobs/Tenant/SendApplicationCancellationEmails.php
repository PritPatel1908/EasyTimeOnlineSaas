<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalStatus;
use App\Models\Tenant\ApprovedApplicationEffectSnapshot;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\EmailTemplate;
use App\Models\Tenant\EmailTemplateAssignment;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Services\Tenant\ManualPunchApplicationEmailSender;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use RuntimeException;

class SendApplicationCancellationEmails implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $applicationClass,
        public int $applicationId,
        public int $actorId,
    ) {}

    public function handle(): void
    {
        $applicationType = $this->applicationType();
        if ($applicationType === null) {
            return;
        }

        $assignment = EmailTemplateAssignment::query()
            ->with('emailTemplate')
            ->where('application_type', $applicationType)
            ->where('application_action', 'cancel')
            ->where('status', 'active')
            ->first();

        if ($assignment === null
            || $assignment->emailTemplate === null
            || $assignment->emailTemplate->status !== 'active') {
            return;
        }

        $application = $this->application();
        $snapshot = ApprovedApplicationEffectSnapshot::query()
            ->where('approvable_type', $this->applicationClass)
            ->where('approvable_id', $this->applicationId)
            ->where('cancellation_status', 'completed')
            ->firstOrFail();
        $statuses = $application->approval_statuses()
            ->with('approval_status_details.approval_flow_detail.users')
            ->get();
        $employeeUsers = $this->employeeUsers($application);
        $employeeRecipients = $assignment->send_to_employee
            ? ManualPunchApplicationEmailSender::recipientsWithEmail($employeeUsers)
            : collect();
        $allApprovers = $statuses->flatMap(
            static fn (ApprovalStatus $status) => $status->approval_status_details->flatMap(
                static fn ($detail) => $detail->approval_flow_detail?->users ?? new Collection(),
            ),
        )->unique('id')->values();
        $otherApprovers = $assignment->send_to_another_approver
            ? $allApprovers
                ->reject(fn (User $user): bool => (int) $user->id === $this->actorId)
                ->values()
            : collect();
        $approverRecipients = ManualPunchApplicationEmailSender::recipientsWithEmail($otherApprovers);

        if ($employeeRecipients->isEmpty() && $approverRecipients->isEmpty()) {
            return;
        }

        $cancelledBy = User::query()->find($this->actorId);
        $cancelledAt = $snapshot->cancelled_at;
        $cancellationReason = (string) ($snapshot->cancellation_reason ?? '');
        $applicationVariables = $this->applicationVariables($application, $applicationType);
        $approvalVariables = [
            'application_status' => 'cancelled',
            'total_approval_levels' => (string) ($statuses
                ->map(static fn (ApprovalStatus $status): int => (int) $status->approval_status_details->max('level'))
                ->max() ?? 0),
            'total_approvers' => (string) $allApprovers->count(),
            'current_approval_level' => '',
            'current_level_approver_names' => '',
            'previous_level_approval_history' => 'Not applicable to request cancellation',
            'approval_history' => 'Request cancelled',
            'cancellation_reason' => $cancellationReason,
            'cancelled_by' => $cancelledBy?->name ?? '',
            'cancelled_at' => $cancelledAt instanceof CarbonInterface
                ? $cancelledAt->format('Y-m-d H:i:s')
                : '',
        ];
        $mailer = ManualPunchApplicationEmailSender::mailer();
        $template = $assignment->emailTemplate;

        foreach ($employeeRecipients as $employee) {
            ManualPunchApplicationEmailSender::sendTemplateEmail(
                $mailer,
                $template->title,
                $template->email_content,
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
            'employee_name' => $employeeUsers->pluck('name')->filter()->implode(', '),
            'employee_code' => $employeeUsers->pluck('code')->filter()->implode(', '),
        ];
        foreach ($approverRecipients as $approver) {
            ManualPunchApplicationEmailSender::sendTemplateEmail(
                $mailer,
                $template->title,
                $template->email_content,
                $approverVariables,
                (string) $approver->email,
            );
        }
    }

    private function applicationType(): ?string
    {
        return match ($this->applicationClass) {
            LeaveApplication::class => 'leave_application',
            ManualAttendanceApplication::class => 'manual_attendance_application',
            ManualPunchApplication::class => 'manual_punch_application',
            OvertimeApplication::class => 'overtime_application',
            CoffApplication::class => 'coff_application',
            ShortLeaveApplication::class => 'short_leave_application',
            ShiftChangeApplication::class => 'shift_change_application',
            WeekOffChangeApplication::class => 'week_off_change_application',
            WeekOffSwapApplication::class => 'week_off_swap_application',
            default => null,
        };
    }

    private function application(): Model
    {
        return match ($this->applicationClass) {
            LeaveApplication::class => LeaveApplication::query()->findOrFail($this->applicationId),
            ManualAttendanceApplication::class => ManualAttendanceApplication::query()->findOrFail($this->applicationId),
            ManualPunchApplication::class => ManualPunchApplication::query()->findOrFail($this->applicationId),
            OvertimeApplication::class => OvertimeApplication::query()->findOrFail($this->applicationId),
            CoffApplication::class => CoffApplication::query()->findOrFail($this->applicationId),
            ShortLeaveApplication::class => ShortLeaveApplication::query()->findOrFail($this->applicationId),
            ShiftChangeApplication::class => ShiftChangeApplication::query()->findOrFail($this->applicationId),
            WeekOffChangeApplication::class => WeekOffChangeApplication::query()->findOrFail($this->applicationId),
            WeekOffSwapApplication::class => WeekOffSwapApplication::query()->findOrFail($this->applicationId),
            default => throw new RuntimeException('The cancelled application type is not supported for email.'),
        };
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
     * @return array<string, string>
     */
    private function applicationVariables(Model $application, string $applicationType): array
    {
        $variables = [];

        if ($application instanceof LeaveApplication) {
            $application->loadMissing(['leave_type', 'leave_reason']);
        }
        if ($application instanceof ShiftChangeApplication) {
            $application->loadMissing(['shifts', 'shift_rotation']);
        }

        foreach (EmailTemplate::APPLICATION_VARIABLES[$applicationType] as $variable) {
            $key = $variable['key'];
            $value = match ($key) {
                'leave_type_code' => $application instanceof LeaveApplication
                    ? $application->leave_type?->code
                    : null,
                'leave_type' => $application instanceof LeaveApplication
                    ? $application->leave_type?->description
                    : null,
                'leave_reason_code' => $application instanceof LeaveApplication
                    ? $application->leave_reason?->code
                    : null,
                'leave_reason' => $application instanceof LeaveApplication
                    ? $application->leave_reason?->leave_reason
                    : null,
                'reason_explanation' => $application instanceof LeaveApplication
                    ? $application->reason_explanation
                    : null,
                'punch_type' => $application instanceof ManualPunchApplication
                    ? ManualPunch::punchTypeLabel($application->punch_type)
                    : null,
                'shift_names' => $application instanceof ShiftChangeApplication
                    ? $application->shifts->pluck('name')->filter()->implode(', ')
                    : null,
                'shift_rotation_name' => $application instanceof ShiftChangeApplication
                    ? $application->shift_rotation?->code
                    : null,
                'short_leave_type' => $application instanceof ShortLeaveApplication
                    ? match ((int) $application->short_leave_type) {
                        1 => 'Late Coming',
                        2 => 'Early Going',
                        3 => 'Break Between Working Hours',
                        default => '',
                    }
                    : null,
                default => $application->getAttribute($key),
            };
            $variables[$key] = $this->formatValue($value, $key);
        }

        return $variables;
    }

    private function formatValue(mixed $value, string $key): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof CarbonInterface) {
            if (in_array($key, ['date', 'punch_date', 'application_date', 'from_date', 'to_date', 'week_date', 'swap_date'], true)) {
                return $value->format('Y-m-d');
            }

            return in_array($key, ['from_time', 'to_time', 'punch_time'], true)
                ? $value->format('H:i:s')
                : $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return implode(', ', array_map(static fn (mixed $item): string => (string) $item, $value));
        }

        return (string) $value;
    }
}
