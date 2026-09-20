<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => array_values((array) $this->input('company_id', [])),
            'location_id' => array_values((array) $this->input('location_id', [])),
            'category_id' => array_values(array_filter((array) $this->input('category_id', []), static fn($id): bool => $id !== '')),
        ]);
    }

    public function rules(): array
    {
        return DesignationRequestRules::rules();
    }
}
