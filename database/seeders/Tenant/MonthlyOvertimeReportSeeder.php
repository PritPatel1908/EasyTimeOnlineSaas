<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CustomReport;
use App\Support\CustomReportDateRange;
use Illuminate\Database\Seeder;

class MonthlyOvertimeReportSeeder extends Seeder
{
    private const REPORT_NAME = 'Monthly Overtime Report';

    public function run(): void
    {
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $report = CustomReport::query()->firstOrCreate(
            ['report_name' => self::REPORT_NAME],
            [
                'from_date' => $dateRange['from']->toDateString(),
                'to_date' => $dateRange['to']->toDateString(),
                'date_range_preset' => 'this_month',
                'report_orientation' => 'horizontal',
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
            'approved_overtime_minutes',
        ];
        $fields = array_map(static function (string $fieldName): array {
            $isOvertimeMinutes = $fieldName === 'approved_overtime_minutes';

            return [
                'field_name' => $fieldName,
                'date_time_format' => 'default',
                'minute_format' => $isOvertimeMinutes ? 'hh_mm' : 'default',
                'use_in_summary' => $isOvertimeMinutes,
            ];
        }, $fieldNames);

        $report->custom_report_details()->firstOrCreate([], [
            'custom_report_fields' => $fields,
            'custom_report_filter' => [],
        ]);
    }
}
