<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Support\CustomReportFieldOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CustomReportRequestRules
{
    public static function prepareJsonArrays(FormRequest $request): void
    {
        foreach (['custom_report_fields', 'custom_report_filter'] as $field) {
            $value = $request->input($field);
            if (! is_string($value)) {
                continue;
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge([$field => $decoded]);
            }
        }
    }

    public static function rules(): array
    {
        return [
            'report_name' => ['required', 'string', 'max:255'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'report_orientation' => ['required', 'string', 'in:vertical,horizontal'],
            'status' => ['required', 'integer', 'in:1,0'],
            'custom_report_fields' => ['required', 'array', 'min:1'],
            'custom_report_fields.*' => ['required', 'array'],
            'custom_report_fields.*.field_name' => ['required', 'string', Rule::in(array_keys(CustomReportFieldOptions::all()))],
            'custom_report_fields.*.date_time_format' => ['required', 'string', 'in:default,d-m-Y,m-d-Y,Y-m-d,d/m/Y,m/d/Y,H:i,h:i A,d-m-Y H:i,Y-m-d H:i'],
            'custom_report_fields.*.use_in_summary' => ['required', 'boolean'],
            'custom_report_filter' => ['present', 'array'],
        ];
    }
}
