<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;

final class LeaveGroupRequestRules
{
    public static function rules(?int $leaveGroupId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grade_wise_leaves', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($leaveGroupId),
            ],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }
}
