<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLateComingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
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
                Rule::unique('late_coming_rules', 'code')->ignore($this->route('late_coming_rule')),
            ],
            'description' => ['nullable', 'string'],
            'ignore_late_coming_minutes' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
