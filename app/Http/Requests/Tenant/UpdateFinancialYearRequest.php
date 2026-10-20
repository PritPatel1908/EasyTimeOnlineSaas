<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\FinancialYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $values = [
            'location_id' => $this->selectionArray($this->input('location_id')),
            'company_id' => $this->selectionArray($this->input('company_id')),
        ];

        $financialYear = FinancialYear::query()->findOrFail($this->route('financial_year'));
        if ($financialYear->is_started) {
            $values = array_merge($values, [
                'start_date' => $financialYear->start_date->format('Y-m-d'),
                'end_date' => $financialYear->end_date->format('Y-m-d'),
                'status' => $financialYear->status,
            ]);
        }

        $this->merge($values);
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
                'max:50',
                Rule::unique('financial_years', 'code')->ignore($this->route('financial_year')),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'distinct', 'exists:locations,id'],
            'company_id' => ['nullable', 'array'],
            'company_id.*' => ['integer', 'distinct', 'exists:companies,id'],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function selectionArray(mixed $selection): array
    {
        if ($selection === null || $selection === '') {
            return [];
        }

        return is_array($selection) ? $selection : [$selection];
    }
}
