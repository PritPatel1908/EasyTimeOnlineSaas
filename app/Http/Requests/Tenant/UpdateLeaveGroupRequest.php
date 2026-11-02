<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Support\TenantPermissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveGroupRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $monthlyRules = $this->input('monthly_rules');

        if (!is_array($monthlyRules)) {
            $this->merge(['monthly_rules' => []]);
            return;
        }

        $normalizedMonthlyRules = [];
        foreach ($monthlyRules as $index => $rule) {
            if (is_array($rule)) {
                $normalizedMonthlyRules[$index] = $rule;
            }
        }

        $this->merge(['monthly_rules' => $normalizedMonthlyRules]);
    }

    public function authorize(): bool
    {
        return TenantPermissions::allows($this->user('tenant'), 'LeaveGroup', 'write');
    }

    public function rules(): array
    {
        $leaveGroupId = (int) $this->route('leave_group');
        return array_merge(
            LeaveGroupRequestRules::rules(
                $leaveGroupId,
                $this->integer('financial_year_id') ?: null,
            ),
            LeaveGroupMonthlyRuleRules::rules($leaveGroupId),
            ['monthly_rules_present' => ['sometimes', 'accepted']],
        );
    }
}
