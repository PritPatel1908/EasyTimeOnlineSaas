<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

final class CategoryRequestRules
{
    public static function rules(?string $categoryId = null): array
    {
        $fields = [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:categories,code' . ($categoryId ? ',' . $categoryId : '')],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'integer', 'in:1,0'],
            'company_id' => ['nullable', 'array'],
            'company_id.*' => ['integer', 'exists:companies,id'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
        ];
        foreach (['is_week_off_paid', 'is_holiday_paid', 'single_punch_allowed_present', 'single_punch_allowed_half_day', 'need_approval_for_overtime', 'regular_ot_on_wo', 'bypass_timing_rule', 'ignore_before_after_shift_punch', 'fix_work_hours', 'fix_work_hours_as_per_shift', 'ignore_break_in_attendance', 'reset_halfday_rule_cycle', 'give_double_ot_in_public_holiday', 'give_double_coff_in_public_holiday', 'is_eligible_for_c_off', 'allow_halfday_c_off', 'allow_backdated_leave'] as $field) {
            $fields[$field] = ['nullable', 'boolean'];
        }
        foreach (['max_short_leave_minutes_per_month', 'max_short_leave_minutes_per_application', 'max_occurance_of_short_leave_in_month', 'advance_short_leave_application', 'c_off_lapse_in_days', 'backdated_day_limit', 'advance_day_limit', 'maximum_accumulation', 'maximum_request_in_a_month', 'maximum_request_in_a_year'] as $field) {
            $fields[$field] = ['nullable', 'integer', 'min:0'];
        }
        $fields['canteen_break_limit'] = ['nullable', 'string', 'max:255'];
        $fields['fix_work_hours_value'] = ['nullable', 'string', 'max:255'];
        $fields['leave_type_id'] = ['nullable', 'array'];
        $fields['leave_type_id.*'] = ['integer', 'exists:leave_types,id'];
        $fields['c_off_against_wo_hl_slabs'] = ['nullable', 'array'];
        $fields['c_off_against_wo_hl_slabs.*.from_time'] = ['nullable', 'date_format:H:i'];
        $fields['c_off_against_wo_hl_slabs.*.to_time'] = ['nullable', 'date_format:H:i', 'after_or_equal:c_off_against_wo_hl_slabs.*.from_time'];
        $fields['c_off_against_wo_hl_slabs.*.credit_days'] = ['nullable', 'numeric', 'min:0'];
        $fields['c_off_against_ot_slabs'] = ['nullable', 'array'];
        $fields['c_off_against_ot_slabs.*.from_hours'] = ['nullable', 'date_format:H:i'];
        $fields['c_off_against_ot_slabs.*.to_hours'] = ['nullable', 'date_format:H:i', 'after_or_equal:c_off_against_ot_slabs.*.from_hours'];
        $fields['c_off_against_ot_slabs.*.credit_days'] = ['nullable', 'numeric', 'min:0'];
        $fields['min_avail'] = ['nullable', 'numeric', 'min:0'];
        $fields['max_avail'] = ['nullable', 'numeric', 'min:0', 'gte:min_avail'];
        $fields['skip_overtime'] = ['nullable', 'date_format:H:i'];
        return $fields;
    }
}
