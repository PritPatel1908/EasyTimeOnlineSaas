<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualAttendanceRequest extends FormRequest
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
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'shift_type' => ['nullable', 'string', 'in:auto,fixed,rotational'],
            'shift_id' => ['nullable', 'required_if:shift_type,auto,fixed', 'prohibited_unless:shift_type,auto,fixed', 'integer', 'exists:shifts,id'],
            'shift_rotation_id' => ['nullable', 'required_if:shift_type,rotational', 'prohibited_unless:shift_type,rotational', 'integer', 'exists:shift_rotations,id'],
            'in_time' => ['required', 'date'],
            'out_time' => ['required', 'date', 'after_or_equal:in_time'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
