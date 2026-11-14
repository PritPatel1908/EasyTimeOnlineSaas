<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MASTER_TABLES = [
        'App\\Models\\Tenant\\AbsentRule' => 'absent_rules',
        'App\\Models\\Tenant\\Area' => 'areas',
        'App\\Models\\Tenant\\BusRoute' => 'bus_routes',
        'App\\Models\\Tenant\\CanteenFacility' => 'canteen_facilities',
        'App\\Models\\Tenant\\Category' => 'categories',
        'App\\Models\\Tenant\\Company' => 'companies',
        'App\\Models\\Tenant\\DataPolicy' => 'data_policies',
        'App\\Models\\Tenant\\Department' => 'departments',
        'App\\Models\\Tenant\\Designation' => 'designations',
        'App\\Models\\Tenant\\EarlyGoingRule' => 'early_going_rules',
        'App\\Models\\Tenant\\Grade' => 'grades',
        'App\\Models\\Tenant\\GradeWiseLeave' => 'grade_wise_leaves',
        'App\\Models\\Tenant\\HalfDayRule' => 'half_day_rules',
        'App\\Models\\Tenant\\Holiday' => 'holidays',
        'App\\Models\\Tenant\\LateComingRule' => 'late_coming_rules',
        'App\\Models\\Tenant\\LeaveReason' => 'leave_reasons',
        'App\\Models\\Tenant\\LeaveType' => 'leave_types',
        'App\\Models\\Tenant\\LegacyLeaveGroup' => 'leave_groups',
        'App\\Models\\Tenant\\Location' => 'locations',
        'App\\Models\\Tenant\\Machine' => 'machines',
        'App\\Models\\Tenant\\OvertimeReason' => 'overtime_reasons',
        'App\\Models\\Tenant\\OvertimeRule' => 'overtime_rules',
        'App\\Models\\Tenant\\Role' => 'roles',
        'App\\Models\\Tenant\\Shift' => 'shifts',
        'App\\Models\\Tenant\\ShiftRotation' => 'shift_rotations',
        'App\\Models\\Tenant\\StatusMaster' => 'status_masters',
        'App\\Models\\Tenant\\SubCategory' => 'sub_categories',
        'App\\Models\\Tenant\\SubDepartment' => 'sub_departments',
        'App\\Models\\Tenant\\Team' => 'teams',
        'App\\Models\\Tenant\\Unit' => 'units',
    ];

    private const EMPLOYEE_RELATIONS = [
        'location_id' => ['App\\Models\\Tenant\\Location', 'locations'],
        'company_id' => ['App\\Models\\Tenant\\Company', 'companies'],
        'category_id' => ['App\\Models\\Tenant\\Category', 'categories'],
        'sub_category_id' => ['App\\Models\\Tenant\\SubCategory', 'sub_categories'],
        'department_id' => ['App\\Models\\Tenant\\Department', 'departments'],
        'sub_department_id' => ['App\\Models\\Tenant\\SubDepartment', 'sub_departments'],
        'designation_id' => ['App\\Models\\Tenant\\Designation', 'designations'],
        'grade_id' => ['App\\Models\\Tenant\\Grade', 'grades'],
        'unit_id' => ['App\\Models\\Tenant\\Unit', 'units'],
        'bus_route_id' => ['App\\Models\\Tenant\\BusRoute', 'bus_routes'],
        'data_policy_id' => ['App\\Models\\Tenant\\DataPolicy', 'data_policies'],
        'shift_id' => ['App\\Models\\Tenant\\Shift', 'shifts'],
        'team_id' => ['App\\Models\\Tenant\\Team', 'teams'],
        'canteen_facility_id' => ['App\\Models\\Tenant\\CanteenFacility', 'canteen_facilities'],
        'leave_group_id' => ['App\\Models\\Tenant\\LegacyLeaveGroup', 'leave_groups'],
        'grade_wise_leave_id' => ['App\\Models\\Tenant\\GradeWiseLeave', 'grade_wise_leaves'],
        'shift_rotation_id' => ['App\\Models\\Tenant\\ShiftRotation', 'shift_rotations'],
        'absent_rule_id' => ['App\\Models\\Tenant\\AbsentRule', 'absent_rules'],
        'early_going_rule_id' => ['App\\Models\\Tenant\\EarlyGoingRule', 'early_going_rules'],
        'half_day_rule_id' => ['App\\Models\\Tenant\\HalfDayRule', 'half_day_rules'],
        'late_coming_rule_id' => ['App\\Models\\Tenant\\LateComingRule', 'late_coming_rules'],
        'overtime_rule_id' => ['App\\Models\\Tenant\\OvertimeRule', 'overtime_rules'],
    ];

    public function up(): void
    {
        Schema::create('master_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('master_type');
            $table->unsignedBigInteger('master_id');
            $table->string('type');
            $table->json('value');
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('for_all')->default(false);
            $table->timestamps();

            $table->index(['master_type', 'master_id', 'user_id', 'for_all', 'to_date'], 'master_snapshots_lookup_index');
        });

        $date = now()->toDateString();
        $timestamp = now()->toDateTimeString();

        foreach (self::MASTER_TABLES as $modelType => $tableName) {
            $this->seedMasterSnapshots($modelType, $tableName, $date, $timestamp);
        }

        $this->seedEmployeeSnapshots($date, $timestamp);
    }

    public function down(): void
    {
        Schema::dropIfExists('master_snapshots');
    }

    private function seedMasterSnapshots(string $modelType, string $tableName, string $date, string $timestamp): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $fields = array_values(array_filter(
            ['name', 'code'],
            static fn (string $field): bool => Schema::hasColumn($tableName, $field),
        ));
        if ($fields === []) {
            return;
        }

        foreach (DB::table($tableName)->get(array_merge(['id'], $fields)) as $record) {
            $values = [];
            foreach ($fields as $field) {
                $values[$field] = $record->{$field};
            }

            $this->insertSnapshot($modelType, (int) $record->id, $values, null, true, $date, $timestamp);
        }
    }

    private function seedEmployeeSnapshots(string $date, string $timestamp): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        foreach (self::EMPLOYEE_RELATIONS as $attribute => [$modelType, $tableName]) {
            if (! Schema::hasColumn('users', $attribute) || ! Schema::hasTable($tableName)) {
                continue;
            }

            $fields = array_values(array_filter(
                ['name', 'code'],
                static fn (string $field): bool => Schema::hasColumn($tableName, $field),
            ));
            if ($fields === []) {
                continue;
            }

            foreach (DB::table('users')->where('user_type', 'employee')->get(['id', $attribute]) as $employee) {
                $ids = $this->normalizeIds($employee->{$attribute});
                if ($ids === []) {
                    continue;
                }

                foreach (DB::table($tableName)->whereIn('id', $ids)->get(array_merge(['id'], $fields)) as $record) {
                    $values = [];
                    foreach ($fields as $field) {
                        $values[$field] = $record->{$field};
                    }

                    $this->insertSnapshot($modelType, (int) $record->id, $values, (int) $employee->id, false, $date, $timestamp);
                }
            }
        }
    }

    private function insertSnapshot(
        string $modelType,
        int $masterId,
        array $values,
        ?int $userId,
        bool $forAll,
        string $date,
        string $timestamp,
    ): void {
        if ($values === []) {
            return;
        }

        DB::table('master_snapshots')->insert([
            'master_type' => $modelType,
            'master_id' => $masterId,
            'type' => $this->valueType($values),
            'value' => json_encode($values, JSON_THROW_ON_ERROR),
            'from_date' => $date,
            'to_date' => null,
            'user_id' => $userId,
            'for_all' => $forAll,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function normalizeIds(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        $ids = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0,
        )));
    }

    private function valueType(array $values): string
    {
        return match (array_keys($values)) {
            ['name'] => 'name',
            ['code'] => 'code',
            default => 'both',
        };
    }
};
