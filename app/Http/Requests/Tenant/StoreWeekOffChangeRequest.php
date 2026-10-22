<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Enums\WOTypeEnumn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWeekOffChangeRequest extends FormRequest
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
            'from_date' => ['required', 'date'],
            'is_forever' => ['nullable', 'boolean'],
            'to_date' => ['exclude_if:is_forever,1', 'required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.week_days' => [
                'required',
                'integer',
                'between:0,6',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $index = explode('.', $attribute)[1] ?? null;
                    $detail = $index === null ? [] : $this->input('details.'.$index, []);
                    $weekFields = ['first_week', 'second_week', 'third_week', 'fourth_week', 'fifth_week'];
                    $hasWeek = collect($weekFields)->contains(
                        static fn (string $field): bool => filter_var($detail[$field] ?? false, FILTER_VALIDATE_BOOLEAN)
                    );

                    if (! $hasWeek) {
                        $fail('Select at least one applicable week for each weekly-off detail.');
                    }
                },
            ],
            'details.*.wo_type' => ['required', Rule::in(array_map(
                static fn ($type): int => $type->value,
                WOTypeEnumn::cases()
            ))],
            'details.*.first_week' => ['nullable', 'boolean'],
            'details.*.second_week' => ['nullable', 'boolean'],
            'details.*.third_week' => ['nullable', 'boolean'],
            'details.*.fourth_week' => ['nullable', 'boolean'],
            'details.*.fifth_week' => ['nullable', 'boolean'],
        ];
    }
}
