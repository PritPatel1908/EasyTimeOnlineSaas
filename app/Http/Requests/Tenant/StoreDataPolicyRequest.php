<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreDataPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'unique:data_policies,code'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'self_only' => ['nullable', 'boolean'],
            'all_locations' => ['nullable', 'boolean'],
            'all_companies' => ['nullable', 'boolean'],
            'all_departments' => ['nullable', 'boolean'],
            'all_teams' => ['nullable', 'boolean'],
            'all_sub_departments' => ['nullable', 'boolean'],
            'all_categories' => ['nullable', 'boolean'],
            'all_sub_categories' => ['nullable', 'boolean'],
            'all_designations' => ['nullable', 'boolean'],
            'all_grades' => ['nullable', 'boolean'],
            'all_units' => ['nullable', 'boolean'],
            'all_bus_routes' => ['nullable', 'boolean'],
            'all_areas' => ['nullable', 'boolean'],
            'all_machines' => ['nullable', 'boolean'],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['integer', 'exists:locations,id'],
            'companies' => ['nullable', 'array'],
            'companies.*' => ['integer', 'exists:companies,id'],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['integer', 'exists:departments,id'],
            'teams' => ['nullable', 'array'],
            'teams.*' => ['integer', 'exists:teams,id'],
            'sub_departments' => ['nullable', 'array'],
            'sub_departments.*' => ['integer', 'exists:sub_departments,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'sub_categories' => ['nullable', 'array'],
            'sub_categories.*' => ['integer', 'exists:sub_categories,id'],
            'designations' => ['nullable', 'array'],
            'designations.*' => ['integer', 'exists:designations,id'],
            'grades' => ['nullable', 'array'],
            'grades.*' => ['integer', 'exists:grades,id'],
            'units' => ['nullable', 'array'],
            'units.*' => ['integer', 'exists:units,id'],
            'bus_routes' => ['nullable', 'array'],
            'bus_routes.*' => ['integer', 'exists:bus_routes,id'],
            'areas' => ['nullable', 'array'],
            'areas.*' => ['integer', 'exists:areas,id'],
            'machines' => ['nullable', 'array'],
            'machines.*' => ['integer', 'exists:machines,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $booleanFields = [
            'status',
            'self_only',
            'all_locations',
            'all_companies',
            'all_departments',
            'all_teams',
            'all_sub_departments',
            'all_categories',
            'all_sub_categories',
            'all_designations',
            'all_grades',
            'all_units',
            'all_bus_routes',
            'all_areas',
            'all_machines',
        ];

        $values = [];
        foreach ($booleanFields as $field) {
            $values[$field] = $this->boolean($field);
        }

        $this->merge($values);
    }
}
