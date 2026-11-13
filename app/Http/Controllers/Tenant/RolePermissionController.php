<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    private const ACTIONS = ['read', 'write', 'create', 'delete', 'import', 'export'];

    private static function getModules(): array
    {
        return [
            'AbsentRule',
            'ActivityLog',
            'Announcement',
            'ApprovalFlow',
            'Area',
            'Attendance',
            'AttendanceLog',
            'BusRoute',
            'Category',
            'CanteenFacility',
            'Coff',
            'CoffApplication',
            'Company',
            'DailyReport',
            'DataPolicy',
            'Department',
            'Designation',
            'DmsSetting',
            'EarlyGoingRule',
            'EmailConfiguration',
            'User',
            'FinancialYear',
            'GeneralConfiguration',
            'Grade',
            'GradeWiseLeave',
            'HalfDayRule',
            'Holiday',
            'InOutMuster',
            'LateComingRule',
            'LeaveAccount',
            'LeaveAccountDetail',
            'LeaveApplication',
            'LeaveEncashment',
            'LeaveReason',
            'LeaveType',
            'License',
            'Location',
            'Machine',
            'ManualAttendance',
            'ManualAttendanceApplication',
            'ManualLeaveBalance',
            'ManualPunch',
            'ManualPunchApplication',
            'MonthlyReport',
            'OutDuty',
            'Overtime',
            'OvertimeApplication',
            'OvertimeReason',
            'OvertimeRule',
            'PasswordPolicy',
            'Role',
            'RotationMuster',
            'Setting',
            'Shift',
            'ShiftChange',
            'ShiftChangeApplication',
            'ShiftMuster',
            'ShiftRotation',
            'ShortLeaveApplication',
            'SubCategory',
            'SubDepartment',
            'Team',
            'Transaction',
            'Unit',
            'WeekOffChange',
            'WeekOffChangeApplication',
            'WeekOffMuster',
            'WeekOffSwap',
            'WeekOffSwapApplication',
            'YearlyReport',
        ];
    }

    public function index(): View
    {
        return view('tenant.roles-permissions.index', [
            'roles' => Role::query()
                ->where('guard_name', 'tenant')
                ->withCount('users')
                ->with('permissions:id,name')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request, string $tenant): RedirectResponse
    {
        $validated = $request->validateWithBag('createRole', [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->where(fn($query) => $query->where('guard_name', 'tenant')),
            ],
            'status' => ['required', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:150'],
        ]);

        $connection = DB::connection('tenant');
        $permissions = $validated['permissions'] ?? [];

        $connection->transaction(function () use ($connection, $validated, $permissions): void {
            $newRole = new Role([
                'name' => $validated['name'],
                'guard_name' => 'tenant',
                'status' => $validated['status'],
            ]);
            $newRole->setConnection($connection->getName());

            if ($connection->getDriverName() === 'sqlsrv') {
                $grammar = $connection->getQueryGrammar();
                $now = now();
                $sql = sprintf(
                    'INSERT INTO %s (%s, %s, %s, %s, %s) OUTPUT INSERTED.%s VALUES (?, ?, ?, ?, ?)',
                    $grammar->wrapTable(config('permission.table_names.roles')),
                    $grammar->wrap('name'),
                    $grammar->wrap('guard_name'),
                    $grammar->wrap('status'),
                    $grammar->wrap('created_at'),
                    $grammar->wrap('updated_at'),
                    $grammar->wrap('id')
                );
                $insertedRole = $connection->selectOne($sql, [
                    $validated['name'],
                    'tenant',
                    $validated['status'],
                    $now,
                    $now,
                ], false);
                $roleId = $insertedRole?->id;

                if ($roleId === null) {
                    throw new \RuntimeException('SQL Server did not return the ID of the newly created tenant role.');
                }

                $newRole->setRawAttributes([
                    'id' => $roleId,
                    'name' => $validated['name'],
                    'guard_name' => 'tenant',
                    'status' => $validated['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], true);
                $newRole->exists = true;
                $newRole->wasRecentlyCreated = true;

                ActivityLogger::log(
                    Auth::guard('tenant')->user(),
                    $newRole,
                    [],
                    $newRole->getAttributes(),
                    'created'
                );
            } else {
                $newRole->save();
            }

            $this->syncPermissions($newRole, $permissions);
        });

        return redirect()->route('tenant.roles.index', ['tenant' => $tenant])->with('success', 'Role created successfully.');
    }

    public function update(Request $request, string $tenant, Role $role): RedirectResponse
    {
        $this->ensureTenantRole($role);

        $validated = $request->validateWithBag('editRole' . $role->id, [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->id)->where(fn($query) => $query->where('guard_name', 'tenant')),
            ],
            'status' => ['required', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:150'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        if (array_key_exists('permissions', $validated)) {
            $this->syncPermissions($role, $validated['permissions']);
        }

        return redirect()->route('tenant.roles.index', ['tenant' => $tenant])->with('success', 'Role updated successfully.');
    }

    public function destroy(string $tenant, Role $role): RedirectResponse
    {
        $this->ensureTenantRole($role);

        if ($role->users()->exists()) {
            return back()->with('error', 'This role is assigned to users and cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('tenant.roles.index', ['tenant' => $tenant])->with('success', 'Role deleted successfully.');
    }

    public function permissions(string $tenant, Role $role): View
    {
        $this->ensureTenantRole($role);

        return view('tenant.roles-permissions.permissions', [
            'role' => $role->load('permissions:id,name'),
            'modules' => self::getModules(),
            'actions' => self::ACTIONS,
            'customPermissions' => [
                'Pending Request' => [
                    'module' => 'pendingrequest',
                    'actions' => ['read', 'approve', 'reject'],
                ],
                'Approved Request' => [
                    'module' => 'approvedrequest',
                    'actions' => ['read', 'cancel'],
                ],
                'Rejected Request' => [
                    'module' => 'rejectedrequest',
                    'actions' => ['read'],
                ],
                'Cancelled Request' => [
                    'module' => 'cancelledrequest',
                    'actions' => ['read'],
                ],
                'Upcoming Request' => [
                    'module' => 'upcomingrequest',
                    'actions' => ['read'],
                ],
            ],
        ]);
    }

    public function updatePermissions(Request $request, string $tenant, Role $role): RedirectResponse
    {
        $this->ensureTenantRole($role);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:150'],
        ]);
        $this->syncPermissions($role, $validated['permissions'] ?? []);

        return redirect()->route('tenant.roles.index', ['tenant' => $tenant])->with('success', 'Permissions updated successfully.');
    }

    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $oldPermissions = $role->permissions()->pluck('name')->sort()->values()->all();
        $permissionNames = collect($permissionNames)
            ->filter(fn($permission) => preg_match('/^[a-z0-9-]+\.[a-z]+$/', (string) $permission) === 1)
            ->unique()
            ->values();

        $connectionName = $role->getConnection()->getName();
        $permissions = $permissionNames->map(function (string $name) use ($connectionName): Permission {
            return Permission::on($connectionName)->firstOrCreate([
                'name' => $name,
                'guard_name' => 'tenant',
            ]);
        });

        $role->syncPermissions($permissions);

        $newPermissions = $permissionNames->all();
        if ($oldPermissions !== $newPermissions) {
            ActivityLogger::log(
                Auth::guard('tenant')->user(),
                $role,
                ['permissions' => $oldPermissions],
                ['permissions' => $newPermissions],
                'permissions_updated'
            );
        }
    }

    private function ensureTenantRole(Role $role): void
    {
        abort_unless($role->guard_name === 'tenant', 404);
    }
}
