<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\LegacyLeaveGroup;
use App\Models\Tenant\Location;
use App\Models\Tenant\Shift;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

final class CustomReportTableBuilder
{
    /**
     * @param array<int, array<string, mixed>> $fields
     * @param iterable<User> $employees
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function buildForEmployees(
        array $fields,
        string $fromDate,
        string $toDate,
        string $orientation,
        iterable $employees,
    ): array {
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

        $masterValues = $this->masterValues($fields, $employees);
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
                            $masterValues,
                            $field['date_time_format'] ?? 'default',
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
                        $masterValues,
                        $field['date_time_format'] ?? 'default',
                    ),
                    $fields,
                );
                continue;
            }

            foreach ($dates as $date) {
                $row = [];
                foreach ($fields as $field) {
                    $row[] = ($field['field_name'] ?? '') === 'date'
                        ? $date->format($this->dateFormat($field['date_time_format'] ?? 'default'))
                        : $this->employeeValue(
                            $employee,
                            $field['field_name'] ?? '',
                            $masterValues,
                            $field['date_time_format'] ?? 'default',
                        );
                }
                $rows[] = $row;
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param iterable<User> $employees
     * @return array<string, array<int, string>>
     */
    private function masterValues(array $fields, iterable $employees): array
    {
        $relations = [
            'location_name' => ['column' => 'location_id', 'model' => Location::class, 'value' => 'name'],
            'location_code' => ['column' => 'location_id', 'model' => Location::class, 'value' => 'code'],
            'company_name' => ['column' => 'company_id', 'model' => Company::class, 'value' => 'name'],
            'company_code' => ['column' => 'company_id', 'model' => Company::class, 'value' => 'code'],
            'department_name' => ['column' => 'department_id', 'model' => Department::class, 'value' => 'name'],
            'department_code' => ['column' => 'department_id', 'model' => Department::class, 'value' => 'code'],
            'sub_department_name' => ['column' => 'sub_department_id', 'model' => SubDepartment::class, 'value' => 'name'],
            'sub_department_code' => ['column' => 'sub_department_id', 'model' => SubDepartment::class, 'value' => 'code'],
            'designation_name' => ['column' => 'designation_id', 'model' => Designation::class, 'value' => 'name'],
            'designation_code' => ['column' => 'designation_id', 'model' => Designation::class, 'value' => 'code'],
            'unit_mill_name' => ['column' => 'unit_id', 'model' => Unit::class, 'value' => 'name'],
            'unit_mill_code' => ['column' => 'unit_id', 'model' => Unit::class, 'value' => 'code'],
            'leave_group_name' => ['column' => 'leave_group_id', 'model' => LegacyLeaveGroup::class, 'value' => 'code'],
            'team_name' => ['column' => 'team_id', 'model' => Team::class, 'value' => 'name'],
            'team_code' => ['column' => 'team_id', 'model' => Team::class, 'value' => 'code'],
            'canteen_facility_name' => ['column' => 'canteen_facility_id', 'model' => CanteenFacility::class, 'value' => 'name'],
            'canteen_facility_code' => ['column' => 'canteen_facility_id', 'model' => CanteenFacility::class, 'value' => 'code'],
            'category_name' => ['column' => 'category_id', 'model' => Category::class, 'value' => 'name'],
            'category_code' => ['column' => 'category_id', 'model' => Category::class, 'value' => 'code'],
            'shift_name' => ['column' => 'shift_id', 'model' => Shift::class, 'value' => 'name'],
            'shift_code' => ['column' => 'shift_id', 'model' => Shift::class, 'value' => 'code'],
            'data_policy' => ['column' => 'data_policy_id', 'model' => DataPolicy::class, 'value' => 'name'],
            'blood_group' => ['column' => 'blood_group_id', 'model' => BloodGroup::class, 'value' => 'name'],
        ];
        $idsByField = [];

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
            $idsByField[$name] = $relation['model']::query()
                ->whereIn('id', array_values(array_unique($ids)))
                ->get(['id', $relation['value']])
                ->mapWithKeys(static fn ($record): array => [(int) $record->id => (string) ($record->{$relation['value']} ?? '')])
                ->all();
        }

        return $idsByField;
    }

    /**
     * @param array<string, array<int, string>> $masterValues
     */
    private function employeeValue(User $employee, string $field, array $masterValues, mixed $format): string
    {
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
            'status' => 'status',
            'birth_date' => 'dob',
            'gender' => 'gender',
            'phone' => 'number',
            'email' => 'email',
        ];

        if (isset($masterValues[$field])) {
            $relation = [
                'location_name' => 'location_id',
                'location_code' => 'location_id',
                'company_name' => 'company_id',
                'company_code' => 'company_id',
                'department_name' => 'department_id',
                'department_code' => 'department_id',
                'sub_department_name' => 'sub_department_id',
                'sub_department_code' => 'sub_department_id',
                'designation_name' => 'designation_id',
                'designation_code' => 'designation_id',
                'unit_mill_name' => 'unit_id',
                'unit_mill_code' => 'unit_id',
                'leave_group_name' => 'leave_group_id',
                'team_name' => 'team_id',
                'team_code' => 'team_id',
                'canteen_facility_name' => 'canteen_facility_id',
                'canteen_facility_code' => 'canteen_facility_id',
                'category_name' => 'category_id',
                'category_code' => 'category_id',
                'shift_name' => 'shift_id',
                'shift_code' => 'shift_id',
                'data_policy' => 'data_policy_id',
                'blood_group' => 'blood_group_id',
            ][$field];

            return implode(', ', array_filter(array_map(
                static fn (int $id): string => $masterValues[$field][$id] ?? '',
                $this->attributeIds($employee->getAttribute($relation)),
            )));
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
