<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return LeaveGroupRequestRules::rules(
            (int) $this->route('leave_group'),
            $this->integer('financial_year_id') ?: null,
        );
    }
}
