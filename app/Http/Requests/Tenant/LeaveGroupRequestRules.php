<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;

final class LeaveGroupRequestRules
{
    public static function rules(?int $leaveGroupId = null, ?int $financialYearId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grade_wise_leaves', 'name')
                    ->where(fn ($query) => $query->where('financial_year_id', $financialYearId)->whereNull('deleted_at'))
                    ->ignore($leaveGroupId),
            ],
            'financial_year_id' => ['nullable', 'integer', 'exists:financial_years,id'],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }
}
