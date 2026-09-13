<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_id' => $this->input('location_id', []),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:companies,code'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'integer', 'in:1,0'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'This company code is already in use.',
            'location_id.array' => 'Please select one or more locations.',
            'location_id.*.exists' => 'One of the selected locations does not exist.',
        ];
    }
}
