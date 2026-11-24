<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CustomReport;
use App\Support\CustomReportDateRange;
use Illuminate\Database\Seeder;

class MonthlyManualPunchReportSeeder extends Seeder
{
    private const REPORT_NAME = 'Monthly Manual Punch Report';

    public function run(): void
    {
        $dateRange = app(CustomReportDateRange::class)->resolve('last_month');
        $report = CustomReport::query()->firstOrCreate(
            ['report_name' => self::REPORT_NAME],
            [
                'from_date' => $dateRange['from']->toDateString(),
                'to_date' => $dateRange['to']->toDateString(),
                'date_range_preset' => 'last_month',
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
            'manual_punch',
        ];
        $fields = array_map(static fn (string $fieldName): array => [
            'field_name' => $fieldName,
            'date_time_format' => 'default',
            'minute_format' => 'default',
            'use_in_summary' => false,
        ], $fieldNames);

        $report->custom_report_details()->firstOrCreate([], [
            'custom_report_fields' => $fields,
            'custom_report_filter' => [],
        ]);
    }
}
