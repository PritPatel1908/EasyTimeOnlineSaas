<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

class UpdateManualPunchRequest extends StoreManualPunchRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'users' => ['required', 'array', 'min:1'],
        ]);
    }
}
