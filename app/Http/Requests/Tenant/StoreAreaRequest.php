<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_id' => $this->input('location_id', []),
            'company_id' => $this->input('company_id', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'unique:areas,code'],
            'name' => ['required', 'string', 'max:255'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
            'company_id' => ['nullable', 'array'],
            'company_id.*' => ['integer', 'exists:companies,id'],
            'time_limit_in_min' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'dms_area_id' => ['nullable', 'integer', 'min:0', 'max:9223372036854775807'],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }
}
