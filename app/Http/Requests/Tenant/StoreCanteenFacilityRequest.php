<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCanteenFacilityRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:canteen_facilities,code'],
            'total_cfa' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'integer', 'in:1,0'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.total_absent_days' => ['required', 'integer', 'min:0'],
            'rules.*.company_contribution_in_percentage_wise' => ['nullable', 'boolean'],
            'rules.*.company_allowance_contribution_in_fixed' => ['nullable', 'numeric', 'min:0'],
            'rules.*.company_allowance_contribution_in_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateContribution($validator);
        });
    }

    private function validateContribution(Validator $validator): void
    {
        foreach ($this->input('rules', []) as $index => $rule) {
            if (filter_var($rule['company_contribution_in_percentage_wise'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                if (blank($rule['company_allowance_contribution_in_percentage'] ?? null)) $validator->errors()->add("rules.{$index}.company_allowance_contribution_in_percentage", 'Percentage contribution is required.');
            } elseif (blank($rule['company_allowance_contribution_in_fixed'] ?? null)) {
                $validator->errors()->add("rules.{$index}.company_allowance_contribution_in_fixed", 'Fixed contribution is required.');
            }
        }
    }
}
