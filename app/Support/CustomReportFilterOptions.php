<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant\BusRoute;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\LegacyLeaveGroup;
use App\Models\Tenant\Location;
use App\Models\Tenant\Shift;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;

final class CustomReportFilterOptions
{
    /**
     * @return array<string, array{label: string, model: class-string, column: string, json: bool, label_column?: string}>
     */
    public static function definitions(): array
    {
        return [
            'location_ids' => ['label' => 'Locations', 'model' => Location::class, 'column' => 'location_id', 'json' => true],
            'company_ids' => ['label' => 'Companies', 'model' => Company::class, 'column' => 'company_id', 'json' => true],
            'department_ids' => ['label' => 'Departments', 'model' => Department::class, 'column' => 'department_id', 'json' => true],
            'sub_department_ids' => ['label' => 'Sub Departments', 'model' => SubDepartment::class, 'column' => 'sub_department_id', 'json' => true],
            'category_ids' => ['label' => 'Categories', 'model' => Category::class, 'column' => 'category_id', 'json' => true],
            'sub_category_ids' => ['label' => 'Sub Categories', 'model' => SubCategory::class, 'column' => 'sub_category_id', 'json' => true],
            'designation_ids' => ['label' => 'Designations', 'model' => Designation::class, 'column' => 'designation_id', 'json' => true],
            'grade_ids' => ['label' => 'Grades', 'model' => Grade::class, 'column' => 'grade_id', 'json' => true],
            'unit_ids' => ['label' => 'Units', 'model' => Unit::class, 'column' => 'unit_id', 'json' => true],
            'bus_route_ids' => ['label' => 'Bus Routes', 'model' => BusRoute::class, 'column' => 'bus_route_id', 'json' => true],
            'leave_group_ids' => ['label' => 'Leave Groups', 'model' => LegacyLeaveGroup::class, 'column' => 'leave_group_id', 'json' => false, 'label_column' => 'code'],
            'team_ids' => ['label' => 'Teams', 'model' => Team::class, 'column' => 'team_id', 'json' => false],
            'canteen_facility_ids' => ['label' => 'Canteen Facilities', 'model' => CanteenFacility::class, 'column' => 'canteen_facility_id', 'json' => false],
            'shift_ids' => ['label' => 'Shifts', 'model' => Shift::class, 'column' => 'shift_id', 'json' => false],
            'data_policy_ids' => ['label' => 'Data Policies', 'model' => DataPolicy::class, 'column' => 'data_policy_id', 'json' => false],
        ];
    }

    /**
     * @return array<string, array{label: string, options: array<int, array{id: int, label: string}>}>
     */
    public static function forForm(): array
    {
        $options = [];

        foreach (self::definitions() as $key => $definition) {
            $labelColumn = $definition['label_column'] ?? 'name';
            $records = $definition['model']::query()
                ->orderBy($labelColumn)
                ->get(array_values(array_unique(['id', $labelColumn, 'code'])));

            $options[$key] = [
                'label' => $definition['label'],
                'options' => $records->map(static fn ($record): array => [
                    'id' => (int) $record->id,
                    'label' => trim($record->{$labelColumn}.(
                        $labelColumn !== 'code' && $record->code ? ' ('.$record->code.')' : ''
                    )),
                ])->all(),
            ];
        }

        $options['employee_ids'] = [
            'label' => 'Employees',
            'options' => User::query()
                ->where('user_type', 'employee')
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
                ->map(static fn (User $employee): array => [
                    'id' => (int) $employee->id,
                    'label' => trim(($employee->code ? $employee->code.' - ' : '').$employee->name),
                ])->all(),
        ];

        return $options;
    }
}
