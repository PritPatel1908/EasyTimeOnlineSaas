<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Support\TenantPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TenantPermissions::allows($this->user('tenant'), 'LeaveGroup', 'write');
    }

    public function rules(): array
    {
        $leaveGroupId = (int) $this->route('leave_group');
        $monthlyMonthFields = [
            'jan',
            'feb',
            'mar',
            'apr',
            'may',
            'jun',
            'jul',
            'aug',
            'sep',
            'oct',
            'nov',
            'dec',
        ];
        $monthlyRuleFields = [
            'leave_type_id',
            'allow_c_f',
            'maximum_request_in_a_month',
            'maximum_request_in_a_year',
            'eligible_after_days',
            'count_weekly_off_as_present',
            'count_holiday_as_present',
            'credit_after_days',
            'max_accumulation',
            'min_avail',
            'max_avail',
            'leave_types_not_to_be_clubbed',
            'leave_credit_type',
            'is_paid',
            'keeps_balance',
            'allow_negative_balance',
            'negative_balance',
            'allow_backdate_leave',
            'backdate_day_limit',
            'allow_advance_leave',
            'advance_day_limit',
            'allow_leave_encashment',
            'allow_leave_lapse',
            'annual_quota',
            'present_count_per',
            'if_total_present_count',
            'then_credit_balance_days',
            'include_leaves',
        ];
        $monthlyRuleFields = array_merge($monthlyRuleFields, $monthlyMonthFields);
        $yearlyRuleFields = [
            'leave_type_id',
            'eligible',
            'allow_c_f',
            'maximum_request_in_a_month',
            'maximum_request_in_a_year',
            'eligible_after_days',
            'credit_after_days',
            'opening',
            'based_on',
            'allow_sandwich',
            'sandwich_leave_id',
            'min_utilization',
            'max_accumulation',
            'min_avail',
            'max_avail',
            'leave_types_not_to_be_clubbed',
            'leave_credit_type',
            'is_paid',
            'keeps_balance',
            'allow_negative_balance',
            'negative_balance',
            'allow_leave_encashment',
            'allow_leave_lapse',
        ];

        $rules = array_merge(LeaveGroupRequestRules::rules(
            $leaveGroupId,
            $this->integer('financial_year_id') ?: null,
        ), [
            'monthly_rules' => ['sometimes', 'array'],
            'monthly_rules.*' => ['required', 'array:'.implode(',', array_merge(['id'], $monthlyRuleFields))],
            'monthly_rules.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('grade_wise_monthly_leave_details', 'id')
                    ->where(fn ($query) => $query->where('grade_wise_leave_id', $leaveGroupId)),
            ],
            'monthly_rules.*.leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'monthly_rules.*.allow_c_f' => ['present', 'nullable', 'boolean'],
            'monthly_rules.*.maximum_request_in_a_month' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.maximum_request_in_a_year' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.eligible_after_days' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.count_weekly_off_as_present' => ['required', 'boolean'],
            'monthly_rules.*.count_holiday_as_present' => ['required', 'boolean'],
            'monthly_rules.*.credit_after_days' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.max_accumulation' => ['present', 'nullable', 'integer', 'min:0', 'max:127'],
            'monthly_rules.*.min_avail' => ['present', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'monthly_rules.*.max_avail' => ['present', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'monthly_rules.*.leave_types_not_to_be_clubbed' => ['sometimes', 'array'],
            'monthly_rules.*.leave_types_not_to_be_clubbed.*' => ['integer', 'distinct', 'exists:leave_types,id'],
            'monthly_rules.*.leave_credit_type' => ['required', Rule::in(['manual', 'monthly', 'yearly', 'based_on_present', 'based_on_working_hours'])],
            'monthly_rules.*.is_paid' => ['required', 'boolean'],
            'monthly_rules.*.keeps_balance' => ['required', 'boolean'],
            'monthly_rules.*.allow_negative_balance' => ['required', 'boolean'],
            'monthly_rules.*.negative_balance' => ['required', 'numeric', 'decimal:0,2', 'between:-99999999.99,99999999.99'],
            'monthly_rules.*.allow_backdate_leave' => ['required', 'boolean'],
            'monthly_rules.*.backdate_day_limit' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.allow_advance_leave' => ['required', 'boolean'],
            'monthly_rules.*.advance_day_limit' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.allow_leave_encashment' => ['required', 'boolean'],
            'monthly_rules.*.allow_leave_lapse' => ['required', 'boolean'],
            'monthly_rules.*.present_count_per' => ['required', Rule::in(['monthly', 'yearly'])],
            'monthly_rules.*.if_total_present_count' => ['present', 'nullable', 'integer', 'min:0'],
            'monthly_rules.*.then_credit_balance_days' => ['present', 'nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'monthly_rules.*.include_leaves' => ['sometimes', 'array'],
            'monthly_rules.*.include_leaves.*' => ['integer', 'distinct', 'exists:leave_types,id'],
            'yearly_rules' => ['sometimes', 'array'],
            'yearly_rules.*' => ['required', 'array:'.implode(',', array_merge(['id'], $yearlyRuleFields))],
            'yearly_rules.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('grade_wise_yearly_leave_details', 'id')
                    ->where(fn ($query) => $query->where('grade_wise_leave_id', $leaveGroupId)),
            ],
            'yearly_rules.*.leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'yearly_rules.*.eligible' => ['present', 'nullable', 'integer', 'min:0'],
            'yearly_rules.*.allow_c_f' => ['present', 'nullable', 'boolean'],
            'yearly_rules.*.maximum_request_in_a_month' => ['present', 'nullable', 'integer', 'min:0'],
            'yearly_rules.*.maximum_request_in_a_year' => ['present', 'nullable', 'integer', 'min:0'],
            'yearly_rules.*.eligible_after_days' => ['present', 'nullable', 'integer', 'min:0'],
            'yearly_rules.*.credit_after_days' => ['present', 'nullable', 'integer', 'min:0'],
            'yearly_rules.*.opening' => ['present', 'nullable', 'integer', 'min:0', 'max:127'],
            'yearly_rules.*.based_on' => ['present', 'nullable', 'integer', 'min:0', 'max:127'],
            'yearly_rules.*.allow_sandwich' => ['required', 'boolean'],
            'yearly_rules.*.sandwich_leave_id' => ['present', 'nullable', 'integer', 'exists:leave_types,id'],
            'yearly_rules.*.min_utilization' => ['present', 'nullable', 'integer', 'min:0'],
            'yearly_rules.*.max_accumulation' => ['present', 'nullable', 'integer', 'min:0', 'max:127'],
            'yearly_rules.*.min_avail' => ['present', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'yearly_rules.*.max_avail' => ['present', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'yearly_rules.*.leave_types_not_to_be_clubbed' => ['sometimes', 'array'],
            'yearly_rules.*.leave_types_not_to_be_clubbed.*' => ['integer', 'distinct', 'exists:leave_types,id'],
            'yearly_rules.*.leave_credit_type' => ['required', Rule::in(['manual', 'monthly', 'yearly', 'based_on_present', 'based_on_working_hours'])],
            'yearly_rules.*.is_paid' => ['required', 'boolean'],
            'yearly_rules.*.keeps_balance' => ['required', 'boolean'],
            'yearly_rules.*.allow_negative_balance' => ['required', 'boolean'],
            'yearly_rules.*.negative_balance' => ['required', 'numeric', 'decimal:0,2', 'between:-99999999.99,99999999.99'],
            'yearly_rules.*.allow_leave_encashment' => ['required', 'boolean'],
            'yearly_rules.*.allow_leave_lapse' => ['required', 'boolean'],
        ]);

        $rules['monthly_rules.*.annual_quota'] = ['present', 'nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'];

        foreach ($monthlyMonthFields as $month) {
            $rules['monthly_rules.*.'.$month] = ['present', 'nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'];
        }

        return $rules;
    }
}
