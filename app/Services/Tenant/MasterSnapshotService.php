<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\AbsentRule;
use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\Area;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\EarlyGoingRule;
use App\Models\Tenant\Grade;
use App\Models\Tenant\GradeWiseLeave;
use App\Models\Tenant\HalfDayRule;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\LateComingRule;
use App\Models\Tenant\LeaveReason;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\LegacyLeaveGroup;
use App\Models\Tenant\Location;
use App\Models\Tenant\Machine;
use App\Models\Tenant\MasterSnapshot;
use App\Models\Tenant\OvertimeReason;
use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\Role;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\StatusMaster;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterSnapshotService
{
    private const EMPLOYEE_APPROVAL_FLOW_FIELDS = [
        'approval_flow_id' => 'Approval Flow',
        'shift_change_approval_flow_id' => 'Shift Change',
        'week_off_change_approval_flow_id' => 'Week Off Change',
        'week_off_swap_approval_flow_id' => 'Week Off Swap',
        'manual_punch_approval_flow_id' => 'Manual Punch',
        'manual_attendance_approval_flow_id' => 'Manual Attendance',
        'leave_approval_flow_id' => 'Leave',
        'short_leave_approval_flow_id' => 'Short Leave',
        'coff_approval_flow_id' => 'COFF',
        'od_approval_flow_id' => 'OD',
        'overtime_approval_flow_id' => 'Overtime',
    ];

    private const MASTER_MODELS = [
        AbsentRule::class,
        Area::class,
        BusRoute::class,
        CanteenFacility::class,
        Category::class,
        Company::class,
        DataPolicy::class,
        Department::class,
        Designation::class,
        EarlyGoingRule::class,
        Grade::class,
        GradeWiseLeave::class,
        HalfDayRule::class,
        Holiday::class,
        LateComingRule::class,
        LeaveReason::class,
        LeaveType::class,
        LegacyLeaveGroup::class,
        Location::class,
        Machine::class,
        OvertimeReason::class,
        OvertimeRule::class,
        Role::class,
        Shift::class,
        ShiftRotation::class,
        StatusMaster::class,
        SubCategory::class,
        SubDepartment::class,
        Team::class,
        Unit::class,
    ];

    public function captureCreated(Model $model): void
    {
        if ($model instanceof User) {
            $this->captureEmployeeAssignments($model, []);

            return;
        }

        if ($this->isMaster($model)) {
            $values = $this->availableValues($model);
            if ($values !== []) {
                $this->createSnapshot($model::class, $model->getKey(), $values, null, true);
            }
        }
    }

    public function captureUpdated(Model $model): void
    {
        if ($model instanceof User) {
            if ($model->user_type === 'employee') {
                $this->captureEmployeeAssignments($model, array_keys($model->getChanges()));
            }

            return;
        }

        if (! $this->isMaster($model)) {
            return;
        }

        $changedFields = array_values(array_intersect(['name', 'code'], array_keys($model->getChanges())));
        if ($changedFields === []) {
            return;
        }

        $values = [];
        foreach ($changedFields as $field) {
            $values[$field] = $model->getAttribute($field);
        }

        DB::transaction(function () use ($model, $values): void {
            MasterSnapshot::query()
                ->where('master_type', $model::class)
                ->where('master_id', $model->getKey())
                ->whereNull('user_id')
                ->where('for_all', true)
                ->whereNull('to_date')
                ->update(['to_date' => today()->toDateString(), 'updated_at' => now()]);

            $this->createSnapshot($model::class, $model->getKey(), $values, null, true);
        });
    }

    private function captureEmployeeAssignments(User $employee, array $changedFields): void
    {
        if ($employee->user_type !== 'employee') {
            return;
        }

        $attributes = $changedFields === [] ? array_keys($employee->getAttributes()) : $changedFields;
        if ($changedFields === [] || array_intersect(self::EMPLOYEE_APPROVAL_FLOW_FIELDS, $attributes) !== []) {
            $this->captureApproversForEmployee($employee);
        }

        foreach ($attributes as $attribute) {
            if (! str_ends_with($attribute, '_id')) {
                continue;
            }

            $relationName = substr($attribute, 0, -3);
            if (! method_exists($employee, $relationName)) {
                continue;
            }

            $relation = $employee->{$relationName}();
            if (! $relation instanceof BelongsTo) {
                continue;
            }

            $relatedModel = $relation->getRelated();
            if (! $this->isMaster($relatedModel)) {
                continue;
            }

            $oldIds = $this->normalizeIds($employee->getRawOriginal($attribute));
            $newIds = $this->normalizeIds($employee->getAttributes()[$attribute] ?? null);
            if ($oldIds === $newIds) {
                continue;
            }

            $type = $relatedModel::class;
            $removedIds = array_values(array_diff($oldIds, $newIds));
            $addedIds = array_values(array_diff($newIds, $oldIds));

            DB::transaction(function () use ($employee, $relatedModel, $type, $removedIds, $addedIds): void {
                if ($removedIds !== []) {
                    MasterSnapshot::query()
                        ->where('master_type', $type)
                        ->where('user_id', $employee->getKey())
                        ->where('for_all', false)
                        ->whereNull('to_date')
                        ->whereIn('master_id', $removedIds)
                        ->update(['to_date' => today()->toDateString(), 'updated_at' => now()]);
                }

                foreach ($addedIds as $masterId) {
                    $values = $this->relatedValues($relatedModel, $masterId);
                    if ($values !== []) {
                        $this->createSnapshot($type, $masterId, $values, $employee->getKey(), false);
                    }
                }
            });
        }
    }

    public function captureApproverSnapshotsForFlow(ApprovalFlow $flow, array $previousEmployeeIds = []): void
    {
        $query = User::query()
            ->where('user_type', 'employee')
            ->where(function ($query) use ($flow): void {
                foreach (array_keys(self::EMPLOYEE_APPROVAL_FLOW_FIELDS) as $field) {
                    $query->orWhere($field, $flow->getKey());
                }
            });
        $employeeIds = array_values(array_unique(array_merge(
            array_map('intval', $previousEmployeeIds),
            $query->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
        )));

        if ($employeeIds === []) {
            return;
        }

        User::query()
            ->where('user_type', 'employee')
            ->whereIn('id', $employeeIds)
            ->get()
            ->each(fn (User $employee) => $this->captureApproversForEmployee($employee));
    }

    public function captureApproversForEmployee(User $employee): void
    {
        if ($employee->user_type !== 'employee' || $employee->getKey() === null) {
            return;
        }

        $flowIds = array_values(array_unique(array_filter(array_map(
            static fn (string $field): int => (int) $employee->getAttribute($field),
            array_keys(self::EMPLOYEE_APPROVAL_FLOW_FIELDS),
        ), static fn (int $id): bool => $id > 0)));
        $flowsById = [];

        if ($flowIds !== []) {
            $flows = ApprovalFlow::query()
                ->whereIn('id', $flowIds)
                ->with([
                    'approval_flow_details' => static fn ($query) => $query->orderBy('level'),
                    'approval_flow_details.users' => static fn ($query) => $query->orderBy('users.id'),
                ])
                ->orderBy('id')
                ->get();
            $flowsById = $flows->keyBy('id')->all();
        }

        $approvalFlows = [];
        foreach (self::EMPLOYEE_APPROVAL_FLOW_FIELDS as $field => $module) {
            $flowId = (int) $employee->getAttribute($field);
            $flow = $flowsById[$flowId] ?? null;
            if ($flow === null) {
                continue;
            }

            $levels = [];
            foreach ($flow->approval_flow_details as $detail) {
                $approvers = [];
                foreach ($detail->users as $approver) {
                    $approvers[] = [
                        'id' => (int) $approver->getKey(),
                        'name' => (string) ($approver->name ?? ''),
                        'code' => (string) ($approver->code ?? ''),
                    ];
                }
                $levels[] = [
                    'level' => (int) $detail->level,
                    'approvers' => $approvers,
                ];
            }

            $approvalFlows[] = [
                'module' => $module,
                'flow_id' => (int) $flow->getKey(),
                'flow_code' => (string) $flow->approval_flow_code,
                'levels' => $levels,
            ];
        }

        $snapshot = MasterSnapshot::query()
            ->where('master_type', MasterSnapshot::APPROVERS_SNAPSHOT_TYPE)
            ->where('master_id', $employee->getKey())
            ->where('user_id', $employee->getKey())
            ->where('for_all', false)
            ->whereNull('to_date')
            ->latest('id')
            ->first();
        $value = ['approval_flows' => $approvalFlows];

        if ($snapshot === null && $approvalFlows === []) {
            return;
        }

        if ($snapshot?->value === $value) {
            return;
        }

        DB::transaction(function () use ($employee, $snapshot, $value): void {
            if ($snapshot !== null) {
                $snapshot->update(['to_date' => today()->toDateString()]);
            }

            $this->createSnapshot(
                MasterSnapshot::APPROVERS_SNAPSHOT_TYPE,
                $employee->getKey(),
                $value,
                (int) $employee->getKey(),
                false,
            );
        });
    }

    private function relatedValues(Model $model, int $id): array
    {
        $fields = $this->availableFields($model);
        if ($fields === []) {
            return [];
        }

        $record = DB::table($model->getTable())
            ->where($model->getKeyName(), $id)
            ->first($fields);

        return $record === null ? [] : (array) $record;
    }

    private function availableValues(Model $model): array
    {
        $values = [];
        foreach ($this->availableFields($model) as $field) {
            $values[$field] = $model->getAttribute($field);
        }

        return $values;
    }

    private function availableFields(Model $model): array
    {
        return array_values(array_filter(
            ['name', 'code'],
            static fn (string $field): bool => Schema::hasColumn($model->getTable(), $field),
        ));
    }

    private function isMaster(Model $model): bool
    {
        return in_array($model::class, self::MASTER_MODELS, true);
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

    private function createSnapshot(string $masterType, int|string|null $masterId, array $values, ?int $userId, bool $forAll): void
    {
        MasterSnapshot::query()->create([
            'master_type' => $masterType,
            'master_id' => $masterId,
            'type' => $this->valueType($values),
            'value' => $values,
            'from_date' => today()->toDateString(),
            'to_date' => null,
            'user_id' => $userId,
            'for_all' => $forAll,
        ]);
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
