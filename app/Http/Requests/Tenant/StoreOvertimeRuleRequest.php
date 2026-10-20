<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOvertimeRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ignore_early_come_minutes' => $this->input('ignore_early_come_minutes') ?: null,
            'ignore_late_come_minutes' => $this->input('ignore_late_come_minutes') ?: null,
            'ignore_late_going' => $this->input('ignore_late_going', false),
            'ignore_overtime' => $this->input('ignore_overtime', 0),
            'ignore_late_going_overtime' => $this->input('ignore_late_going_overtime', 0),
            'status' => $this->input('status', 1),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('overtime_rules', 'code')],
            'description' => ['nullable', 'string'],
            'ignore_early_come_minutes' => ['nullable', 'date_format:H:i,H:i:s'],
            'ignore_late_come_minutes' => ['nullable', 'date_format:H:i,H:i:s'],
            'ignore_late_going' => ['required', 'boolean'],
            'ignore_overtime' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'ignore_late_going_overtime' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
            'slabs' => ['nullable', 'array'],
            'slabs.*' => ['array'],
            'slabs.*.value_from' => ['nullable', 'date_format:H:i,H:i:s'],
            'slabs.*.value_to' => ['nullable', 'date_format:H:i,H:i:s'],
            'slabs.*.head_value' => ['nullable', 'date_format:H:i,H:i:s'],
            'slabs.*.decimal_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
