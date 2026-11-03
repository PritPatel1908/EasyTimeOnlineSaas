<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreShortLeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'users' => ['nullable', 'array'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', 'distinct', 'exists:companies,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'distinct', 'exists:locations,id'],
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
            'reason' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'from_time' => ['nullable', 'date_format:H:i'],
            'to_time' => ['nullable', 'date_format:H:i'],
            'minutes' => ['required_if:short_leave_type,1,2', 'nullable', 'integer', 'min:1', 'max:1440'],
            'short_leave_type' => ['required', 'integer', Rule::in([1, 2, 3])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $users = (array) $this->input('users', []);
            $filterGroups = [
                (array) $this->input('company_ids', []),
                (array) $this->input('location_ids', []),
                ...array_values((array) $this->input('employee_filters', [])),
            ];
            $hasFilters = collect($filterGroups)
                ->flatten()
                ->contains(static fn (mixed $id): bool => filled($id));

            if ($users === [] && ! $hasFilters) {
                $validator->errors()->add('users', 'Select at least one employee or choose employee filters.');
            }

            if ((int) $this->input('short_leave_type') !== 3) {
                return;
            }

            if (! $this->filled('from_time') || ! $this->filled('to_time')) {
                $validator->errors()->add('from_time', 'From and to times are required for break short leave.');

                return;
            }

            if ($this->input('to_time') <= $this->input('from_time')) {
                $validator->errors()->add('to_time', 'The to time must be later than the from time.');
            }
        });
    }
}
