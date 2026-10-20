<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbsentRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_single_punch' => $this->input('allow_single_punch', false),
            'ignore_month_end_late' => $this->input('ignore_month_end_late', false),
            'ignore_month_end_early' => $this->input('ignore_month_end_early', false),
            'status' => $this->input('status', 1),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('absent_rules', 'code')->ignore($this->route('absent_rule')),
            ],
            'description' => ['nullable', 'string'],
            'work_hr_less_than_minutes' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'allow_single_punch' => ['required', 'boolean'],
            'late_coming_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'no_of_late' => ['nullable', 'numeric', 'min:0'],
            'consecutive_late_coming' => ['nullable', 'numeric', 'min:0'],
            'ignore_month_end_late' => ['required', 'boolean'],
            'early_going_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'no_of_early' => ['nullable', 'numeric', 'min:0'],
            'consecutive_early_going' => ['nullable', 'numeric', 'min:0'],
            'ignore_month_end_early' => ['required', 'boolean'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
