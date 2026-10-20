<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'skip_days_on_month_end' => $this->boolean('skip_days_on_month_end'),
            'skip_days_on_year_end' => $this->boolean('skip_days_on_year_end'),
            'shift_status' => $this->boolean('shift_status'),
            'is_forever' => $this->boolean('is_forever'),
            'end_date' => $this->boolean('is_forever') ? null : $this->input('end_date'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rotationId = $this->route('shift_rotation');

        return [
            'code' => ['required', 'string', 'max:255', 'unique:shift_rotations,code,'.$rotationId],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'is_forever' => ['required', 'boolean'],
            'end_date' => $this->boolean('is_forever')
                ? ['nullable']
                : ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'skip_days_on_month_end' => ['required', 'boolean'],
            'skip_days_on_year_end' => ['required', 'boolean'],
            'shift_status' => ['required', 'boolean'],
            'status' => ['required', 'integer', 'in:1,0'],
            'rotation_values' => ['required', 'array', 'min:1'],
            'rotation_values.*.shift_id' => ['required', 'integer', 'distinct', 'exists:shifts,id'],
            'rotation_values.*.days' => ['required', 'integer', 'min:1'],
        ];
    }
}
