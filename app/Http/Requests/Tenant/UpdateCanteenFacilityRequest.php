<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCanteenFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['company_contribution_in_percentage_wise' => $this->boolean('company_contribution_in_percentage_wise')]);
    }

    public function rules(): array
    {
        $id = $this->route('canteen_facility');
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('canteen_facilities', 'code')->ignore($id)],
            'total_cfa' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'integer', 'in:1,0'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'total_absent_days' => ['required', 'integer', 'min:0'],
            'company_contribution_in_percentage_wise' => ['required', 'boolean'],
            'company_allowance_contribution_in_fixed' => ['nullable', 'string', 'max:255'],
            'company_allowance_contribution_in_percentage' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('company_contribution_in_percentage_wise')) {
                if (blank($this->input('company_allowance_contribution_in_percentage'))) $validator->errors()->add('company_allowance_contribution_in_percentage', 'Percentage contribution is required.');
            } elseif (blank($this->input('company_allowance_contribution_in_fixed'))) {
                $validator->errors()->add('company_allowance_contribution_in_fixed', 'Fixed contribution is required.');
            }
        });
    }
}
