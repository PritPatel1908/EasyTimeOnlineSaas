<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

final class DesignationRequestRules
{
    public static function rules(?string $designationId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:designations,code' . ($designationId ? ',' . $designationId : '')],
            'status' => ['required', 'integer', 'in:1,0'],
            'company_id' => ['nullable', 'array'],
            'company_id.*' => ['integer', 'exists:companies,id'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
            'category_id' => ['nullable', 'array'],
            'category_id.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
