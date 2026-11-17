<?php

declare(strict_types=1);

namespace App\Support;

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
use App\Models\Tenant\Shift;
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

        $snapshotFields = array_intersect(array_column($fields, 'field_name'), array_keys(self::SNAPSHOT_FIELDS));
        $snapshots = $this->snapshots($snapshotFields, $employees);
        $liveMasterValues = $this->liveMasterValues($fields, $employees);
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
                            $field['date_time_format'] ?? 'default',
                            null,
                        );
                    }
                }
                $rows[] = array_merge($row, array_fill(0, count($dates), ''));
            }

            return ['headers' => $headers, 'rows' => $rows];
        }

        $headers = $labels;
        foreach ($employees as $employee) {
            if ($dateIndexes === []) {
                $rows[] = array_map(
                    fn (array $field): string => $this->employeeValue(
                        $employee,
                        $field['field_name'] ?? '',
                        $snapshots,
                        $liveMasterValues,
                        $field['date_time_format'] ?? 'default',
                        null,
                    ),
                    $fields,
                );

                continue;
            }

            $employeeRows = [];
            foreach ($dates as $date) {
                $row = [];
                foreach ($fields as $field) {
                    $row[] = ($field['field_name'] ?? '') === 'date'
                        ? $date->format($this->dateFormat($field['date_time_format'] ?? 'default'))
                        : $this->employeeValue(
                            $employee,
                            $field['field_name'] ?? '',
                            $snapshots,
                            $liveMasterValues,
                            $field['date_time_format'] ?? 'default',
                            $date,
                        );
                }
                $employeeRows[] = $row;
            }
            array_push(
                $rows,
                ...$this->compressDateRows(
                    $employeeRows,
                    $dateIndexes,
                    $this->dateFormat($fields[$dateIndexes[0]]['date_time_format'] ?? 'default'),
                ),
            );
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<int, string>  $fields
     * @param  Collection<int, User>  $employees
     * @return array{assignments: Collection<int, MasterSnapshot>, values: Collection<int, MasterSnapshot>}
     */
    private function snapshots(array $fields, Collection $employees): array
    {
        if ($fields === []) {
            return ['assignments' => collect(), 'values' => collect()];
        }

        $models = array_values(array_unique(array_map(
            static fn (string $field): string => self::SNAPSHOT_FIELDS[$field]['model'],
            $fields,
        )));
        $employeeIds = $employees->pluck('id')->filter()->map(static fn ($id): int => (int) $id)->all();
        $assignments = MasterSnapshot::query()
            ->whereIn('master_type', $models)
            ->where('for_all', false)
            ->whereIn('user_id', $employeeIds)
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();

        $masterIds = $assignments->pluck('master_id')->map(static fn ($id): int => (int) $id)->all();
        foreach ($fields as $field) {
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

        $values = $masterIds === [] ? collect() : MasterSnapshot::query()
            ->whereIn('master_type', $models)
            ->whereIn('master_id', $masterIds)
            ->where('for_all', true)
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();

        return ['assignments' => $assignments, 'values' => $values];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  Collection<int, User>  $employees
     * @return array<string, array<int, string>>
     */
    private function liveMasterValues(array $fields, Collection $employees): array
    {
        $relations = [
            'shift_name' => ['column' => 'shift_id', 'model' => Shift::class, 'value' => 'name'],
            'shift_code' => ['column' => 'shift_id', 'model' => Shift::class, 'value' => 'code'],
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
     * @param  array{assignments: Collection<int, MasterSnapshot>, values: Collection<int, MasterSnapshot>}  $snapshots
     * @param  array<string, array<int, string>>  $liveMasterValues
     */
    private function employeeValue(
        User $employee,
        string $field,
        array $snapshots,
        array $liveMasterValues,
        mixed $format,
        ?DateTimeImmutable $date,
    ): string {
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
            $column = [
                'shift_name' => 'shift_id',
                'shift_code' => 'shift_id',
                'blood_group' => 'blood_group_id',
            ][$field];

            return implode(', ', array_filter(array_map(
                static fn (int $id): string => $liveMasterValues[$field][$id] ?? '',
                $this->attributeIds($employee->getAttribute($column)),
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
     * @param  array{assignments: Collection<int, MasterSnapshot>, values: Collection<int, MasterSnapshot>}  $snapshots
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
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, int>  $dateIndexes
     * @return array<int, array<int, string>>
     */
    private function compressDateRows(array $rows, array $dateIndexes, string $dateFormat): array
    {
        $distinctRows = [];
        foreach ($rows as $row) {
            foreach ($dateIndexes as $index) {
                unset($row[$index]);
            }
            $distinctRows[serialize(array_values($row))] = true;
        }
        if (count($distinctRows) < 2) {
            return $rows;
        }

        $compressed = [];
        foreach ($rows as $row) {
            $dateIndex = $dateIndexes[0];
            $date = DateTimeImmutable::createFromFormat('!'.$dateFormat, $row[$dateIndex]);
            if ($date === false) {
                throw new \RuntimeException('Unable to parse the custom report date.');
            }
            $comparison = $row;
            foreach ($dateIndexes as $index) {
                unset($comparison[$index]);
            }
            $comparison = array_values($comparison);

            $lastIndex = array_key_last($compressed);
            if ($lastIndex !== null && $compressed[$lastIndex]['values'] === $comparison) {
                $compressed[$lastIndex]['end'] = $date;

                continue;
            }

            $compressed[] = [
                'row' => $row,
                'values' => $comparison,
                'start' => $date,
                'end' => $date,
            ];
        }

        return array_map(function (array $period) use ($dateIndexes, $dateFormat): array {
            $range = $period['start']->format($dateFormat);
            if ($period['start'] != $period['end']) {
                $range .= ' - '.$period['end']->format($dateFormat);
            }
            foreach ($dateIndexes as $index) {
                $period['row'][$index] = $range;
            }

            return $period['row'];
        }, $compressed);
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
        return in_array($format, ['d-m-Y', 'm-d-Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'], true)
            ? $format
            : 'Y-m-d';
    }
}
