<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public const TEMPLATE_TYPES = [
        'manual_punch_application_approve_reject',
        'overtime_application_approve_reject',
        'manual_attendance_application_approve_reject',
        'shift_change_application_approve_reject',
        'leave_application_approve_reject',
        'short_leave_application_approve_reject',
        'coff_application_approve_reject',
        'week_off_change_application_approve_reject',
        'week_off_swap_application_approve_reject',
        'birthday_notification',
        'joining_notification',
        'onboarding_notification',
    ];

    public const APPLICATION_VARIABLES = [
        'manual_punch_application' => [
            ['key' => 'punch_type', 'label' => 'Punch type'],
            ['key' => 'punch_date', 'label' => 'Punch date'],
            ['key' => 'punch_time', 'label' => 'Punch time'],
            ['key' => 'reason', 'label' => 'Reason'],
            ['key' => 'submitted_on', 'label' => 'Submitted on'],
        ],
        'overtime_application' => [
            ['key' => 'date', 'label' => 'Overtime date'],
            ['key' => 'overtime_minutes', 'label' => 'Overtime minutes'],
            ['key' => 'reason', 'label' => 'Reason'],
        ],
        'manual_attendance_application' => [
            ['key' => 'in_time', 'label' => 'In time'],
            ['key' => 'out_time', 'label' => 'Out time'],
            ['key' => 'reason', 'label' => 'Reason'],
        ],
        'shift_change_application' => [
            ['key' => 'shift_type', 'label' => 'Shift type'],
            ['key' => 'shift_names', 'label' => 'Selected shifts'],
            ['key' => 'shift_rotation_name', 'label' => 'Shift rotation'],
            ['key' => 'from_date', 'label' => 'From date'],
            ['key' => 'is_forever', 'label' => 'Is permanent'],
            ['key' => 'to_date', 'label' => 'To date'],
            ['key' => 'reason', 'label' => 'Reason'],
        ],
        'leave_application' => [
            ['key' => 'leave_type_code', 'label' => 'Leave type code'],
            ['key' => 'leave_type', 'label' => 'Leave type'],
            ['key' => 'leave_reason_code', 'label' => 'Leave reason code'],
            ['key' => 'leave_reason', 'label' => 'Leave reason'],
            ['key' => 'reason_explanation', 'label' => 'Reason explanation'],
            ['key' => 'application_date', 'label' => 'Application date'],
            ['key' => 'from_date', 'label' => 'From date'],
            ['key' => 'to_date', 'label' => 'To date'],
            ['key' => 'is_only_half', 'label' => 'Half-day application'],
            ['key' => 'is_first_half', 'label' => 'First half'],
            ['key' => 'is_second_half', 'label' => 'Second half'],
            ['key' => 'leave_count', 'label' => 'Leave count'],
        ],
        'short_leave_application' => [
            ['key' => 'date', 'label' => 'Short leave date'],
            ['key' => 'from_time', 'label' => 'From time'],
            ['key' => 'to_time', 'label' => 'To time'],
            ['key' => 'minutes', 'label' => 'Duration in minutes'],
            ['key' => 'short_leave_type', 'label' => 'Short leave type'],
            ['key' => 'reason', 'label' => 'Reason'],
        ],
        'coff_application' => [
            ['key' => 'coff_against_date', 'label' => 'COFF against date'],
            ['key' => 'coff_from_date', 'label' => 'COFF from date'],
            ['key' => 'coff_to_date', 'label' => 'COFF to date'],
            ['key' => 'is_half_day', 'label' => 'Half-day application'],
            ['key' => 'is_first_half', 'label' => 'First half'],
            ['key' => 'is_second_half', 'label' => 'Second half'],
        ],
        'week_off_change_application' => [
            ['key' => 'from_date', 'label' => 'From date'],
            ['key' => 'is_forever', 'label' => 'Is permanent'],
            ['key' => 'to_date', 'label' => 'To date'],
            ['key' => 'reason', 'label' => 'Reason'],
            ['key' => 'week_days', 'label' => 'Weekly-off days'],
            ['key' => 'wo_types', 'label' => 'Weekly-off types'],
            ['key' => 'first_week', 'label' => 'First week'],
            ['key' => 'second_week', 'label' => 'Second week'],
            ['key' => 'third_week', 'label' => 'Third week'],
            ['key' => 'fourth_week', 'label' => 'Fourth week'],
            ['key' => 'fifth_week', 'label' => 'Fifth week'],
        ],
        'week_off_swap_application' => [
            ['key' => 'week_date', 'label' => 'Current weekly-off date'],
            ['key' => 'swap_date', 'label' => 'Requested weekly-off date'],
            ['key' => 'reason', 'label' => 'Reason'],
        ],
    ];

    public const COMMON_VARIABLES = [
        ['key' => 'employee_name', 'label' => 'Employee name'],
        ['key' => 'employee_code', 'label' => 'Employee code'],
    ];

    public const APPROVAL_VARIABLES = [
        ['key' => 'application_status', 'label' => 'Application status'],
        ['key' => 'total_approval_levels', 'label' => 'Total approval levels'],
        ['key' => 'total_approvers', 'label' => 'Total approvers'],
        ['key' => 'current_approval_level', 'label' => 'Current approval level'],
        ['key' => 'current_level_approver_names', 'label' => 'Current level approver names'],
        ['key' => 'previous_level_approval_history', 'label' => 'Previous level decisions (employee, approved/rejected, date, rejection reason)'],
        ['key' => 'approval_history', 'label' => 'Full approval history (level, employee, decision, date, rejection reason)'],
        ['key' => 'cancellation_reason', 'label' => 'Cancellation reason'],
        ['key' => 'cancelled_by', 'label' => 'Cancelled by'],
        ['key' => 'cancelled_at', 'label' => 'Cancelled at'],
    ];

    protected $fillable = [
        'title',
        'template_type',
        'email_content',
        'status',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(EmailTemplateAssignment::class);
    }

    /**
     * @return array<string, list<array{key: string, label: string}>>
     */
    public static function variablesByTemplateType(): array
    {
        $variablesByType = [];

        foreach (self::APPLICATION_VARIABLES as $type => $variables) {
            $approvalType = $type.'_approve_reject';
            if (in_array($approvalType, self::TEMPLATE_TYPES, true)) {
                $variablesByType[$approvalType] = [
                    ...self::COMMON_VARIABLES,
                    ...$variables,
                    ...self::APPROVAL_VARIABLES,
                ];
            }
        }

        return $variablesByType;
    }
}
