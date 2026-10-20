<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualPunchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'distinct', 'exists:locations,id'],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', 'distinct', 'exists:companies,id'],
            'punch_type' => ['required', 'string', 'in:1,2,3,4'],
            'punch_date' => ['required', 'date'],
            'punch_time' => ['required', 'date_format:H:i,H:i:s'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
