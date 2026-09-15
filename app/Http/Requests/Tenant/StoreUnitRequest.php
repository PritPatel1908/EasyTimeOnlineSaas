<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['location_id' => array_values((array) $this->input('location_id', []))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('units', 'code')],
            'status' => ['required', 'integer', 'in:1,0'],
            'location_id' => ['nullable', 'array'],
            'location_id.*' => ['integer', 'exists:locations,id'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->ensureLocationsAreAccessible();
    }

    private function ensureLocationsAreAccessible(): void
    {
        $ids = array_filter($this->validated('location_id', []));
        if (count($ids) !== Location::query()->whereIn('id', $ids)->count()) {
            abort(403, 'One or more selected locations are outside your data policy.');
        }
    }
}