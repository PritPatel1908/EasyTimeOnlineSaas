<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => array_values((array) $this->input('company_id', [])),
            'location_id' => array_values((array) $this->input('location_id', [])),
            'leave_type_id' => array_values(array_filter((array) $this->input('leave_type_id', []), static fn ($id): bool => $id !== '')),
        ]);
    }

    public function rules(): array
    {
        return CategoryRequestRules::rules();
    }
}
