<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'machine_ip' => ['nullable', 'string', 'max:255'],
            'machine_serial_number' => ['nullable', 'string', 'max:255'],
            'dms_device_id' => ['nullable', 'integer', 'min:0', 'max:' . PHP_INT_MAX],
            'dms_area_id' => ['nullable', 'integer', 'min:0', 'max:' . PHP_INT_MAX],
            'access_direction' => ['nullable', 'in:in,out,both,0,1,2'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'status' => ['required', 'integer', 'in:1,0'],
        ];
    }
}
