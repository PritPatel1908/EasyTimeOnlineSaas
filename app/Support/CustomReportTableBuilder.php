<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\LegacyLeaveGroup;
use App\Models\Tenant\Location;
use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\Role;
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
    ): array {
        $employees = collect($employees)->values();
        $fieldLabels = CustomReportFieldOptions::all();
        $fields = array_values(array_filter($fields, 'is_array'));
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
        if ($dateIndexes !== []) {
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
        $liveMasterValues = $this->liveMasterValues($fields, $employees);
        $attendanceValues = $this->attendanceShiftValues($fields, $employees, $fromDate, $toDate);
        $rows = [];

        if ($dateIndexes !== [] && $orientation === 'horizontal') {
            $headers = [];
            foreach ($fields as $index => $field) {
                if (($field['field_name'] ?? '') !== 'date') {
                    $headers[] = $labels[$index];
                }
            }
            $dateFormat = $this->dateFormat($fields[$dateIndexes[0]]['date_time_format'] ?? 'default');
            foreach ($dates as $date) {
                $headers[] = $date->format($dateFormat);
            }

            foreach ($employees as $employee) {
                $row = [];
                foreach ($fields as $field) {
                    if (($field['field_name'] ?? '') !== 'date') {
                        $row[] = $this->employeeValue(
                            $employee,
                            $field['field_name'] ?? '',
                            $snapshots,
                            $liveMasterValues,
                            $attendanceValues,
                            $field['date_time_format'] ?? 'default',
                            null,
                        );
                    }
                }
                $rows[] = array_merge($row, array_fill(0, count($dates), ''));
            }

            return ['headers' => $headers, 'rows' => $rows];
        }

        if ($dateIndexes !== []) {
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
            $detailHeaders = array_map(static fn (int $index): string => $labels[$index], $detailIndexes);
            $startDate = new DateTimeImmutable($fromDate);

            foreach ($employees as $employeeIndex => $employee) {
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
                        $field['date_time_format'] ?? 'default',
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
                $rows[] = $detailHeaders;

                foreach ($dates as $date) {
                    $row = [];
                    foreach ($detailIndexes as $index) {
                        $field = $fields[$index];
                        $fieldName = $field['field_name'] ?? '';
                        if ($fieldName === 'date') {
                            $row[] = $date->format($this->dateFormat($field['date_time_format'] ?? 'default'));

                            continue;
                        }

                        $row[] = $this->employeeValue(
                            $employee,
                            $fieldName,
                            $snapshots,
                            $liveMasterValues,
                            $attendanceValues,
                            $field['date_time_format'] ?? 'default',
                            $date,
                        );
                    }
                    $rows[] = $row;
                }
            }

            return ['headers' => $headers, 'rows' => $rows];
        }

        $headers = $labels;
        foreach ($employees as $employee) {
            $rows[] = array_map(
                fn (array $field): string => $this->employeeValue(
                    $employee,
                    $field['field_name'] ?? '',
                    $snapshots,
                    $liveMasterValues,
                    $attendanceValues,
                    $field['date_time_format'] ?? 'default',
                    null,
                ),
                $fields,
            );
        }

        return ['headers' => $headers, 'rows' => $rows];
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
     * @return array<string, array<int, string>>
     */
    private function liveMasterValues(array $fields, Collection $employees): array
    {
        $relations = [
            'blood_group' => ['column' => 'blood_group_id', 'model' => BloodGroup::class, 'value' => 'name'],
        ];
        $values = [];

        foreach ($fields as $field) {
            $name = $field['field_name'] ?? '';
            if (! isset($relations[$name])) {
                continue;
            }

            $relation = $relations[$name];
            $ids = [];
            foreach ($employees as $employee) {
                $ids = array_merge($ids, $this->attributeIds($employee->getAttribute($relation['column'])));
            }
            $values[$name] = $relation['model']::query()
                ->whereIn('id', array_values(array_unique($ids)))
                ->get(['id', $relation['value']])
                ->mapWithKeys(static fn ($record): array => [(int) $record->id => (string) ($record->{$relation['value']} ?? '')])
                ->all();
        }

        return $values;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  Collection<int, User>  $employees
     * @return array<int, array<string, array<string, string>>>
     */
    private function attendanceShiftValues(array $fields, Collection $employees, string $fromDate, string $toDate): array
    {
        $shiftFields = array_intersect(
            array_column($fields, 'field_name'),
            ['shift_name', 'shift_code'],
        );
        $employeeIds = $employees->pluck('id')->filter()->map(static fn (mixed $id): int => (int) $id)->all();
        if ($shiftFields === [] || $employeeIds === []) {
            return [];
        }

        $values = [];
        $firstYear = (int) (new DateTimeImmutable($fromDate))->format('Y');
        $lastYear = (int) (new DateTimeImmutable($toDate))->format('Y');

        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $yearStart = max($fromDate, $year.'-01-01');
            $yearEnd = min($toDate, $year.'-12-31');
            $attendances = Attendance::year($year)
                ->with('shift')
                ->whereIn('user_id', $employeeIds)
                ->whereBetween('date', [$yearStart, $yearEnd])
                ->get();

            foreach ($attendances as $attendance) {
                $employeeId = (int) $attendance->user_id;
                $date = $attendance->date->toDateString();
                $shiftName = (string) ($attendance->shift?->name ?? '');
                $shiftCode = (string) (($attendance->shift_code ?: $attendance->shift?->code) ?? '');

                if ($shiftName !== '') {
                    $values[$employeeId][$date]['shift_name'] = $shiftName;
                }
                if ($shiftCode !== '') {
                    $values[$employeeId][$date]['shift_code'] = $shiftCode;
                }
            }
        }

        return $values;
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

        if (in_array($field, ['shift_name', 'shift_code'], true)) {
            $employeeValues = $attendanceValues[(int) $employee->getKey()] ?? [];
            if ($date !== null) {
                return $employeeValues[$date->format('Y-m-d')][$field] ?? '';
            }

            return implode(', ', array_values(array_unique(array_filter(array_map(
                static fn (array $dailyValues): string => $dailyValues[$field] ?? '',
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
            return $this->snapshotValue($employee, $field, $snapshots, $date);
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
     */
    private function snapshotValue(User $employee, string $field, array $snapshots, ?DateTimeImmutable $date): string
    {
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
}
