<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveGroupRequest extends FormRequest
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
            if (!is_array($rule)) {
                continue;
            }

            unset($rule['id']);
            $normalizedMonthlyRules[$index] = $rule;
        }

        $this->merge(['monthly_rules' => $normalizedMonthlyRules]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            LeaveGroupRequestRules::rules(null, $this->integer('financial_year_id') ?: null),
            LeaveGroupMonthlyRuleRules::rules(),
        );
    }
}
