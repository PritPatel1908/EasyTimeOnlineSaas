<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['location_id', 'company_id', 'department_id', 'sub_department_id', 'category_id', 'sub_category_id', 'designation_id', 'grade_id', 'unit_id', 'bus_route_id'] as $field) {
            $this->merge([$field => array_values(array_filter((array) $this->input($field, []), static fn($id): bool => $id !== ''))]);
        }
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'unique:users,code'],
            'fname' => ['required', 'string', 'max:100'],
            'mname' => ['nullable', 'string', 'max:100'],
            'lname' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'number' => ['nullable', 'string', 'max:30'],
            'card' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date'],
            'blood_group_id' => ['nullable', 'integer', 'exists:blood_groups,id'],
            'join_date' => ['nullable', 'date'],
            'status' => ['required', 'boolean'],
            'user_type' => ['required', Rule::in(['employee', 'guest'])],
            'password' => ['exclude_unless:is_locked,1', 'nullable', 'string', 'min:8'],
            'is_locked' => ['nullable', 'boolean'],
            'profile_pic' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'left_date' => ['nullable', 'date'],
            'left_reason' => ['nullable', 'string', 'max:255'],
            'shift_type' => ['nullable', Rule::in(['auto', 'fixed', 'rotational'])],
            'shift_ids' => ['exclude_unless:shift_type,auto,fixed', 'required', 'array', 'min:1'],
            'shift_ids.*' => ['integer', 'distinct', 'exists:shifts,id'],
            'password_policy_id' => ['exclude_unless:is_locked,1', 'nullable', 'integer', 'exists:password_policies,id'],
            'is_inactive' => ['nullable', 'boolean'],
            'approval_flow_id' => ['nullable', 'integer'],
            'login_attempts' => ['nullable', 'integer', 'min:0'],
            'password_changed_at' => ['nullable', 'date'],
            'last_active_at' => ['nullable', 'date'],
            'last_login_at' => ['nullable', 'date'],
            'allow_mobile_login' => ['nullable', 'boolean'],
            'allow_mobile_punch' => ['nullable', 'boolean'],
            'dms_user_id' => ['nullable', 'string', 'max:255'],
            'rejoin_date' => ['nullable', 'date'],
            'rejoin_reason' => ['nullable', 'string', 'max:255'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'inactive_date' => ['nullable', 'date'],
            'inactive_days' => ['nullable', 'integer', 'min:0'],
            'last_active_at' => ['nullable', 'date'],
            'last_login_at' => ['nullable', 'date'],
            'shift_status' => ['nullable', 'boolean'],
            'late_coming_rule_id' => ['nullable', 'integer', 'exists:late_coming_rules,id'],
            'early_going_rule_id' => ['nullable', 'integer', 'exists:early_going_rules,id'],
            'half_day_rule_id' => ['nullable', 'integer', 'exists:half_day_rules,id'],
            'absent_rule_id' => ['nullable', 'integer', 'exists:absent_rules,id'],
            'overtime_rule_id' => ['nullable', 'integer', 'exists:overtime_rules,id'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'languages' => ['nullable', 'string', 'max:2000'],
            'hobbies' => ['nullable', 'string', 'max:2000'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_number' => ['nullable', 'string', 'max:255'],
            'emergency_address' => ['nullable', 'string', 'max:255'],
            'flat_building' => ['nullable', 'string', 'max:255'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'flore' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:255'],
            'is_current_address' => ['nullable', 'boolean'],
            'shift_rotation_id' => ['exclude_unless:shift_type,rotational', 'required', 'integer', 'exists:shift_rotations,id'],
            'shift_change_id' => ['nullable', 'integer'],
            'week_off_change_id' => ['nullable', 'integer'],
            'shift_change_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'week_off_change_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'week_off_swap_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'manual_punch_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'manual_attendance_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'leave_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'short_leave_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'grade_wise_leave_id' => ['nullable', 'integer'],
            'last_check_date' => ['nullable', 'date'],
            'last_check_id' => ['nullable', 'integer'],
            'short_leave_minutes' => ['nullable', 'integer', 'min:0'],
            'coff_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'od_approval_flow_id' => ['nullable', 'integer', 'exists:approval_flows,id'],
            'aadhar_number' => ['nullable', 'string', 'max:255'],
            'uan_number' => ['nullable', 'string', 'max:255'],
            'esic_number' => ['nullable', 'string', 'max:255'],
            'pan_number' => ['nullable', 'string', 'max:255'],
            'data_policy_id' => ['exclude_unless:is_locked,1', 'nullable', 'exists:data_policies,id'],
            'role_id' => ['exclude_unless:is_locked,1', 'nullable', 'exists:roles,id'],
            'leave_group_id' => ['nullable', 'integer', 'exists:leave_groups,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'canteen_facility_id' => ['nullable', 'integer', 'exists:canteen_facilities,id'],
            '*.id' => ['nullable'],
        ] + $this->relationRules();
    }

    private function relationRules(): array
    {
        return collect(['location_id', 'company_id', 'department_id', 'sub_department_id', 'category_id', 'sub_category_id', 'designation_id', 'grade_id', 'unit_id', 'bus_route_id'])
            ->mapWithKeys(fn(string $field): array => [
                $field => ['array'],
                $field . '.*' => ['integer'],
            ])
            ->all();
    }
}
