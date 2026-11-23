<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use App\Services\Tenant\MasterSnapshotService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterSnapshotRecoverySeeder extends Seeder
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

    public function run(): void
    {
        $this->ensureSnapshotTable();

        DB::transaction(function (): void {
            $date = today()->toDateString();
            $timestamp = now()->toDateTimeString();

            foreach (self::MASTER_TABLES as $masterType => $tableName) {
                $this->seedMasterValues($masterType, $tableName, $date, $timestamp);
            }

            $this->seedEmployeeAssignments($date, $timestamp);
        });

        $snapshotService = app(MasterSnapshotService::class);
        User::query()
            ->where('user_type', 'employee')
            ->orderBy('id')
            ->chunkById(200, function ($employees) use ($snapshotService): void {
                foreach ($employees as $employee) {
                    $snapshotService->captureApproversForEmployee($employee);
                }
            });

        $this->command?->info('Master snapshot baselines restored from current master values and employee assignments. Historical changes cannot be reconstructed by this seeder.');
    }

    private function ensureSnapshotTable(): void
    {
        if (Schema::hasTable('master_snapshots')) {
            return;
        }

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
    }

    private function seedMasterValues(string $masterType, string $tableName, string $date, string $timestamp): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $fields = $this->availableFields($tableName);
        if ($fields === []) {
            return;
        }

        foreach (DB::table($tableName)->get(array_merge(['id'], $fields)) as $record) {
            $values = [];
            foreach ($fields as $field) {
                $values[$field] = $record->{$field};
            }

            $this->insertIfMissing($masterType, (int) $record->id, $values, null, true, $date, $timestamp);
        }
    }

    private function seedEmployeeAssignments(string $date, string $timestamp): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'user_type')) {
            return;
        }

        foreach (self::EMPLOYEE_RELATIONS as $attribute => [$masterType, $tableName]) {
            if (! Schema::hasColumn('users', $attribute) || ! Schema::hasTable($tableName)) {
                continue;
            }

            $fields = $this->availableFields($tableName);
            if ($fields === []) {
                continue;
            }

            $employeeColumns = ['id', $attribute];
            DB::table('users')
                ->select($employeeColumns)
                ->where('user_type', 'employee')
                ->orderBy('id')
                ->chunkById(200, function ($employees) use (
                    $attribute,
                    $date,
                    $masterType,
                    $tableName,
                    $fields,
                    $timestamp,
                ): void {
                    $employeeMasterIds = [];
                    foreach ($employees as $employee) {
                        $employeeMasterIds[(int) $employee->id] = $this->normalizeIds($employee->{$attribute});
                    }
                    $masterIds = array_values(array_unique(array_merge([], ...array_values($employeeMasterIds))));
                    if ($masterIds === []) {
                        return;
                    }

                    $records = DB::table($tableName)
                        ->whereIn('id', $masterIds)
                        ->get(array_merge(['id'], $fields))
                        ->keyBy('id');

                    foreach ($employees as $employee) {
                        foreach ($employeeMasterIds[(int) $employee->id] as $masterId) {
                            $record = $records->get($masterId);
                            if ($record === null) {
                                continue;
                            }

                            $values = [];
                            foreach ($fields as $field) {
                                $values[$field] = $record->{$field};
                            }
                            $this->insertIfMissing(
                                $masterType,
                                $masterId,
                                $values,
                                (int) $employee->id,
                                false,
                                $date,
                                $timestamp,
                            );
                        }
                    }
                }, 'id');
        }
    }

    private function insertIfMissing(
        string $masterType,
        int $masterId,
        array $values,
        ?int $userId,
        bool $forAll,
        string $fromDate,
        string $timestamp,
    ): void {
        if ($values === []) {
            return;
        }

        $query = DB::table('master_snapshots')
            ->where('master_type', $masterType)
            ->where('master_id', $masterId)
            ->where('for_all', $forAll)
            ->whereNull('to_date');

        if ($userId === null) {
            $query->whereNull('user_id');
        } else {
            $query->where('user_id', $userId);
        }

        if ($query->exists()) {
            return;
        }

        DB::table('master_snapshots')->insert([
            'master_type' => $masterType,
            'master_id' => $masterId,
            'type' => $this->valueType($values),
            'value' => json_encode($values, JSON_THROW_ON_ERROR),
            'from_date' => $fromDate,
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

    private function availableFields(string $tableName): array
    {
        return array_values(array_filter(
            ['name', 'code'],
            static fn (string $field): bool => Schema::hasColumn($tableName, $field),
        ));
    }

    private function valueType(array $values): string
    {
        return match (array_keys($values)) {
            ['name'] => 'name',
            ['code'] => 'code',
            default => 'both',
        };
    }
}
