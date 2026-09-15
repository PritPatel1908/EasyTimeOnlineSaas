<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => array_values((array) $this->input('company_id', [])),
            'location_id' => array_values((array) $this->input('location_id', [])),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:teams,code'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'integer', 'in:1,0'],
            'company_id' => ['nullable', 'array'],
            'company_id.*' => ['integer', 'exists:companies,id'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
        ];
    }
}
