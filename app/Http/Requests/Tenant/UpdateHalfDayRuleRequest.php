<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHalfDayRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'single_punch_allowed' => $this->input('single_punch_allowed', false),
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
                Rule::unique('half_day_rules', 'code')->ignore($this->route('half_day_rule')),
            ],
            'description' => ['nullable', 'string'],
            'single_punch_allowed' => ['required', 'boolean'],
            'work_hr_less_than_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'late_coming_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'no_of_late' => ['nullable', 'numeric', 'min:0'],
            'consecutive_late_coming' => ['nullable', 'integer', 'min:0'],
            'ignore_month_end_late' => ['required', 'boolean'],
            'early_going_minutes' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'no_of_early' => ['nullable', 'numeric', 'min:0'],
            'consecutive_early_going' => ['nullable', 'numeric', 'min:0'],
            'ignore_month_end_early' => ['required', 'boolean'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
