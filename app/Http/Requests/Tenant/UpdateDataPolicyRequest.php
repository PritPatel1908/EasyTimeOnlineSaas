<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataPolicyRequest extends StoreDataPolicyRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('data_policies', 'code')->ignore($this->route('dataPolicy')),
            ],
        ]);
    }
}
