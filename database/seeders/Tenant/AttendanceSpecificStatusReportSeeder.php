<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\CustomReport;
use App\Models\Tenant\StatusMaster;
use App\Support\CustomReportDateRange;
use Illuminate\Database\Seeder;
use RuntimeException;

class AttendanceSpecificStatusReportSeeder extends Seeder
{
    private const REPORT_NAME = 'Attendance Specific Status Report';

    private const DEFAULT_STATUS_CODES = ['AA', 'AP', 'PA', 'AWO', 'WOP', 'AHL', 'HLA'];

    public function run(): void
    {
        $statusMasterIds = StatusMaster::query()
            ->whereIn('code', self::DEFAULT_STATUS_CODES)
            ->pluck('id', 'code')
            ->all();
        $missingStatusCodes = array_values(array_diff(self::DEFAULT_STATUS_CODES, array_keys($statusMasterIds)));

        if ($missingStatusCodes !== []) {
            throw new RuntimeException(
                'Cannot seed the Attendance Specific Status Report; missing status codes: '.implode(', ', $missingStatusCodes),
            );
        }

        $selectedStatusIds = array_map(
            static fn (string $code): int => (int) $statusMasterIds[$code],
            self::DEFAULT_STATUS_CODES,
        );
        $dateRange = app(CustomReportDateRange::class)->resolve('this_month');
        $report = CustomReport::query()->firstOrCreate(
            ['report_name' => self::REPORT_NAME],
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
            'attendance_specific_status',
        ];
        $fields = array_map(static function (string $fieldName) use ($selectedStatusIds): array {
            $isSpecificStatus = $fieldName === 'attendance_specific_status';

            return [
                'field_name' => $fieldName,
                'date_time_format' => 'default',
                'minute_format' => 'default',
                'status_master_ids' => $isSpecificStatus ? $selectedStatusIds : [],
                'use_in_summary' => $isSpecificStatus,
                'half_status_count_as_half' => false,
            ];
        }, $fieldNames);

        $report->custom_report_details()->firstOrCreate([], [
            'custom_report_fields' => $fields,
            'custom_report_filter' => [],
        ]);
    }
}
