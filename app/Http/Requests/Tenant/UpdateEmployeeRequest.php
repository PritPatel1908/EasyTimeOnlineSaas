<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['code'] = ['required', 'string', 'max:100', Rule::unique('users', 'code')->ignore($this->route('employee'))];
        $rules['password'] = ['exclude_unless:is_locked,1', 'nullable', 'string', 'min:8'];

        return $rules;
    }
}
