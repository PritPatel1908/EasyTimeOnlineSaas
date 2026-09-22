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
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\BusRoute;
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
        DB::transaction(function () use ($request): void {
            $validated = $request->validated();
            if ($request->hasFile('profile_pic')) {
                $validated['profile_pic'] = $request->file('profile_pic')->store('employee-profiles', 'public');
            }
            $roleId = $validated['role_id'] ?? null;
            unset($validated['role_id']);
            $employee = User::query()->create($this->attributes($validated));
            if ($roleId) {
                $employee->syncRoles([Role::query()->findOrFail($roleId)]);
            }
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
        $roleId = $validated['role_id'] ?? null;
        unset($validated['role_id']);
        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }
        DB::transaction(function () use ($employee, $validated, $roleId): void {
            $employee->update($this->attributes($validated));
            $employee->syncRoles($roleId ? [Role::query()->findOrFail($roleId)] : []);
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
            'dataPolicies' => DataPolicy::query()->where('status', 1)->orderBy('name')->get(),
            'roles' => Role::query()->where('guard_name', 'tenant')->where('status', 1)->orderBy('name')->get(),
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
            'gender',
            'dob',
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
}
