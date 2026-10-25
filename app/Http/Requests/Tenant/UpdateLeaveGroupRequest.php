<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Support\TenantPermissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveGroupRequest extends FormRequest
{
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
