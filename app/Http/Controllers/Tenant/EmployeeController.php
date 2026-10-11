<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreEmployeeRequest;
use App\Http\Requests\Tenant\UpdateEmployeeRequest;
use App\Jobs\Tenant\GenerateEmployeeExport;
use App\Jobs\Tenant\ProcessEmployeeImport;
use App\Models\Tenant\Category;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\Role;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\AbsentRule;
use App\Models\Tenant\BloodGroup;
use App\Models\Tenant\EarlyGoingRule;
use App\Models\Tenant\HalfDayRule;
use App\Models\Tenant\LateComingRule;
use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\User;
use App\Models\Tenant\PasswordPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    private const INDEX_URL = 'employee-structure/employees';
    private const RELATIONS = [
        'companies' => Company::class,
        'locations' => Location::class,
        'departments' => Department::class,
        'subDepartments' => SubDepartment::class,
        'categories' => Category::class,
        'subCategories' => SubCategory::class,
        'designations' => Designation::class,
        'grades' => Grade::class,
        'units' => Unit::class,
        'busRoutes' => BusRoute::class,
    ];

    public function index(): View
    {
        return view('employee-structure.employees.index', ['employees' => User::query()->where('user_type', 'employee')->latest('id')->paginate(15)->withQueryString()]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $status = $request->validate(['status' => ['nullable', 'in:all,1,0']])['status'] ?? 'all';
        $query = User::query()->where('user_type', 'employee')->latest('id');
        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }
        $employees = $query->get();

        return response()->json(['html' => view('employee-structure.employees.partials.rows', compact('employees'))->render(), 'count' => $employees->count()]);
    }

    public function create(): View
    {
        return view('employee-structure.employees.add', array_merge(['employee' => null], $this->formOptions()));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $shiftIds = $this->extractShiftIds($validated);
        DB::transaction(function () use ($request, $validated, $shiftIds): void {
            if ($request->hasFile('profile_pic')) {
                $validated['profile_pic'] = $request->file('profile_pic')->store('employee-profiles', 'public');
            }
            $roleId = $validated['role_id'] ?? null;
            unset($validated['role_id']);
            $employee = User::query()->create($this->attributes($validated));
            if ($roleId) {
                $employee->syncRoles([Role::query()->findOrFail($roleId)]);
            }
            if ($shiftIds !== null) {
                $employee->shifts()->sync($shiftIds);
            }
            $this->savePersonalInfo($employee, $validated);
            $this->saveAddress($employee, $validated);
            $this->saveEducationAndExperience($employee, $validated);
            $this->saveFamilyDetails($employee, $validated);
            $this->saveWeekOffs($employee, $validated);
        });
        return redirect(url(self::INDEX_URL))->with('success', 'Employee created successfully.');
    }

    public function show(Request $request): View
    {
        return view('employee-structure.employees.show', ['employee' => User::query()->where('user_type', 'employee')->findOrFail($request->route('employee'))]);
    }

    public function edit(Request $request): View
    {
        return view('employee-structure.employees.edit', array_merge(['employee' => User::query()->where('user_type', 'employee')->findOrFail($request->route('employee'))], $this->formOptions()));
    }

    public function update(UpdateEmployeeRequest $request): RedirectResponse
    {
        $employee = User::query()->where('user_type', 'employee')->findOrFail($request->route('employee'));
        $validated = $request->validated();
        if ($request->hasFile('profile_pic')) {
            $validated['profile_pic'] = $request->file('profile_pic')->store('employee-profiles', 'public');
        }
        $shiftIds = $this->extractShiftIds($validated);
        $roleId = $validated['role_id'] ?? null;
        unset($validated['role_id']);
        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }
        DB::transaction(function () use ($employee, $validated, $roleId, $shiftIds): void {
            $employee->update($this->attributes($validated));
            $employee->syncRoles($roleId ? [Role::query()->findOrFail($roleId)] : []);
            if ($shiftIds !== null) {
                $employee->shifts()->sync($shiftIds);
            }
            $this->savePersonalInfo($employee, $validated);
            $this->saveAddress($employee, $validated);
            $this->saveEducationAndExperience($employee, $validated);
            $this->saveFamilyDetails($employee, $validated);
        });
        return redirect(url(self::INDEX_URL))->with('success', 'Employee updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $employee = User::query()->where('user_type', 'employee')->findOrFail($request->route('employee'));
        if ((int) $employee->id === 1) {
            return redirect(url(self::INDEX_URL))->with('error', 'The primary administrator cannot be deleted.');
        }
        $employee->delete();
        return redirect(url(self::INDEX_URL))->with('success', 'Employee deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'employees_' . now()->format('Ymd_His') . '.csv';
        GenerateEmployeeExport::dispatch(Auth::guard('tenant')->id(), $fileName);
        return redirect(url(self::INDEX_URL))->with('success', 'Employee export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, User::IMPORT_EXPORT_COLUMNS);
            fclose($handle);
        }, 'employees_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'], 'update_duplicate_records' => ['nullable', 'boolean']]);
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $storedPath = $file->storeAs('employee-imports', 'employee_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());
        ProcessEmployeeImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false));
        return redirect(url(self::INDEX_URL))->with('success', 'Employee import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aemployees_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'employee-exports/' . $fileName;
        if (! Storage::disk('local')->exists($path)) {
            (new GenerateEmployeeExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'employees.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function formOptions(): array
    {
        $options = [];
        foreach (self::RELATIONS as $key => $model) {
            $options[$key] = $model::query()->where(fn($query) => $query->where('status', 1)->orWhereNull('status'))->orderBy('name')->get();
        }
        return array_merge($options, [
            'leaveGroups' => LeaveGroup::query()->where('status', 1)->orderBy('code')->get(),
            'teams' => Team::query()->where('status', 1)->orderBy('name')->get(),
            'canteenFacilities' => CanteenFacility::query()->where('status', 1)->orderBy('name')->get(),
            'passwordPolicies' => PasswordPolicy::query()->orderBy('policy_name')->get(),
            'shifts' => Shift::query()->where('status', 1)->orderBy('name')->get(),
            'shiftRotations' => ShiftRotation::query()->where('status', 1)->orderBy('code')->get(),
            'dataPolicies' => DataPolicy::query()->where('status', 1)->orderBy('name')->get(),
            'bloodGroups' => BloodGroup::query()->orderBy('id')->get(),
            'roles' => Role::query()->where('guard_name', 'tenant')->where('status', 1)->orderBy('name')->get(),
            'approvalFlows' => ApprovalFlow::query()->orderBy('approval_flow_code')->get(),
            'lateComingRules' => LateComingRule::query()->where('status', 1)->orderBy('code')->get(),
            'earlyGoingRules' => EarlyGoingRule::query()->where('status', 1)->orderBy('code')->get(),
            'halfDayRules' => HalfDayRule::query()->where('status', 1)->orderBy('code')->get(),
            'absentRules' => AbsentRule::query()->where('status', 1)->orderBy('code')->get(),
            'overtimeRules' => OvertimeRule::query()->where('status', 1)->orderBy('code')->get(),
        ]);
    }

    private function attributes(array $validated): array
    {
        $fields = [
            'code',
            'fname',
            'mname',
            'lname',
            'name',
            'email',
            'password',
            'number',
            'card',
            'bank_name',
            'bank_account_number',
            'bank_ifsc_code',
            'bank_account_branch_name',
            'gender',
            'dob',
            'blood_group_id',
            'join_date',
            'status',
            'user_type',
            'is_locked',
            'profile_pic',
            'left_date',
            'left_reason',
            'data_policy_id',
            'leave_group_id',
            'team_id',
            'canteen_facility_id',
            'company_id',
            'location_id',
            'department_id',
            'sub_department_id',
            'category_id',
            'sub_category_id',
            'designation_id',
            'grade_id',
            'unit_id',
            'bus_route_id',
            'dms_user_id',
            'rejoin_date',
            'rejoin_reason',
            'reference_name',
            'reference_number',
            'inactive_date',
            'inactive_days',
            'last_active_at',
            'last_login_at',
            'shift_status',
            'late_coming_rule_id',
            'early_going_rule_id',
            'half_day_rule_id',
            'absent_rule_id',
            'overtime_rule_id',
            'shift_rotation_id',
            'shift_change_id',
            'week_off_change_id',
            'shift_change_approval_flow_id',
            'week_off_change_approval_flow_id',
            'week_off_swap_approval_flow_id',
            'manual_punch_approval_flow_id',
            'manual_attendance_approval_flow_id',
            'leave_approval_flow_id',
            'short_leave_approval_flow_id',
            'grade_wise_leave_id',
            'last_check_date',
            'last_check_id',
            'short_leave_minutes',
            'coff_approval_flow_id',
            'od_approval_flow_id',
            'aadhar_number',
            'uan_number',
            'esic_number',
            'pan_number',
            'allow_mobile_login',
            'allow_mobile_punch',
            'shift_type',
            'password_policy_id',
            'is_inactive',
            'approval_flow_id',
            'login_attempts',
            'password_changed_at',
            'last_active_at',
            'last_login_at',
        ];
        $attributes = array_intersect_key($validated, array_flip($fields));
        $attributes['name'] = trim(($validated['fname'] ?? '') . ' ' . ($validated['lname'] ?? ''));

        return $attributes;
    }

    private function savePersonalInfo(User $employee, array $validated): void
    {
        $attributes = [
            'height' => $validated['height'] ?? null,
            'weight' => $validated['weight'] ?? null,
        ];

        foreach (['languages', 'hobbies'] as $field) {
            $items = array_map('trim', $validated[$field] ?? []);
            $attributes[$field] = array_values(array_filter($items, static fn (string $item): bool => $item !== '')) ?: null;
        }

        $emergencyFields = ['emergency_name', 'emergency_number', 'emergency_address'];
        $emergencyItems = [];
        $rowCount = 0;

        foreach ($emergencyFields as $field) {
            $emergencyItems[$field] = array_map(
                static fn(?string $item): string => trim($item ?? ''),
                $validated[$field] ?? [],
            );
            $rowCount = max($rowCount, count($emergencyItems[$field]));
        }

        $emergencyValues = array_fill_keys($emergencyFields, []);
        for ($index = 0; $index < $rowCount; $index++) {
            $row = array_map(
                static fn (string $field): string => $emergencyItems[$field][$index] ?? '',
                $emergencyFields,
            );

            if (count(array_filter($row, static fn (string $value): bool => $value !== '')) === 0) {
                continue;
            }

            foreach ($emergencyFields as $field) {
                $emergencyValues[$field][] = $emergencyItems[$field][$index] ?? '';
            }
        }

        foreach ($emergencyFields as $field) {
            $attributes[$field] = $emergencyValues[$field] !== []
                ? json_encode($emergencyValues[$field], JSON_THROW_ON_ERROR)
                : null;
        }

        $hasValues = collect($attributes)->contains(static fn($value): bool => $value !== null && $value !== '');
        if ($hasValues || $employee->personalInfo()->exists()) {
            $employee->personalInfo()->updateOrCreate([], $attributes);
        }
    }

    private function saveAddress(User $employee, array $validated): void
    {
        $attributes = [
            'flat_building' => $validated['flat_building'] ?? null,
            'house_no' => $validated['house_no'] ?? null,
            'flore' => $validated['flore'] ?? null,
            'street' => $validated['street'] ?? null,
            'landmark' => $validated['landmark'] ?? null,
            'country' => $validated['country'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'current_flat_building' => $validated['current_flat_building'] ?? null,
            'current_house_no' => $validated['current_house_no'] ?? null,
            'current_flore' => $validated['current_flore'] ?? null,
            'current_street' => $validated['current_street'] ?? null,
            'current_landmark' => $validated['current_landmark'] ?? null,
            'current_country' => $validated['current_country'] ?? null,
            'current_state' => $validated['current_state'] ?? null,
            'current_city' => $validated['current_city'] ?? null,
            'current_pincode' => $validated['current_pincode'] ?? null,
            'is_current_address' => (bool) ($validated['is_current_address'] ?? false),
        ];

        $address = $employee->addresses()->where('is_current_address', true)->first()
            ?? $employee->addresses()->first();
        $hasAddress = collect($attributes)->except('is_current_address')
            ->contains(static fn($value): bool => $value !== null && $value !== '');

        if ($address) {
            $address->update($attributes);
        } elseif ($hasAddress) {
            $employee->addresses()->create($attributes);
        }
    }

    private function saveEducationAndExperience(User $employee, array $validated): void
    {
        foreach ([
            'educations' => [
                'relation' => 'education',
                'fields' => ['education', 'year', 'university_name'],
                'submitted' => 'educations_submitted',
            ],
            'experiences' => [
                'relation' => 'experiences',
                'fields' => ['previous_employeer', 'designation', 'join_date', 'left_date', 'ctc'],
                'submitted' => 'experiences_submitted',
            ],
        ] as $input => $definition) {
            if (! array_key_exists($input, $validated) && ! ($validated[$definition['submitted']] ?? false)) {
                continue;
            }

            $relation = $employee->{$definition['relation']}();
            $relation->delete();

            foreach ($validated[$input] ?? [] as $row) {
                $attributes = array_intersect_key($row, array_flip($definition['fields']));
                $attributes = array_map(
                    static fn($value) => is_string($value) ? trim($value) : $value,
                    $attributes,
                );

                if (collect($attributes)->every(static fn($value): bool => $value === null || $value === '')) {
                    continue;
                }

                $relation->create($attributes);
            }
        }
    }

    private function saveFamilyDetails(User $employee, array $validated): void
    {
        if (! array_key_exists('family_details', $validated) && ! ($validated['family_details_submitted'] ?? false)) {
            return;
        }

        $relation = $employee->family_details();
        $relation->delete();

        foreach ($validated['family_details'] ?? [] as $row) {
            $attributes = array_intersect_key($row, array_flip(['relation', 'name', 'dob', 'is_nominee', 'occupation']));
            $attributes = array_map(
                static fn($value) => is_string($value) ? trim($value) : $value,
                $attributes,
            );

            $hasFamilyDetails = collect($attributes)
                ->except('is_nominee')
                ->contains(static fn($value): bool => $value !== null && $value !== '');

            if (! $hasFamilyDetails) {
                continue;
            }

            $attributes['is_nominee'] = (bool) ($attributes['is_nominee'] ?? false);
            $relation->create($attributes);
        }
    }

    private function saveWeekOffs(User $employee, array $validated): void
    {
        if (! ($validated['week_offs_submitted'] ?? false)) {
            return;
        }

        $relation = $employee->user_week_offs();
        $relation->delete();

        foreach ($validated['week_offs'] ?? [] as $weekOff) {
            $weekDays = $weekOff['week_days'] ?? null;
            $weekFlags = array_intersect_key($weekOff, array_flip([
                'first_week',
                'second_week',
                'third_week',
                'fourth_week',
                'fifth_week',
            ]));

            if ($weekDays === null || ! collect($weekFlags)->contains(static fn($enabled): bool => (bool) $enabled)) {
                continue;
            }

            $relation->create([
                'week_days' => $weekDays,
                'wo_type' => $weekOff['wo_type'] ?? 1,
                'first_week' => (bool) ($weekOff['first_week'] ?? false),
                'second_week' => (bool) ($weekOff['second_week'] ?? false),
                'third_week' => (bool) ($weekOff['third_week'] ?? false),
                'fourth_week' => (bool) ($weekOff['fourth_week'] ?? false),
                'fifth_week' => (bool) ($weekOff['fifth_week'] ?? false),
            ]);
        }
    }

    private function extractShiftIds(array &$validated): ?array
    {
        $shiftType = $validated['shift_type'] ?? null;
        if ($shiftType === null) {
            return null;
        }

        $shiftIds = $this->normalizeShiftIds($shiftType, $validated['shift_ids'] ?? []);
        unset($validated['shift_ids']);
        $validated['shift_rotation_id'] = $shiftType === 'rotational' ? ($validated['shift_rotation_id'] ?? null) : null;

        return $shiftIds;
    }

    private function normalizeShiftIds(string $shiftType, array $shiftIds): array
    {
        if ($shiftType === 'rotational') {
            return [];
        }

        return $shiftType === 'fixed' ? array_slice($shiftIds, 0, 1) : $shiftIds;
    }
}
