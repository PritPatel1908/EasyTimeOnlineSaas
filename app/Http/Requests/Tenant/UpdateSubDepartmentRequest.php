<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department_id' => array_values((array) $this->input('department_id', [])),
            'location_id' => array_values((array) $this->input('location_id', [])),
        ]);
    }

    public function rules(): array
    {
        $subDepartmentId = $this->route('sub_department');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sub_departments,code,' . $subDepartmentId],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'integer', 'in:1,0'],
            'department_id' => ['nullable', 'array'],
            'department_id.*' => ['integer', 'exists:departments,id'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
        ];
    }
}
