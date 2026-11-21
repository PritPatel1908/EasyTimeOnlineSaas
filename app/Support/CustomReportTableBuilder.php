<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\AttendanceLog;
use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\LegacyLeaveGroup;
use App\Models\Tenant\Location;
use App\Models\Tenant\Machine;
use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\Role;
use App\Models\Tenant\Shift;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Illuminate\Support\Collection;

final class CustomReportTableBuilder
{
    private const SNAPSHOT_FIELDS = [
        'role' => ['model' => Role::class, 'column' => null, 'value' => 'name'],
        'data_policy' => ['model' => DataPolicy::class, 'column' => 'data_policy_id', 'value' => 'name'],
        'location_name' => ['model' => Location::class, 'column' => 'location_id', 'value' => 'name'],
        'location_code' => ['model' => Location::class, 'column' => 'location_id', 'value' => 'code'],
        'company_name' => ['model' => Company::class, 'column' => 'company_id', 'value' => 'name'],
        'company_code' => ['model' => Company::class, 'column' => 'company_id', 'value' => 'code'],
        'department_name' => ['model' => Department::class, 'column' => 'department_id', 'value' => 'name'],
        'department_code' => ['model' => Department::class, 'column' => 'department_id', 'value' => 'code'],
        'sub_department_name' => ['model' => SubDepartment::class, 'column' => 'sub_department_id', 'value' => 'name'],
        'sub_department_code' => ['model' => SubDepartment::class, 'column' => 'sub_department_id', 'value' => 'code'],
        'designation_name' => ['model' => Designation::class, 'column' => 'designation_id', 'value' => 'name'],
        'designation_code' => ['model' => Designation::class, 'column' => 'designation_id', 'value' => 'code'],
        'unit_mill_name' => ['model' => Unit::class, 'column' => 'unit_id', 'value' => 'name'],
        'unit_mill_code' => ['model' => Unit::class, 'column' => 'unit_id', 'value' => 'code'],
        'leave_group_name' => ['model' => LegacyLeaveGroup::class, 'column' => 'leave_group_id', 'value' => 'name'],
        'team_name' => ['model' => Team::class, 'column' => 'team_id', 'value' => 'name'],
        'team_code' => ['model' => Team::class, 'column' => 'team_id', 'value' => 'code'],
        'canteen_facility_name' => ['model' => CanteenFacility::class, 'column' => 'canteen_facility_id', 'value' => 'name'],
        'canteen_facility_code' => ['model' => CanteenFacility::class, 'column' => 'canteen_facility_id', 'value' => 'code'],
        'category_name' => ['model' => Category::class, 'column' => 'category_id', 'value' => 'name'],
        'category_code' => ['model' => Category::class, 'column' => 'category_id', 'value' => 'code'],
    ];

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  iterable<User>  $employees
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function buildForEmployees(
        array $fields,
        string $fromDate,
        string $toDate,
        string $orientation,
        iterable $employees,
        string $summaryOrientation = 'vertical',
    ): array {
        $employees = collect($employees)->values();
        $fieldLabels = CustomReportFieldOptions::all();
        $fields = array_values(array_filter($fields, 'is_array'));
        $machineInOutSequences = $this->machineInOutSequenceDefinitions($fields);
        $hasMachineInOut = in_array('machine_in_out', array_column($fields, 'field_name'), true);
        $statusMasterFilterIds = $this->selectedStatusMasterIds($fields);
        $selectedStatusCodes = $statusMasterFilterIds === []
            ? []
            : StatusMaster::query()
                ->whereIn('id', $statusMasterFilterIds)
                ->orderBy('code')
                ->pluck('code')
                ->map(static fn (mixed $code): string => (string) $code)
                ->all();
        foreach ($fields as &$field) {
            if (CustomReportFieldOptions::supportsStatusSelection((string) ($field['field_name'] ?? ''))) {
                $field['_selected_status_codes'] = $selectedStatusCodes;
            }
        }
        unset($field);
        $labels = array_map(
            static fn (array $field): string => $fieldLabels[$field['field_name'] ?? ''] ?? (string) ($field['field_name'] ?? ''),
            $fields,
        );
        $dateIndexes = [];
        foreach ($fields as $index => $field) {
            if (($field['field_name'] ?? '') === 'date') {
                $dateIndexes[] = $index;
            }
        }

        $dates = [];
        if ($dateIndexes !== [] || $hasMachineInOut) {
            $start = new DateTimeImmutable($fromDate);
            $end = (new DateTimeImmutable($toDate))->modify('+1 day');
            foreach (new DatePeriod($start, new DateInterval('P1D'), $end) as $date) {
                $dates[] = $date;
            }
        }

        $snapshotFields = array_intersect(
            array_column($fields, 'field_name'),
            array_merge(array_keys(self::SNAPSHOT_FIELDS), ['approvers']),
        );
        $snapshots = $this->snapshots($snapshotFields, $employees);
        $liveMasterValues = $this->liveMasterValues($fields, $employees, $snapshots['assignments']);
        $attendanceValues = $this->attendanceValues($fields, $employees, $fromDate, $toDate, $machineInOutSequences);
        $rows = [];

        if (($dateIndexes !== [] || $hasMachineInOut) && $orientation === 'horizontal') {
            $masterIndexes = [];
            $detailIndexes = [];
            foreach ($fields as $index => $field) {
                $fieldName = $field['field_name'] ?? '';
                if ($fieldName === 'date') {
                    continue;
                }
                if (in_array($fieldName, CustomReportFieldOptions::dateDetailFields(), true)) {
                    $detailIndexes[] = $index;
                } else {
                    $masterIndexes[] = $index;
                }
            }

            $dateFormat = $dateIndexes === []
                ? 'Y-m-d'
                : $this->dateFormat($fields[$dateIndexes[0]]['date_time_format'] ?? 'default');

            foreach ($employees as $employee) {
                $employeeId = (int) $employee->getKey();
                $employeeDates = $dates;
                if ($rows !== []) {
                    $rows[] = [];
                }

                $masterHeaders = array_map(static fn (int $index): string => $labels[$index], $masterIndexes);
                $masterValues = [];
                foreach ($masterIndexes as $index) {
                    $field = $fields[$index];
                    $masterDate = ($field['field_name'] ?? '') === 'approvers'
                        ? new DateTimeImmutable($toDate)
                        : new DateTimeImmutable($fromDate);
                    $masterValues[] = $this->employeeValue(
                        $employee,
                        $field['field_name'] ?? '',
                        $snapshots,
                        $liveMasterValues,
                        $attendanceValues,
                        $this->fieldFormat($field),
                        $masterDate,
                    );
                }
                if ($masterHeaders === []) {
                    $masterHeaders = ['Employee'];
                    $masterValues[] = (string) ($employee->name ?: $employee->code ?: $employee->getKey());
                }
                $rows[] = $masterHeaders;
                $rows[] = array_values($masterValues);

                $rows[] = [];
                $rows[] = array_merge(
                    ['Date'],
                    array_map(static fn (DateTimeImmutable $date): string => $date->format($dateFormat), $employeeDates),
                );
                foreach ($detailIndexes as $index) {
                    $field = $fields[$index];
                    $fieldName = $field['field_name'] ?? '';
                    if ($fieldName === 'machine_in_out') {
                        $sequences = $machineInOutSequences[$index] ?? [];
                        if ($sequences === []) {
                            $detailRow = [$labels[$index]];
                            foreach ($employeeDates as $_date) {
                                $detailRow[] = '';
                            }
                            $rows[] = $detailRow;

                            continue;
                        }

                        foreach ($sequences as $sequence => $definition) {
                            $detailRow = [$definition['label']];
                            foreach ($employeeDates as $date) {
                                $dateKey = $date->format('Y-m-d');
                                $detailRow[] = $attendanceValues[$employeeId][$dateKey]['machine_in_out'][$index][$sequence] ?? '';
                            }
                            $rows[] = $detailRow;
                        }

                        continue;
                    }

                    $detailRow = [$labels[$index]];
                    foreach ($employeeDates as $date) {
                        $detailRow[] = $this->employeeValue(
                            $employee,
                            $fieldName,
                            $snapshots,
                            $liveMasterValues,
                            $attendanceValues,
                            $this->fieldFormat($field),
                            $date,
                        );
                    }
                    $rows[] = $detailRow;
                }

                $this->appendSummaryRows(
                    $rows,
                    $fields,
                    [$employeeId => $attendanceValues[$employeeId] ?? []],
                    $summaryOrientation,
                );
            }

            return ['headers' => [], 'rows' => $rows];
        }

        if ($dateIndexes !== [] || $hasMachineInOut) {
            $masterIndexes = [];
            $detailIndexes = [];
            foreach ($fields as $index => $field) {
                if (($field['field_name'] ?? '') === 'date') {
                    $detailIndexes[] = $index;
                } elseif (in_array($field['field_name'] ?? '', CustomReportFieldOptions::dateDetailFields(), true)) {
                    $detailIndexes[] = $index;
                } else {
                    $masterIndexes[] = $index;
                }
            }
            usort($detailIndexes, static function (int $left, int $right) use ($fields): int {
                $leftIsDate = ($fields[$left]['field_name'] ?? '') === 'date';
                $rightIsDate = ($fields[$right]['field_name'] ?? '') === 'date';

                return $leftIsDate === $rightIsDate ? $left <=> $right : ($leftIsDate ? -1 : 1);
            });

            $headers = [];
            $detailHeaders = ['Date'];
            foreach ($detailIndexes as $index) {
                $fieldName = $fields[$index]['field_name'] ?? '';
                if ($fieldName === 'date') {
                    continue;
                }
                if ($fieldName === 'machine_in_out') {
                    $sequences = $machineInOutSequences[$index] ?? [];
                    if ($sequences === []) {
                        $detailHeaders[] = $labels[$index];
                    } else {
                        foreach ($sequences as $definition) {
                            $detailHeaders[] = $definition['label'];
                        }
                    }

                    continue;
                }
                $detailHeaders[] = $labels[$index];
            }
            $startDate = new DateTimeImmutable($fromDate);
            $dateFormat = $dateIndexes === []
                ? 'Y-m-d'
                : $this->dateFormat($fields[$dateIndexes[0]]['date_time_format'] ?? 'default');

            foreach ($employees as $employeeIndex => $employee) {
                $employeeId = (int) $employee->getKey();
                $employeeDates = $dates;
                if ($employeeIndex > 0) {
                    $rows[] = [];
                }

                $masterValues = [];
                foreach ($masterIndexes as $index) {
                    $field = $fields[$index];
                    $masterDate = ($field['field_name'] ?? '') === 'approvers'
                        ? new DateTimeImmutable($toDate)
                        : $startDate;
                    $masterValues[$index] = $this->employeeValue(
                        $employee,
                        $field['field_name'] ?? '',
                        $snapshots,
                        $liveMasterValues,
                        $attendanceValues,
                        $this->fieldFormat($field),
                        $masterDate,
                    );
                }
                $masterHeaders = array_map(static fn (int $index): string => $labels[$index], $masterIndexes);
                if ($masterHeaders === []) {
                    $masterHeaders = ['Employee'];
                    $masterValues[] = (string) ($employee->name ?: $employee->code ?: $employee->getKey());
                }
                $rows[] = $masterHeaders;
                $rows[] = array_values($masterValues);
                $rows[] = [];
                $rows[] = $detailHeaders;

                foreach ($employeeDates as $date) {
                    $row = [$date->format($dateFormat)];
                    foreach ($detailIndexes as $index) {
                        $field = $fields[$index];
                        $fieldName = $field['field_name'] ?? '';
                        if ($fieldName === 'date') {
                            continue;
                        }
                        if ($fieldName === 'machine_in_out') {
                            $sequences = $machineInOutSequences[$index] ?? [];
                            if ($sequences === []) {
                                $row[] = '';
                            } else {
                                $dateKey = $date->format('Y-m-d');
                                foreach ($sequences as $sequence => $_definition) {
                                    $row[] = $attendanceValues[$employeeId][$dateKey]['machine_in_out'][$index][$sequence] ?? '';
                                }
                            }

                            continue;
                        }

                        $row[] = $this->employeeValue(
                            $employee,
                            $fieldName,
                            $snapshots,
                            $liveMasterValues,
                            $attendanceValues,
                            $this->fieldFormat($field),
                            $date,
                        );
                    }
                    $rows[] = $row;
                }

                $this->appendSummaryRows(
                    $rows,
                    $fields,
                    [$employeeId => $attendanceValues[$employeeId] ?? []],
                    $summaryOrientation,
                );
            }

            return ['headers' => $headers, 'rows' => $rows];
        }

        $headers = $labels;
        $statusCodes = $summaryOrientation === 'horizontal'
            ? $this->summaryStatusCodes($fields, $attendanceValues)
            : [];
        if ($summaryOrientation === 'horizontal') {
            $headers = array_merge($headers, $this->horizontalSummaryHeaders($fields, $statusCodes));
        }
        foreach ($employees as $employee) {
            $employeeId = (int) $employee->getKey();
            $row = array_map(
                fn (array $field): string => $this->employeeValue(
                    $employee,
                    $field['field_name'] ?? '',
                    $snapshots,
                    $liveMasterValues,
                    $attendanceValues,
                    $this->fieldFormat($field),
                    null,
                ),
                $fields,
            );
            if ($summaryOrientation === 'horizontal') {
                $row = array_merge(
                    $row,
                    $this->horizontalSummaryValues($fields, $attendanceValues[$employeeId] ?? [], $statusCodes),
                );
                $rows[] = $row;
            } else {
                $rows[] = $row;
                $this->appendSummaryRows(
                    $rows,
                    $fields,
                    [$employeeId => $attendanceValues[$employeeId] ?? []],
                    $summaryOrientation,
                );
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function fieldFormat(array $field): mixed
    {
        return CustomReportFieldOptions::supportsMinuteFormat((string) ($field['field_name'] ?? ''))
            ? $this->minuteFormat($field)
            : ($field['date_time_format'] ?? 'default');
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function minuteFormat(array $field): string
    {
        if (isset($field['minute_format'])) {
            return (string) $field['minute_format'];
        }

        return in_array($field['date_time_format'] ?? 'default', ['H:i', 'h:i A'], true)
            ? 'hh_mm'
            : 'default';
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, array<string, array<string, string>>>  $attendanceValues
     * @return array{minute_totals: array<string, string>, status_counts: array<string, int|float>}
     */
    private function summaryData(array $fields, array $attendanceValues): array
    {
        $summaryFields = array_values(array_filter(
            $fields,
            static fn (array $field): bool => filter_var($field['use_in_summary'] ?? false, FILTER_VALIDATE_BOOLEAN)
                && (CustomReportFieldOptions::supportsMinuteFormat((string) ($field['field_name'] ?? ''))
                    || in_array($field['field_name'] ?? '', ['attendance_status', 'attendance_specific_status'], true)),
        ));
        $minuteTotals = [];
        $statusSelected = array_intersect(
            ['attendance_status', 'attendance_specific_status'],
            array_column($summaryFields, 'field_name'),
        ) !== [];

        foreach ($summaryFields as $field) {
            $fieldName = (string) ($field['field_name'] ?? '');
            if (! CustomReportFieldOptions::supportsMinuteFormat($fieldName)) {
                continue;
            }

            $total = 0;
            foreach ($attendanceValues as $dailyValues) {
                foreach ($dailyValues as $values) {
                    $minutes = $values[$fieldName] ?? null;
                    if (is_numeric($minutes)) {
                        $total += (int) $minutes;
                    }
                }
            }
            $minuteTotals[$fieldName] = $this->formatMinutes($total, $this->minuteFormat($field));
        }

        $statusCounts = [];
        if ($statusSelected) {
            $statusField = in_array('attendance_specific_status', array_column($summaryFields, 'field_name'), true)
                ? 'attendance_specific_status'
                : 'attendance_status';
            $halfStatusCountAsHalf = $this->countsHalfStatusAsHalf($summaryFields);

            foreach ($summaryFields as $field) {
                if (($field['field_name'] ?? '') !== 'attendance_specific_status') {
                    continue;
                }
                foreach ($field['_selected_status_codes'] ?? [] as $statusCode) {
                    if (is_string($statusCode) && $statusCode !== '') {
                        $statusCounts[$statusCode] = 0;
                    }
                }
            }

            foreach ($attendanceValues as $dailyValues) {
                foreach ($dailyValues as $values) {
                    $status = $values[$statusField] ?? '';
                    if ($status !== '') {
                        $count = $halfStatusCountAsHalf && ($values['is_half_day'] ?? '0') === '1'
                            ? 0.5
                            : 1;
                        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + $count;
                    }
                }
            }
            $statusCounts = array_filter(
                $statusCounts,
                static fn (int|float $count): bool => $count > 0,
            );
            ksort($statusCounts);
        }

        return [
            'minute_totals' => $minuteTotals,
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, array<string, array<string, string>>>  $attendanceValues
     * @return array<int, string>
     */
    private function summaryStatusCodes(array $fields, array $attendanceValues): array
    {
        return array_keys($this->summaryData($fields, $attendanceValues)['status_counts']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, string>  $statusCodes
     * @return array<int, string>
     */
    private function horizontalSummaryHeaders(array $fields, array $statusCodes): array
    {
        $summary = $this->summaryData($fields, []);
        $headers = array_map(
            static fn (string $fieldName): string => 'Summary - '.CustomReportFieldOptions::all()[$fieldName],
            array_keys($summary['minute_totals']),
        );

        foreach ($statusCodes as $statusCode) {
            $headers[] = 'Summary - Attendance Status '.$statusCode.' Count';
        }
        if ($statusCodes !== []) {
            $headers[] = 'Summary - Attendance Status Grand Total Count';
        }

        return $headers;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, array<string, string>>  $employeeAttendanceValues
     * @param  array<int, string>  $statusCodes
     * @return array<int, string>
     */
    private function horizontalSummaryValues(array $fields, array $employeeAttendanceValues, array $statusCodes): array
    {
        $summary = $this->summaryData($fields, [0 => $employeeAttendanceValues]);
        $values = array_values($summary['minute_totals']);
        foreach ($statusCodes as $statusCode) {
            $count = $summary['status_counts'][$statusCode] ?? 0;
            $values[] = $count > 0 ? (string) $count : '';
        }
        if ($statusCodes !== []) {
            $values[] = (string) array_sum($summary['status_counts']);
        }

        return $values;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, array<string, array<string, string>>>  $attendanceValues
     * @param  array<int, array<int, string>>  $rows
     */
    private function appendSummaryRows(array &$rows, array $fields, array $attendanceValues, string $orientation): void
    {
        $summary = $this->summaryData($fields, $attendanceValues);
        if ($summary['minute_totals'] === [] && $summary['status_counts'] === []) {
            return;
        }

        $rows[] = [];
        $rows[] = ['Summary'];
        if ($orientation === 'horizontal') {
            if ($summary['minute_totals'] !== []) {
                $rows[] = array_map(
                    static fn (string $fieldName): string => CustomReportFieldOptions::all()[$fieldName],
                    array_keys($summary['minute_totals']),
                );
                $rows[] = array_values($summary['minute_totals']);
            }
            if ($summary['status_counts'] !== []) {
                $rows[] = ['Attendance Status'];
                $rows[] = array_merge(['Status'], array_keys($summary['status_counts']), ['Grand Total']);
                $rows[] = array_merge(
                    ['Count'],
                    array_map('strval', array_values($summary['status_counts'])),
                    [(string) array_sum($summary['status_counts'])],
                );
            }

            return;
        }

        foreach ($summary['minute_totals'] as $fieldName => $total) {
            $rows[] = [CustomReportFieldOptions::all()[$fieldName], $total];
        }
        if ($summary['status_counts'] !== []) {
            $rows[] = ['Attendance Status'];
            $rows[] = ['Status', 'Count'];
            foreach ($summary['status_counts'] as $status => $count) {
                $rows[] = [(string) $status, (string) $count];
            }
            $rows[] = ['Grand Total', (string) array_sum($summary['status_counts'])];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, int>
     */
    private function selectedStatusMasterIds(array $fields): array
    {
        $ids = [];
        foreach ($fields as $field) {
            if (! CustomReportFieldOptions::supportsStatusSelection((string) ($field['field_name'] ?? ''))) {
                continue;
            }

            $selectedIds = $field['status_master_ids'] ?? [];
            if (! is_array($selectedIds)) {
                continue;
            }

            foreach ($selectedIds as $id) {
                if (is_numeric($id) && (int) $id > 0) {
                    $ids[] = (int) $id;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function countsHalfStatusAsHalf(array $fields): bool
    {
        foreach ($fields as $field) {
            if (in_array($field['field_name'] ?? '', ['attendance_status', 'attendance_specific_status'], true)
                && filter_var($field['use_in_summary'] ?? false, FILTER_VALIDATE_BOOLEAN)
                && filter_var($field['half_status_count_as_half'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $fields
     * @param  Collection<int, User>  $employees
     * @return array{assignments: Collection<int, MasterSnapshot>, values: Collection<int, MasterSnapshot>, approvers: Collection<int, MasterSnapshot>}
     */
    private function snapshots(array $fields, Collection $employees): array
    {
        if ($fields === []) {
            return ['assignments' => collect(), 'values' => collect(), 'approvers' => collect()];
        }

        $snapshotFields = array_intersect($fields, array_keys(self::SNAPSHOT_FIELDS));
        $models = array_values(array_unique(array_map(
            static fn (string $field): string => self::SNAPSHOT_FIELDS[$field]['model'],
            $snapshotFields,
        )));
        $employeeIds = $employees->pluck('id')->filter()->map(static fn ($id): int => (int) $id)->all();
        $assignments = $models === [] ? collect() : MasterSnapshot::query()
            ->whereIn('master_type', $models)
            ->where('for_all', false)
            ->whereIn('user_id', $employeeIds)
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();

        $masterIds = $assignments->pluck('master_id')->map(static fn ($id): int => (int) $id)->all();
        foreach ($snapshotFields as $field) {
            $column = self::SNAPSHOT_FIELDS[$field]['column'];
            if ($column === null) {
                foreach ($employees as $employee) {
                    foreach ($this->roleIds($employee) as $id) {
                        $masterIds[] = $id;
                    }
                }

                continue;
            }

            foreach ($employees as $employee) {
                $masterIds = array_merge($masterIds, $this->attributeIds($employee->getAttribute($column)));
            }
        }
        $masterIds = array_values(array_unique($masterIds));

        $values = $masterIds === [] || $models === [] ? collect() : MasterSnapshot::query()
            ->whereIn('master_type', $models)
            ->whereIn('master_id', $masterIds)
            ->where('for_all', true)
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();

        $approvers = in_array('approvers', $fields, true) && $employeeIds !== []
            ? MasterSnapshot::query()
                ->where('master_type', MasterSnapshot::APPROVERS_SNAPSHOT_TYPE)
                ->whereIn('master_id', $employeeIds)
                ->whereIn('user_id', $employeeIds)
                ->where('for_all', false)
                ->orderBy('from_date')
                ->orderBy('id')
                ->get()
            : collect();

        return ['assignments' => $assignments, 'values' => $values, 'approvers' => $approvers];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  Collection<int, User>  $employees
     * @param  Collection<int, MasterSnapshot>  $assignmentSnapshots
     * @return array<string, array<int, string>>
     */
    private function liveMasterValues(array $fields, Collection $employees, Collection $assignmentSnapshots): array
    {
        $relations = [
            'blood_group' => ['column' => 'blood_group_id', 'model' => BloodGroup::class, 'value' => 'name'],
        ];
        $models = [];
        foreach ($fields as $field) {
            $fieldName = (string) ($field['field_name'] ?? '');
            $definition = self::SNAPSHOT_FIELDS[$fieldName] ?? $relations[$fieldName] ?? null;
            if ($definition === null) {
                continue;
            }

            $model = $definition['model'];
            $column = $definition['column'] ?? null;
            $ids = [];
            foreach ($employees as $employee) {
                $ids = array_merge(
                    $ids,
                    $column === null
                        ? $this->roleIds($employee)
                        : $this->attributeIds($employee->getAttribute($column)),
                );
            }
            $ids = array_merge(
                $ids,
                $assignmentSnapshots
                    ->where('master_type', $model)
                    ->pluck('master_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->all(),
            );
            $models[$model]['ids'] = array_merge($models[$model]['ids'] ?? [], $ids);
            $models[$model]['fields'][$fieldName] = $definition['value'];
        }

        $values = [];
        foreach ($models as $model => $modelDefinition) {
            $ids = array_values(array_unique($modelDefinition['ids']));
            if ($ids === []) {
                continue;
            }

            $columns = array_values(array_unique(array_merge(['id'], array_values($modelDefinition['fields']))));
            foreach ($model::query()->whereIn('id', $ids)->get($columns) as $record) {
                foreach ($modelDefinition['fields'] as $fieldName => $valueColumn) {
                    $value = $record->getAttribute($valueColumn);
                    if (is_scalar($value) && (string) $value !== '') {
                        $values[$fieldName][(int) $record->getKey()] = (string) $value;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  Collection<int, User>  $employees
     * @param  array<int, array<int, array{label: string, sub_sequences: array<int, array<int, array{machine_id: int, machine_name: string, dms_device_id: int|null, access_direction: string|null}>>}>>  $machineInOutSequences
     * @return array<int, array<string, array<string, mixed>>>
     */
    private function attendanceValues(
        array $fields,
        Collection $employees,
        string $fromDate,
        string $toDate,
        array $machineInOutSequences,
    ): array {
        $attendanceColumns = [
            'attendance_in_time' => 'in_time',
            'attendance_out_time' => 'out_time',
            'late_minutes' => 'late_minutes',
            'early_minutes' => 'early_minutes',
            'working_hrs' => 'work_time',
            'overtime_minutes' => 'total_overtime_minutes',
            'approved_overtime_minutes' => 'approved_overtime_minutes',
        ];
        $requestedFields = array_values(array_intersect(
            array_column($fields, 'field_name'),
            array_merge(array_keys($attendanceColumns), ['attendance_specific_status', 'attendance_status', 'shift_name', 'shift_code', 'machine_in_out']),
        ));
        $requestedAttendanceFields = array_values(array_diff($requestedFields, ['machine_in_out']));
        $statusMasterFilterIds = $this->selectedStatusMasterIds($fields);
        $halfStatusCountAsHalf = $this->countsHalfStatusAsHalf($fields);
        $employeeIds = $employees->pluck('id')->filter()->map(static fn (mixed $id): int => (int) $id)->all();
        if ($employeeIds === [] || ($requestedAttendanceFields === [] && $machineInOutSequences === [])) {
            return [];
        }

        $values = [];
        $attendanceRows = [];
        $shiftIds = [];
        $statusMasterIds = [];
        $fieldFormats = [];
        foreach ($fields as $field) {
            $fieldFormats[$field['field_name'] ?? ''] = $this->fieldFormat($field);
        }
        $columns = ['user_id', 'date'];
        if ($halfStatusCountAsHalf) {
            $columns[] = 'is_half_day';
        }
        foreach ($requestedFields as $field) {
            if (isset($attendanceColumns[$field])) {
                $columns[] = $attendanceColumns[$field];
            }
            if ($field === 'shift_name' || $field === 'shift_code') {
                $columns[] = 'shift_id';
            }
            if ($field === 'attendance_status' || $field === 'attendance_specific_status') {
                $columns[] = 'status_master_id';
            }
            if ($field === 'late_minutes') {
                $columns[] = 'is_late';
            }
            if ($field === 'early_minutes') {
                $columns[] = 'is_early';
            }
        }
        $columns = array_values(array_unique($columns));
        $firstYear = (int) (new DateTimeImmutable($fromDate))->format('Y');
        $lastYear = (int) (new DateTimeImmutable($toDate))->format('Y');

        for ($year = $firstYear; $requestedAttendanceFields !== [] && $year <= $lastYear; $year++) {
            $yearStart = max($fromDate, $year.'-01-01');
            $yearEnd = min($toDate, $year.'-12-31');
            $attendanceQuery = Attendance::year($year)
                ->whereIn('user_id', $employeeIds)
                ->whereBetween('date', [$yearStart, $yearEnd]);
            $attendances = $attendanceQuery->get($columns);

            foreach ($attendances as $attendance) {
                $attendanceRows[] = $attendance;
                if (in_array('shift_id', $columns, true) && $attendance->shift_id !== null) {
                    $shiftIds[] = (int) $attendance->shift_id;
                }
                if (in_array('status_master_id', $columns, true) && $attendance->status_master_id !== null) {
                    $statusMasterIds[] = (int) $attendance->status_master_id;
                }
            }
        }

        $shiftIds = array_values(array_unique($shiftIds));
        $statusMasterIds = array_values(array_unique($statusMasterIds));
        $shiftSnapshots = in_array('shift_id', $columns, true) && $shiftIds !== []
            ? MasterSnapshot::query()
                ->where('master_type', Shift::class)
                ->where('for_all', true)
                ->whereNull('user_id')
                ->whereIn('master_id', $shiftIds)
                ->whereDate('from_date', '<=', $toDate)
                ->where(static function ($query) use ($fromDate): void {
                    $query->whereNull('to_date')
                        ->orWhereDate('to_date', '>', $fromDate);
                })
                ->orderBy('from_date')
                ->orderBy('id')
                ->get()
            : collect();
        $attendanceStatuses = in_array('status_master_id', $columns, true) && $statusMasterIds !== []
            ? StatusMaster::query()->whereIn('id', $statusMasterIds)->pluck('code', 'id')->all()
            : [];

        foreach ($attendanceRows as $attendance) {
            $date = $attendance->date->toDateString();
            $employeeId = (int) $attendance->user_id;

            if (in_array('status_master_id', $columns, true)) {
                $statusCode = $attendanceStatuses[(int) $attendance->status_master_id] ?? null;
                if (is_scalar($statusCode) && (string) $statusCode !== '') {
                    if (in_array('attendance_status', $requestedFields, true)) {
                        $values[$employeeId][$date]['attendance_status'] = (string) $statusCode;
                    }
                    if (in_array('attendance_specific_status', $requestedFields, true)
                        && ($statusMasterFilterIds === [] || in_array((int) $attendance->status_master_id, $statusMasterFilterIds, true))) {
                        $values[$employeeId][$date]['attendance_specific_status'] = (string) $statusCode;
                    }
                }
                if (in_array('is_half_day', $columns, true)) {
                    $values[$employeeId][$date]['is_half_day'] = $attendance->is_half_day ? '1' : '0';
                }
            }

            if (in_array('shift_id', $columns, true) && $attendance->shift_id !== null) {
                $snapshot = $shiftSnapshots
                    ->where('master_id', (int) $attendance->shift_id)
                    ->filter(fn (MasterSnapshot $candidate): bool => $this->coversDate(
                        $candidate,
                        new DateTimeImmutable($date),
                    ))
                    ->sortByDesc(static fn (MasterSnapshot $candidate): string => $candidate->from_date->format('Y-m-d').'-'.str_pad((string) $candidate->id, 12, '0', STR_PAD_LEFT))
                    ->first();

                foreach (['shift_name' => 'name', 'shift_code' => 'code'] as $field => $snapshotKey) {
                    if (! in_array($field, $requestedFields, true)) {
                        continue;
                    }
                    $value = $snapshot?->value[$snapshotKey] ?? null;
                    if (is_scalar($value) && (string) $value !== '') {
                        $values[$employeeId][$date][$field] = (string) $value;
                    }
                }
            }

            foreach ($attendanceColumns as $field => $column) {
                if (! in_array($field, $requestedFields, true)
                    || ($field === 'late_minutes' && ! $attendance->is_late)
                    || ($field === 'early_minutes' && ! $attendance->is_early)) {
                    continue;
                }

                $value = $attendance->getAttribute($column);
                if ($value instanceof \DateTimeInterface) {
                    $fieldFormat = $fieldFormats[$field] ?? 'default';
                    $format = $fieldFormat === 'default' ? 'H:i:s' : $this->dateFormat($fieldFormat);
                    $value = $value->format($format);
                }
                if (is_scalar($value)) {
                    $values[$employeeId][$date][$field] = (string) $value;
                }
            }
        }

        if ($machineInOutSequences !== []) {
            $values = $this->machineInOutAttendanceValues(
                $values,
                $employees,
                $machineInOutSequences,
                $fromDate,
                $toDate,
            );
        }

        return $values;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, array<int, array{label: string, sub_sequences: array<int, array<int, array{machine_id: int, machine_name: string, dms_device_id: int|null, access_direction: string|null}>>}>
     */
    private function machineInOutSequenceDefinitions(array $fields): array
    {
        $assignments = [];
        $machineIds = [];
        foreach ($fields as $fieldIndex => $field) {
            if (($field['field_name'] ?? '') !== 'machine_in_out' || ! is_array($field['machine_in_out'] ?? null)) {
                continue;
            }

            foreach ($field['machine_in_out'] as $assignment) {
                if (! is_array($assignment)) {
                    continue;
                }
                $machineId = filter_var($assignment['machine_id'] ?? null, FILTER_VALIDATE_INT);
                $sequence = filter_var($assignment['sequence'] ?? null, FILTER_VALIDATE_INT);
                $subSequence = filter_var($assignment['sub_sequence'] ?? null, FILTER_VALIDATE_INT);
                if ($machineId === false || $machineId < 1 || $sequence === false || $sequence < 1
                    || $subSequence === false || $subSequence < 1) {
                    continue;
                }

                $machineIds[] = $machineId;
                $assignments[$fieldIndex][$sequence][$subSequence][] = $machineId;
            }
        }

        $machines = $machineIds === []
            ? collect()
            : Machine::query()
                ->whereIn('id', array_values(array_unique($machineIds)))
                ->get(['id', 'name', 'dms_device_id', 'access_direction'])
                ->keyBy('id');
        $definitions = [];

        foreach ($assignments as $fieldIndex => $sequences) {
            ksort($sequences, SORT_NUMERIC);
            foreach ($sequences as $sequence => $subSequences) {
                ksort($subSequences, SORT_NUMERIC);
                $machineNames = [];
                foreach ($subSequences as $subSequence => $ids) {
                    $subSequences[$subSequence] = [];
                    foreach (array_values(array_unique($ids)) as $machineId) {
                        $machine = $machines->get($machineId);
                        $machineName = (string) ($machine?->name ?: 'Machine '.$machineId);
                        $machineNames[] = $machineName;
                        $subSequences[$subSequence][] = [
                            'machine_id' => $machineId,
                            'machine_name' => $machineName,
                            'dms_device_id' => $machine?->dms_device_id === null ? null : (int) $machine->dms_device_id,
                            'access_direction' => $machine?->access_direction === null
                                ? null
                                : strtolower((string) $machine->access_direction),
                        ];
                    }
                }
                $machineNames = array_values(array_unique($machineNames));
                $label = 'Device Seq '.$sequence;
                if ($machineNames !== []) {
                    $label .= ' ('.implode(', ', $machineNames).')';
                }
                $definitions[$fieldIndex][$sequence] = [
                    'label' => $label,
                    'sub_sequences' => $subSequences,
                ];
            }
        }

        return $definitions;
    }

    /**
     * @param  array<int, array<string, mixed>>  $values
     * @param  Collection<int, User>  $employees
     * @param  array<int, array<int, array{label: string, sub_sequences: array<int, array<int, array{machine_id: int, machine_name: string, dms_device_id: int|null, access_direction: string|null}>>}>>  $machineInOutSequences
     * @return array<int, array<string, array<string, mixed>>>
     */
    private function machineInOutAttendanceValues(
        array $values,
        Collection $employees,
        array $machineInOutSequences,
        string $fromDate,
        string $toDate,
    ): array {
        $deviceAssignments = [];
        foreach ($machineInOutSequences as $fieldIndex => $sequences) {
            foreach ($sequences as $sequence => $definition) {
                foreach ($definition['sub_sequences'] as $subSequence => $machines) {
                    foreach ($machines as $machine) {
                        if ($machine['dms_device_id'] === null) {
                            continue;
                        }
                        $deviceAssignments[$machine['dms_device_id']][] = [
                            'field_index' => $fieldIndex,
                            'sequence' => $sequence,
                            'sub_sequence' => $subSequence,
                            'access_direction' => $machine['access_direction'],
                        ];
                    }
                }
            }
        }

        if ($deviceAssignments === []) {
            return $values;
        }

        $punches = [];
        foreach (AttendanceLog::query()
            ->whereIn('user_id', $employees->pluck('id')->filter()->map(static fn (mixed $id): int => (int) $id)->all())
            ->whereIn('dms_device_id', array_keys($deviceAssignments))
            ->whereBetween('datetime', [$fromDate.' 00:00:00', $toDate.' 23:59:59'])
            ->where('is_ignored', false)
            ->orderBy('datetime')
            ->select(['user_id', 'dms_device_id', 'datetime', 'is_out'])
            ->cursor() as $punch) {
            if ($punch->datetime === null) {
                continue;
            }
            foreach ($deviceAssignments[(int) $punch->dms_device_id] ?? [] as $assignment) {
                // Both-direction devices use the punch direction resolved during attendance processing.
                $direction = (bool) $punch->is_out ? 'out' : 'in';
                $accessDirection = $assignment['access_direction'];
                if (in_array($accessDirection, ['in', '1'], true)) {
                    $direction = 'in';
                } elseif (in_array($accessDirection, ['out', '2'], true)) {
                    $direction = 'out';
                }
                $punchSlot = &$punches[(int) $punch->user_id][$punch->datetime->format('Y-m-d')][$assignment['field_index']][$assignment['sequence']][$assignment['sub_sequence']][$direction];
                if ($direction === 'out' || ! isset($punchSlot)) {
                    $punchSlot = $punch->datetime;
                }
                unset($punchSlot);
            }
        }

        foreach ($punches as $employeeId => $dailyPunches) {
            foreach ($dailyPunches as $date => $fieldPunches) {
                foreach ($fieldPunches as $fieldIndex => $sequencePunches) {
                    foreach ($sequencePunches as $sequence => $subSequencePunches) {
                        $inTime = null;
                        // Ordered sub-sequences define which configured endpoint is used for each direction.
                        foreach ($machineInOutSequences[$fieldIndex][$sequence]['sub_sequences'] ?? [] as $subSequence => $_machines) {
                            $time = $subSequencePunches[$subSequence]['in'] ?? null;
                            if ($time !== null) {
                                $inTime = $time;
                                break;
                            }
                        }

                        $outTime = null;
                        foreach (array_reverse($machineInOutSequences[$fieldIndex][$sequence]['sub_sequences'] ?? [], true) as $subSequence => $_machines) {
                            $time = $subSequencePunches[$subSequence]['out'] ?? null;
                            if ($time !== null) {
                                $outTime = $time;
                                break;
                            }
                        }

                        $values[$employeeId][$date]['machine_in_out'][$fieldIndex][$sequence]
                            = $this->formatMachineInOutPair($inTime, $outTime);
                    }
                }
            }
        }

        return $values;
    }

    private function formatMachineInOutPair(?\DateTimeInterface $inTime, ?\DateTimeInterface $outTime): string
    {
        $in = $inTime?->format('h:i A') ?? '';
        $out = $outTime?->format('h:i A') ?? '';
        if ($in === '' && $out === '') {
            return '';
        }
        if ($in === '' || $out === '') {
            return $in.' - '.$out;
        }

        $duration = $outTime->getTimestamp() >= $inTime->getTimestamp()
            ? ' ('.$this->formatMinutes((int) floor(($outTime->getTimestamp() - $inTime->getTimestamp()) / 60), 'hh_mm').')'
            : '';

        return $in.' - '.$out.$duration;
    }

    /**
     * @param  array{assignments: Collection<int, MasterSnapshot>, values: Collection<int, MasterSnapshot>, approvers: Collection<int, MasterSnapshot>}  $snapshots
     * @param  array<string, array<int, string>>  $liveMasterValues
     * @param  array<int, array<string, array<string, string>>>  $attendanceValues
     */
    private function employeeValue(
        User $employee,
        string $field,
        array $snapshots,
        array $liveMasterValues,
        array $attendanceValues,
        mixed $format,
        ?DateTimeImmutable $date,
    ): string {
        if ($field === 'approvers') {
            $employeeId = $employee->getKey();
            if ($employeeId === null) {
                return '';
            }

            $dateToCheck = $date ?? new DateTimeImmutable('today');
            $snapshot = $snapshots['approvers']
                ->where('user_id', $employeeId)
                ->where('master_id', $employeeId)
                ->filter(fn (MasterSnapshot $candidate): bool => $this->coversDate($candidate, $dateToCheck))
                ->sortByDesc(static fn (MasterSnapshot $candidate): string => $candidate->from_date->format('Y-m-d').'-'.str_pad((string) $candidate->id, 12, '0', STR_PAD_LEFT))
                ->first();
            $approvalFlows = $snapshot?->value['approval_flows'] ?? null;
            if (! is_array($approvalFlows)) {
                $legacyApprovers = $snapshot?->value['approvers'] ?? [];

                return is_array($legacyApprovers)
                    ? implode(', ', array_filter(array_map(static fn (mixed $name): string => is_scalar($name) ? (string) $name : '', $legacyApprovers)))
                    : '';
            }

            $flowDescriptions = [];
            foreach ($approvalFlows as $approvalFlow) {
                if (! is_array($approvalFlow)) {
                    continue;
                }

                $levels = [];
                foreach ($approvalFlow['levels'] ?? [] as $level) {
                    if (! is_array($level)) {
                        continue;
                    }

                    $approvers = [];
                    foreach ($level['approvers'] ?? [] as $approver) {
                        if (! is_array($approver)) {
                            continue;
                        }

                        $name = trim((string) ($approver['name'] ?? ''));
                        $code = trim((string) ($approver['code'] ?? ''));
                        $identity = $name !== '' ? $name : $code;
                        if ($identity === '') {
                            $identity = (string) ($approver['id'] ?? '');
                        } elseif ($code !== '' && $code !== $name) {
                            $identity .= ' ('.$code.')';
                        }
                        if ($identity !== '') {
                            $approvers[] = $identity;
                        }
                    }

                    $levels[] = 'Level '.(int) ($level['level'] ?? 0).': '.($approvers === [] ? '—' : implode(', ', $approvers));
                }

                $module = trim((string) ($approvalFlow['module'] ?? 'Approval Flow'));
                $flowCode = trim((string) ($approvalFlow['flow_code'] ?? ''));
                $flowLabel = $module.($flowCode !== '' ? ' ('.$flowCode.')' : '');
                $flowDescriptions[] = $flowLabel.($levels === [] ? '' : ' - '.implode('; ', $levels));
            }

            return implode(' | ', $flowDescriptions);
        }

        if (in_array($field, [
            'shift_name',
            'shift_code',
            'attendance_in_time',
            'attendance_out_time',
            'attendance_specific_status',
            'attendance_status',
            'late_minutes',
            'early_minutes',
            'working_hrs',
            'overtime_minutes',
            'approved_overtime_minutes',
        ], true)) {
            $employeeValues = $attendanceValues[(int) $employee->getKey()] ?? [];
            $formatValue = fn (string $value): string => CustomReportFieldOptions::supportsMinuteFormat($field)
                ? $this->formatMinutes((int) $value, $format)
                : $value;
            if ($date !== null) {
                $value = $employeeValues[$date->format('Y-m-d')][$field] ?? '';
                if ($value === '' && in_array($field, ['late_minutes', 'early_minutes'], true)) {
                    return $this->formatMinutes(0, $format);
                }

                return $value === '' ? '' : $formatValue($value);
            }

            return implode(', ', array_values(array_unique(array_filter(array_map(
                fn (array $dailyValues): string => isset($dailyValues[$field])
                    ? $formatValue($dailyValues[$field])
                    : '',
                $employeeValues,
            )))));
        }

        $columns = [
            'emp_code' => 'code',
            'emp_card' => 'card',
            'name' => 'name',
            'first_name' => 'fname',
            'middle_name' => 'mname',
            'last_name' => 'lname',
            'joining_date' => 'join_date',
            'left_date' => 'left_date',
            'left_remark' => 'left_reason',
            'last_login' => 'last_login_at',
            'aadhar_card' => 'aadhar_number',
            'uan_no' => 'uan_number',
            'esic_no' => 'esic_number',
            'pan_no' => 'pan_number',
            'status' => 'status',
            'birth_date' => 'dob',
            'gender' => 'gender',
            'phone' => 'number',
            'email' => 'email',
        ];

        if (isset(self::SNAPSHOT_FIELDS[$field])) {
            return $this->snapshotValue($employee, $field, $snapshots, $liveMasterValues, $date);
        }

        if (isset($liveMasterValues[$field])) {
            return implode(', ', array_filter(array_map(
                static fn (int $id): string => $liveMasterValues[$field][$id] ?? '',
                $this->attributeIds($employee->getAttribute('blood_group_id')),
            )));
        }

        if ($field === 'height' || $field === 'width') {
            $personalInfo = $employee->relationLoaded('personalInfo') ? $employee->getRelation('personalInfo') : null;
            $value = $personalInfo?->getAttribute($field === 'height' ? 'height' : 'weight');

            return is_scalar($value) ? (string) $value : '';
        }

        if ($field === 'address' || $field === 'current_address') {
            $addresses = $employee->relationLoaded('addresses') ? $employee->getRelation('addresses') : collect();
            $address = $addresses->firstWhere('is_current_address', true) ?? $addresses->first();
            if ($address === null) {
                return '';
            }
            $prefix = $field === 'current_address' ? 'current_' : '';

            return implode(', ', array_filter(array_map(
                static fn (string $part): string => (string) ($address->getAttribute($prefix.$part) ?? ''),
                ['flat_building', 'house_no', 'flore', 'street', 'landmark', 'city', 'state', 'country', 'pincode'],
            )));
        }

        if (in_array($field, ['emergency_contact_name', 'emergency_contact_number', 'emergency_contact_address'], true)) {
            $personalInfo = $employee->relationLoaded('personalInfo') ? $employee->getRelation('personalInfo') : null;
            $attribute = [
                'emergency_contact_name' => 'emergency_name',
                'emergency_contact_number' => 'emergency_number',
                'emergency_contact_address' => 'emergency_address',
            ][$field];
            $value = $personalInfo?->getAttribute($attribute);

            return is_scalar($value) ? (string) $value : '';
        }

        if (! isset($columns[$field])) {
            return '';
        }

        $value = $employee->getAttribute($columns[$field]);
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->dateFormat($format));
        }

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param  array{assignments: Collection<int, MasterSnapshot>, values: Collection<int, MasterSnapshot>, approvers: Collection<int, MasterSnapshot>}  $snapshots
     * @param  array<string, array<int, string>>  $liveMasterValues
     */
    private function snapshotValue(
        User $employee,
        string $field,
        array $snapshots,
        array $liveMasterValues,
        ?DateTimeImmutable $date,
    ): string {
        $definition = self::SNAPSHOT_FIELDS[$field];
        $model = $definition['model'];
        $valueKey = $definition['value'];
        $assignedIds = $definition['column'] === null
            ? $this->roleIds($employee)
            : $this->attributeIds($employee->getAttribute($definition['column']));

        if ($date !== null && $employee->getKey() !== null) {
            $assignmentHistory = $snapshots['assignments']
                ->where('master_type', $model)
                ->where('user_id', $employee->getKey());

            if ($assignmentHistory->isNotEmpty()) {
                $effectiveAssignments = $assignmentHistory
                    ->filter(fn (MasterSnapshot $snapshot): bool => $this->coversDate($snapshot, $date))
                    ->unique('master_id');
                $assignedIds = $effectiveAssignments->pluck('master_id')->map(static fn ($id): int => (int) $id)->all();
            }
        }

        $dateString = ($date ?? new DateTimeImmutable('today'))->format('Y-m-d');
        $result = [];
        foreach ($assignedIds as $id) {
            $snapshot = $snapshots['values']
                ->where('master_type', $model)
                ->where('master_id', $id)
                ->filter(fn (MasterSnapshot $candidate): bool => $this->coversDate($candidate, new DateTimeImmutable($dateString)))
                ->sortByDesc(static fn (MasterSnapshot $candidate): string => $candidate->from_date->format('Y-m-d').'-'.str_pad((string) $candidate->id, 12, '0', STR_PAD_LEFT))
                ->first();

            $value = $snapshot?->value[$valueKey] ?? null;
            if (! is_scalar($value) || trim((string) $value) === '') {
                $value = $liveMasterValues[$field][$id] ?? null;
            }
            if ($field === 'leave_group_name' && ($value === null || $value === '')) {
                $value = $snapshot?->value['code'] ?? null;
            }
            if (is_scalar($value) && (string) $value !== '') {
                $result[] = (string) $value;
            }
        }

        return implode(', ', array_values(array_unique($result)));
    }

    private function coversDate(MasterSnapshot $snapshot, DateTimeImmutable $date): bool
    {
        $day = $date->format('Y-m-d');

        return $snapshot->from_date->format('Y-m-d') <= $day
            && ($snapshot->to_date === null || $snapshot->to_date->format('Y-m-d') > $day);
    }

    /**
     * @return array<int, int>
     */
    private function roleIds(User $employee): array
    {
        if (! $employee->relationLoaded('roles')) {
            return [];
        }

        return $employee->getRelation('roles')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function attributeIds(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            }
        }

        return array_values(array_filter(
            array_map(static fn (mixed $id): int => is_numeric($id) ? (int) $id : 0, (array) $value),
            static fn (int $id): bool => $id > 0,
        ));
    }

    private function dateFormat(mixed $format): string
    {
        return in_array($format, ['d-m-Y', 'm-d-Y', 'Y-m-d', 'd/m/Y', 'm/d/Y', 'H:i', 'h:i A', 'd-m-Y H:i', 'Y-m-d H:i'], true)
            ? $format
            : 'Y-m-d';
    }

    private function formatMinutes(int $minutes, mixed $format): string
    {
        return match ($format) {
            'hh_mm' => ($minutes < 0 ? '-' : '').sprintf('%02d:%02d', intdiv(abs($minutes), 60), abs($minutes) % 60),
            'decimal' => number_format($minutes / 60, 2, '.', ''),
            default => (string) $minutes,
        };
    }
}
