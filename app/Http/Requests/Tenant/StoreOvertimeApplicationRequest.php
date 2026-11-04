<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
            'overtime_minutes' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
