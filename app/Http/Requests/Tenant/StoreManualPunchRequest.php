<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualPunchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'users' => ['nullable', 'array'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'employee_filters' => ['nullable', 'array:department_id,sub_department_id,category_id,sub_category_id,designation_id,grade_id,unit_id,bus_route_id'],
            'employee_filters.department_id' => ['nullable', 'array'],
            'employee_filters.department_id.*' => ['integer', 'distinct', 'exists:departments,id'],
            'employee_filters.sub_department_id' => ['nullable', 'array'],
            'employee_filters.sub_department_id.*' => ['integer', 'distinct', 'exists:sub_departments,id'],
            'employee_filters.category_id' => ['nullable', 'array'],
            'employee_filters.category_id.*' => ['integer', 'distinct', 'exists:categories,id'],
            'employee_filters.sub_category_id' => ['nullable', 'array'],
            'employee_filters.sub_category_id.*' => ['integer', 'distinct', 'exists:sub_categories,id'],
            'employee_filters.designation_id' => ['nullable', 'array'],
            'employee_filters.designation_id.*' => ['integer', 'distinct', 'exists:designations,id'],
            'employee_filters.grade_id' => ['nullable', 'array'],
            'employee_filters.grade_id.*' => ['integer', 'distinct', 'exists:grades,id'],
            'employee_filters.unit_id' => ['nullable', 'array'],
            'employee_filters.unit_id.*' => ['integer', 'distinct', 'exists:units,id'],
            'employee_filters.bus_route_id' => ['nullable', 'array'],
            'employee_filters.bus_route_id.*' => ['integer', 'distinct', 'exists:bus_routes,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'distinct', 'exists:locations,id'],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', 'distinct', 'exists:companies,id'],
            'punch_type' => ['required', 'string', 'in:1,2,3,4'],
            'punch_date' => ['required', 'date'],
            'punch_time' => ['required', 'date_format:H:i,H:i:s'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
