<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Notifications\Tenant\EmployeeImportExportCompleted;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateEmployeeExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<string, array<int|string, string>>
     */
    private array $optionLabelCache = [];

    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) return;
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) return;
        fputcsv($handle, User::EMPLOYEE_EXPORT_COLUMNS);
        $employees = User::query()
            ->where('user_type', 'employee')
            ->with(['personalInfo', 'addresses', 'education', 'experiences', 'family_details', 'user_week_offs', 'roles', 'shifts'])
            ->orderBy('name')
            ->get();
        $this->primeOptionLabels($employees);

        foreach ($employees as $employee) {
            fputcsv($handle, $this->orderByExportColumns($this->exportValues($employee)));
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('employee-exports/' . $this->fileName, $contents)) return;
        $user->notify(new EmployeeImportExportCompleted('export', 'Employee export is ready for download.', '/employee-structure/employees/export/download/' . rawurlencode($this->fileName)));
    }

    private function names(mixed $value, string $model): string
    {
        return $this->optionLabels($value, $model, ['name']);
    }

    /**
     * @return array<string, mixed>
     */
    private function exportValues(User $employee): array
    {
        $personalInfo = $employee->personalInfo;
        $address = $employee->addresses->firstWhere('is_current_address', true) ?? $employee->addresses->first();

        return [
            'code' => $employee->code,
            'fname' => $employee->fname,
            'mname' => $employee->mname,
            'lname' => $employee->lname,
            'name' => $employee->name,
            'email' => $employee->email,
            'number' => $employee->number,
            'card' => $employee->card,
            'gender' => $employee->gender === null ? '' : ucfirst((string) $employee->gender),
            'dob' => $this->formatDate($employee->dob),
            'blood_group' => $this->optionLabels($employee->blood_group_id, 'BloodGroup', ['name']),
            'height' => $personalInfo?->height,
            'weight' => $personalInfo?->weight,
            'languages' => $this->jsonValue($this->listValue($personalInfo?->languages)),
            'hobbies' => $this->jsonValue($this->listValue($personalInfo?->hobbies)),
            'emergency_name' => $this->jsonValue($this->listValue($personalInfo?->emergency_name)),
            'emergency_number' => $this->jsonValue($this->listValue($personalInfo?->emergency_number)),
            'emergency_address' => $this->jsonValue($this->listValue($personalInfo?->emergency_address)),
            'join_date' => $this->formatDate($employee->join_date),
            'status' => $employee->status ? 'Active' : 'Inactive',
            'user_type' => $employee->user_type,
            'company' => $this->names($employee->company_id, 'Company'),
            'location' => $this->names($employee->location_id, 'Location'),
            'department' => $this->names($employee->department_id, 'Department'),
            'sub_department' => $this->names($employee->sub_department_id, 'SubDepartment'),
            'category' => $this->names($employee->category_id, 'Category'),
            'sub_category' => $this->names($employee->sub_category_id, 'SubCategory'),
            'designation' => $this->names($employee->designation_id, 'Designation'),
            'grade' => $this->names($employee->grade_id, 'Grade'),
            'unit' => $this->names($employee->unit_id, 'Unit'),
            'bus_route' => $this->names($employee->bus_route_id, 'BusRoute'),
            'leave_group' => $this->optionLabels($employee->leave_group_id, 'LeaveGroup', ['code', 'description'], ' - '),
            'team' => $this->optionLabels($employee->team_id, 'Team', ['name', 'code'], ' - '),
            'canteen_facility' => $this->optionLabels($employee->canteen_facility_id, 'CanteenFacility', ['name', 'code'], ' - '),
            'shift_type' => $employee->shift_type === null ? '' : ucfirst((string) $employee->shift_type),
            'shift' => $employee->shifts
                ->map(fn($shift): string => trim($shift->name . ($shift->code ? ' (' . $shift->code . ')' : '')))
                ->join(', '),
            'shift_rotation' => $this->optionLabels($employee->shift_rotation_id, 'ShiftRotation', ['code']),
            'data_policy' => $this->optionLabels($employee->data_policy_id, 'DataPolicy', ['name']),
            'role' => $employee->roles->pluck('name')->join(', '),
            'is_locked' => $employee->is_locked ? 'Yes' : 'No',
            'allow_mobile_login' => $employee->allow_mobile_login ? 'Yes' : 'No',
            'allow_mobile_punch' => $employee->allow_mobile_punch ? 'Yes' : 'No',
            'password_policy' => $this->optionLabels($employee->password_policy_id, 'PasswordPolicy', ['policy_name']),
            'aadhar_number' => $employee->aadhar_number,
            'uan_number' => $employee->uan_number,
            'esic_number' => $employee->esic_number,
            'pan_number' => $employee->pan_number,
            'left_date' => $this->formatDate($employee->left_date),
            'left_reason' => $employee->left_reason,
            'is_left' => $employee->left_date || $employee->left_reason ? 'Yes' : 'No',
            'profile_pic' => $employee->profile_pic,
            'dms_user_id' => $employee->dms_user_id,
            'bank_name' => $employee->bank_name,
            'bank_account_number' => $employee->bank_account_number,
            'bank_ifsc_code' => $employee->bank_ifsc_code,
            'bank_account_branch_name' => $employee->bank_account_branch_name,
            'reference_name' => $employee->reference_name,
            'reference_number' => $employee->reference_number,
            'rejoin_date' => $this->formatDate($employee->rejoin_date),
            'rejoin_reason' => $employee->rejoin_reason,
            'inactive_date' => $this->formatDate($employee->inactive_date),
            'inactive_days' => $employee->inactive_days,
            'is_inactive' => $employee->is_inactive ? 'Yes' : 'No',
            'login_attempts' => $employee->login_attempts,
            'password_changed_at' => $this->formatDateTime($employee->password_changed_at),
            'last_active_at' => $this->formatDateTime($employee->last_active_at),
            'last_login_at' => $this->formatDateTime($employee->last_login_at),
            'shift_status' => $employee->shift_status ? 'Yes' : 'No',
            'late_coming_rule' => $this->optionLabels($employee->late_coming_rule_id, 'LateComingRule', ['code']),
            'early_going_rule' => $this->optionLabels($employee->early_going_rule_id, 'EarlyGoingRule', ['code']),
            'half_day_rule' => $this->optionLabels($employee->half_day_rule_id, 'HalfDayRule', ['code']),
            'absent_rule' => $this->optionLabels($employee->absent_rule_id, 'AbsentRule', ['code']),
            'overtime_rule' => $this->optionLabels($employee->overtime_rule_id, 'OvertimeRule', ['code']),
            'approval_flow' => $this->optionLabels($employee->approval_flow_id, 'ApprovalFlow', ['approval_flow_code']),
            'shift_change_id' => $employee->shift_change_id,
            'week_off_change_id' => $employee->week_off_change_id,
            'grade_wise_leave_id' => $employee->grade_wise_leave_id,
            'shift_change_approval_flow' => $this->approvalFlowLabel($employee->shift_change_approval_flow_id),
            'week_off_change_approval_flow' => $this->approvalFlowLabel($employee->week_off_change_approval_flow_id),
            'week_off_swap_approval_flow' => $this->approvalFlowLabel($employee->week_off_swap_approval_flow_id),
            'manual_punch_approval_flow' => $this->approvalFlowLabel($employee->manual_punch_approval_flow_id),
            'manual_attendance_approval_flow' => $this->approvalFlowLabel($employee->manual_attendance_approval_flow_id),
            'leave_approval_flow' => $this->approvalFlowLabel($employee->leave_approval_flow_id),
            'short_leave_approval_flow' => $this->approvalFlowLabel($employee->short_leave_approval_flow_id),
            'coff_approval_flow' => $this->approvalFlowLabel($employee->coff_approval_flow_id),
            'od_approval_flow' => $this->approvalFlowLabel($employee->od_approval_flow_id),
            'last_check_date' => $this->formatDate($employee->last_check_date),
            'last_check_id' => $employee->last_check_id,
            'short_leave_minutes' => $employee->short_leave_minutes,
            'flat_building' => $address?->flat_building,
            'house_no' => $address?->house_no,
            'flore' => $address?->flore,
            'street' => $address?->street,
            'landmark' => $address?->landmark,
            'country' => $address?->country,
            'state' => $address?->state,
            'city' => $address?->city,
            'pincode' => $address?->pincode,
            'current_flat_building' => $address?->current_flat_building,
            'current_house_no' => $address?->current_house_no,
            'current_flore' => $address?->current_flore,
            'current_street' => $address?->current_street,
            'current_landmark' => $address?->current_landmark,
            'current_country' => $address?->current_country,
            'current_state' => $address?->current_state,
            'current_city' => $address?->current_city,
            'current_pincode' => $address?->current_pincode,
            'is_current_address' => $address?->is_current_address ? 'Yes' : 'No',
            'educations' => $this->rowJson($employee->education, ['education', 'year', 'university_name']),
            'experiences' => $this->rowJson($employee->experiences, ['previous_employeer', 'designation', 'join_date', 'left_date', 'ctc'], ['join_date', 'left_date']),
            'family_details' => $this->rowJson($employee->family_details, ['relation', 'name', 'dob', 'is_nominee', 'occupation'], ['dob'], ['is_nominee']),
            'week_offs' => $this->weekOffJson($employee->user_week_offs),
        ];
    }

    private function approvalFlowLabel(mixed $value): string
    {
        return $this->optionLabels($value, 'ApprovalFlow', ['approval_flow_code']);
    }

    /**
     * @param array<int, string> $columns
     */
    private function optionLabels(mixed $value, string $model, array $columns, string $separator = ''): string
    {
        $ids = $this->idValues($value);
        if ($ids === []) {
            return '';
        }

        $cacheKey = $model . ':' . implode(',', $columns) . ':' . $separator;
        if (! isset($this->optionLabelCache[$cacheKey])) {
            $this->cacheOptionLabels($cacheKey, $model, $ids, $columns, $separator);
        } else {
            $missingIds = array_diff($ids, array_keys($this->optionLabelCache[$cacheKey]));
            if ($missingIds !== []) {
                $this->cacheOptionLabels($cacheKey, $model, $missingIds, $columns, $separator);
            }
        }

        $labels = $this->optionLabelCache[$cacheKey];

        return collect($ids)
            ->map(static fn($id): string => $labels[$id] ?? (string) $id)
            ->filter(static fn(string $label): bool => $label !== '')
            ->join(', ');
    }

    /**
     * @param array<int, mixed> $ids
     * @param array<int, string> $columns
     */
    private function cacheOptionLabels(string $cacheKey, string $model, array $ids, array $columns, string $separator): void
    {
        $existing = $this->optionLabelCache[$cacheKey] ?? [];
        $ids = array_values(array_diff($ids, array_keys($existing)));
        if ($ids === []) {
            return;
        }

        $class = 'App\\Models\\Tenant\\' . $model;
        $select = array_values(array_unique(array_merge(['id'], $columns)));
        $rows = $class::query()->whereIn('id', $ids)->get($select);
        $labels = array_combine($ids, array_map('strval', $ids));

        foreach ($rows as $row) {
            $parts = array_values(array_filter(
                array_map(static fn(string $column): string => (string) ($row->{$column} ?? ''), $columns),
                static fn(string $part): bool => $part !== '',
            ));
            $labels[$row->getKey()] = implode($separator, $parts);
        }

        $this->optionLabelCache[$cacheKey] = array_replace($existing, $labels);
    }

    private function primeOptionLabels(Collection $employees): void
    {
        $definitions = [
            'company_id' => ['Company', ['name'], ''],
            'location_id' => ['Location', ['name'], ''],
            'department_id' => ['Department', ['name'], ''],
            'sub_department_id' => ['SubDepartment', ['name'], ''],
            'category_id' => ['Category', ['name'], ''],
            'sub_category_id' => ['SubCategory', ['name'], ''],
            'designation_id' => ['Designation', ['name'], ''],
            'grade_id' => ['Grade', ['name'], ''],
            'unit_id' => ['Unit', ['name'], ''],
            'bus_route_id' => ['BusRoute', ['name'], ''],
            'blood_group_id' => ['BloodGroup', ['name'], ''],
            'leave_group_id' => ['LeaveGroup', ['code', 'description'], ' - '],
            'team_id' => ['Team', ['name', 'code'], ' - '],
            'canteen_facility_id' => ['CanteenFacility', ['name', 'code'], ' - '],
            'shift_rotation_id' => ['ShiftRotation', ['code'], ''],
            'data_policy_id' => ['DataPolicy', ['name'], ''],
            'password_policy_id' => ['PasswordPolicy', ['policy_name'], ''],
            'late_coming_rule_id' => ['LateComingRule', ['code'], ''],
            'early_going_rule_id' => ['EarlyGoingRule', ['code'], ''],
            'half_day_rule_id' => ['HalfDayRule', ['code'], ''],
            'absent_rule_id' => ['AbsentRule', ['code'], ''],
            'overtime_rule_id' => ['OvertimeRule', ['code'], ''],
            'approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'shift_change_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'week_off_change_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'week_off_swap_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'manual_punch_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'manual_attendance_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'leave_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'short_leave_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'coff_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
            'od_approval_flow_id' => ['ApprovalFlow', ['approval_flow_code'], ''],
        ];

        foreach ($definitions as $field => [$model, $columns, $separator]) {
            $ids = $employees->flatMap(fn(User $employee): array => $this->idValues($employee->{$field}))
                ->unique()
                ->values()
                ->all();
            if ($ids !== []) {
                $this->cacheOptionLabels($model . ':' . implode(',', $columns) . ':' . $separator, $model, $ids, $columns, $separator);
            }
        }
    }

    /**
     * @return array<int, mixed>
     */
    private function idValues(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        $values = is_array($value) ? $value : [$value];

        return array_values(array_filter($values, static fn($id): bool => $id !== null && $id !== ''));
    }

    private function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return $value instanceof DateTimeInterface
            ? $value->format('Y-m-d')
            : Carbon::parse((string) $value)->format('Y-m-d');
    }

    private function formatDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return $value instanceof DateTimeInterface
            ? $value->format('Y-m-d H:i:s')
            : Carbon::parse((string) $value)->format('Y-m-d H:i:s');
    }

    /**
     * @return array<int, mixed>
     */
    private function listValue(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : (preg_split('/[\r\n,]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        }

        return array_values(array_filter(
            array_map(static fn($item): string => is_scalar($item) ? trim((string) $item) : '', (array) $value),
            static fn(string $item): bool => $item !== '',
        ));
    }

    /**
     * @param iterable<object> $rows
     * @param array<int, string> $fields
     * @param array<int, string> $dateFields
     * @param array<int, string> $booleanFields
     */
    private function rowJson(iterable $rows, array $fields, array $dateFields = [], array $booleanFields = []): string
    {
        $values = [];
        foreach ($rows as $row) {
            $value = [];
            foreach ($fields as $field) {
                $item = $row->{$field};
                $value[$field] = in_array($field, $dateFields, true)
                    ? $this->formatDate($item)
                    : (in_array($field, $booleanFields, true) ? ($item ? 'Yes' : 'No') : $item);
            }
            $values[] = $value;
        }

        return $this->jsonValue($values);
    }

    /**
     * @param iterable<object> $weekOffs
     */
    private function weekOffJson(iterable $weekOffs): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $types = [1 => 'Full Day', 2 => 'First Half', 3 => 'Second Half'];
        $fields = ['first_week', 'second_week', 'third_week', 'fourth_week', 'fifth_week'];
        $values = [];

        foreach ($weekOffs as $weekOff) {
            $row = [
                'week_days' => $days[$weekOff->week_days] ?? (string) $weekOff->week_days,
                'wo_type' => $types[$weekOff->wo_type] ?? (string) $weekOff->wo_type,
            ];
            foreach ($fields as $field) {
                $row[$field] = $weekOff->{$field} ? 'Yes' : 'No';
            }
            $values[] = $row;
        }

        return $this->jsonValue($values);
    }

    /**
     * @param array<int, mixed> $value
     */
    private function jsonValue(array $value): string
    {
        return $value === [] ? '' : json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, mixed> $values
     * @return array<int, mixed>
     */
    private function orderByExportColumns(array $values): array
    {
        return array_map(
            static fn (string $column): mixed => $values[$column] ?? '',
            User::EMPLOYEE_EXPORT_COLUMNS,
        );
    }
}
