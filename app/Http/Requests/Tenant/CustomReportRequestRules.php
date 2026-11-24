<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Support\CustomReportFieldOptions;
use App\Support\CustomReportFilterOptions;
use Illuminate\Contracts\Validation\Validator;
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

    public static function withValidator(FormRequest $request, Validator $validator): void
    {
        $validator->after(static function (Validator $validator) use ($request): void {
            $fields = $request->input('custom_report_fields', []);
            if (! is_array($fields)) {
                return;
            }

            foreach ($fields as $index => $field) {
                if (is_array($field) && ($field['field_name'] ?? '') === 'machine_in_out'
                    && (! is_array($field['machine_in_out'] ?? null) || $field['machine_in_out'] === [])) {
                    $validator->errors()->add(
                        "custom_report_fields.{$index}.machine_in_out",
                        'Add at least one machine with a sequence and sub sequence.',
                    );
                }

                if (! is_array($field)
                    || ! CustomReportFieldOptions::supportsStatusSelection((string) ($field['field_name'] ?? ''))) {
                    continue;
                }

                if (! is_array($field['status_master_ids'] ?? null) || $field['status_master_ids'] === []) {
                    $validator->errors()->add(
                        "custom_report_fields.{$index}.status_master_ids",
                        'Select at least one attendance status.',
                    );
                }
            }
        });
    }

    public static function rules(): array
    {
        $rules = [
            'report_name' => ['required', 'string', 'max:255'],
            'date_range_preset' => ['required', 'string', 'in:today,yesterday,this_week,last_week,this_month,last_month,this_quarter,last_quarter,this_year,last_year,custom'],
            'from_date' => ['exclude_unless:date_range_preset,custom', 'required', 'date'],
            'to_date' => ['exclude_unless:date_range_preset,custom', 'required', 'date', 'after_or_equal:from_date'],
            'report_orientation' => ['required', 'string', 'in:vertical,horizontal'],
            'summary_orientation' => ['required', 'string', 'in:vertical,horizontal'],
            'status' => ['required', 'integer', 'in:1,0'],
            'custom_report_fields' => ['required', 'array', 'min:1'],
            'custom_report_fields.*' => ['required', 'array'],
            'custom_report_fields.*.field_name' => ['required', 'string', Rule::in(array_keys(CustomReportFieldOptions::all()))],
            'custom_report_fields.*.date_time_format' => ['required', 'string', 'in:default,d-m-Y,m-d-Y,Y-m-d,d/m/Y,m/d/Y,H:i,h:i A,d-m-Y H:i,Y-m-d H:i'],
            'custom_report_fields.*.minute_format' => ['required', 'string', 'in:default,hh_mm,decimal'],
            'custom_report_fields.*.machine_in_out' => ['nullable', 'array'],
            'custom_report_fields.*.machine_in_out.*' => ['required', 'array'],
            'custom_report_fields.*.machine_in_out.*.machine_id' => ['required', 'integer', Rule::exists('machines', 'id')],
            'custom_report_fields.*.machine_in_out.*.sequence' => ['required', 'integer', 'min:1'],
            'custom_report_fields.*.machine_in_out.*.sub_sequence' => ['required', 'integer', 'min:1'],
            'custom_report_fields.*.status_master_ids' => ['nullable', 'array'],
            'custom_report_fields.*.status_master_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('status_masters', 'id'),
            ],
            'custom_report_fields.*.use_in_summary' => ['required', 'boolean'],
            'custom_report_fields.*.use_only_in_summary' => ['sometimes', 'boolean'],
            'custom_report_fields.*.show_fixed_hrs' => ['sometimes', 'boolean'],
            'custom_report_fields.*.half_status_count_as_half' => ['sometimes', 'boolean'],
            'custom_report_filter' => ['present', 'array'],
        ];

        foreach (CustomReportFilterOptions::definitions() as $key => $definition) {
            $rules["custom_report_filter.{$key}"] = ['sometimes', 'array'];
            $rules["custom_report_filter.{$key}.*"] = [
                'integer',
                'distinct',
                Rule::exists($definition['model']::query()->getModel()->getTable(), 'id'),
            ];
        }

        $rules['custom_report_filter.employee_ids'] = ['sometimes', 'array'];
        $rules['custom_report_filter.employee_ids.*'] = [
            'integer',
            'distinct',
            Rule::exists('users', 'id')->where('user_type', 'employee'),
        ];

        return $rules;
    }
}
