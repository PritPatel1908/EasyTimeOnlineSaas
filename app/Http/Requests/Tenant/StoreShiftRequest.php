<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
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
            'is_night_shift' => $this->boolean('is_night_shift'),
            'set_cutoff' => $this->boolean('set_cutoff'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:shifts,code'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
            'company_id' => ['nullable', 'array'],
            'company_id.*' => ['integer', 'exists:companies,id'],
            'in_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'out_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'first_half_end_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'second_half_start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'brack_start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'brack_end_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'auto_from' => ['nullable', 'date_format:H:i,H:i:s'],
            'auto_to' => ['nullable', 'date_format:H:i,H:i:s'],
            'is_night_shift' => ['required', 'boolean'],
            'set_cutoff' => ['required', 'boolean'],
            'cutoff_time' => ['nullable', 'required_if:set_cutoff,1', 'date_format:H:i,H:i:s'],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'This shift code is already in use.',
            'location_id.*.exists' => 'One of the selected locations does not exist.',
            'company_id.*.exists' => 'One of the selected companies does not exist.',
        ];
    }
}
