<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        CustomReportRequestRules::prepareJsonArrays($this);
    }

    public function rules(): array
    {
        return CustomReportRequestRules::rules();
    }
}
