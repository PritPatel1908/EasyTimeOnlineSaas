<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveGroupRequest extends FormRequest
{
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
