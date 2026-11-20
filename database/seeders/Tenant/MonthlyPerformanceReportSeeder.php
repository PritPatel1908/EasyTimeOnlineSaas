<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CustomReport;
use App\Support\CustomReportDateRange;
use Illuminate\Database\Seeder;

class MonthlyPerformanceReportSeeder extends Seeder
{
    public function run(): void
    {
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $report = CustomReport::query()->firstOrCreate(
            ['report_name' => 'Monthly Performance Report'],
            [
                'from_date' => $dateRange['from']->toDateString(),
                'to_date' => $dateRange['to']->toDateString(),
                'date_range_preset' => 'this_month',
                'report_orientation' => 'vertical',
                'summary_orientation' => 'horizontal',
                'status' => 1,
            ],
        );

        $fieldNames = [
            'emp_code',
            'emp_card',
            'name',
            'company_name',
            'department_name',
            'designation_name',
            'category_name',
            'date',
            'shift_name',
            'attendance_in_time',
            'attendance_out_time',
            'late_minutes',
            'early_minutes',
            'working_hrs',
            'overtime_minutes',
            'approved_overtime_minutes',
            'attendance_status',
        ];
        $summaryFields = [
            'late_minutes',
            'early_minutes',
            'working_hrs',
            'overtime_minutes',
            'approved_overtime_minutes',
            'attendance_status',
        ];
        $fields = array_map(static function (string $fieldName) use ($summaryFields): array {
            return [
                'field_name' => $fieldName,
                'date_time_format' => in_array($fieldName, ['attendance_in_time', 'attendance_out_time'], true)
                    ? 'h:i A'
                    : 'default',
                'minute_format' => in_array($fieldName, [
                    'late_minutes',
                    'early_minutes',
                    'working_hrs',
                    'overtime_minutes',
                    'approved_overtime_minutes',
                ], true) ? 'hh_mm' : 'default',
                'use_in_summary' => in_array($fieldName, $summaryFields, true),
            ];
        }, $fieldNames);

        $report->custom_report_details()->firstOrCreate([], [
            'custom_report_fields' => $fields,
            'custom_report_filter' => [],
        ]);
    }
}
