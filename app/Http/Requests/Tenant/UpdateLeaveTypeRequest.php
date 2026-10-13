<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leave_type');

        return [
            'code' => ['required', 'string', 'max:255', 'unique:leave_types,code,' . $leaveTypeId],
            'description' => ['nullable', 'string'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }

    public function messages(): array
    {
        return ['code.unique' => 'This leave type code is already in use.'];
    }
}
