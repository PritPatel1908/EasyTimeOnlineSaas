<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEarlyGoingRuleRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:255', Rule::unique('early_going_rules', 'code')],
            'description' => ['nullable', 'string'],
            'ignore_early_going_minutes' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'status' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
