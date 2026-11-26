<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\Area;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Location;
use App\Models\Tenant\Machine;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\StatusMuster;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Services\Tenant\PendingApprovalQuery;
use App\Support\TenantPermissions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantId = (string) tenant('id');
        $user = Auth::guard('tenant')->user();
        $userName = $user?->name ?: trim(implode(' ', array_filter([
            $user?->fname,
            $user?->mname,
            $user?->lname,
        ])));
        $today = now();
        $tomorrow = $today->copy()->addDay();
        $employeeStatusPeriod = request()->validate([
            'employee_status_period' => ['nullable', 'in:today,yesterday,this_week,last_week,this_month,last_month'],
        ])['employee_status_period'] ?? 'today';
        $activeEmployees = User::query()
            ->where('id', '!=', 1)
            ->whereNull('left_date')
            ->where('is_inactive', false);
        $birthdaysForDate = static function ($date) use ($activeEmployees) {
            return (clone $activeEmployees)
                ->whereMonth('dob', $date->month)
                ->whereDay('dob', $date->day)
                ->orderBy('id')
                ->get(['id', 'name', 'fname', 'mname', 'lname'])
                ->map(static function (User $employee): array {
                    $name = $employee->name ?: trim(implode(' ', array_filter([
                        $employee->fname,
                        $employee->mname,
                        $employee->lname,
                    ])));

                    return [
                        'id' => $employee->id,
                        'name' => $name !== '' ? $name : 'Employee '.$employee->id,
                    ];
                });
        };
        $birthdaysToday = $birthdaysForDate($today);
        $birthdaysTomorrow = $birthdaysForDate($tomorrow);
        $employees = User::query()
            ->with('areas:id')
            ->where('user_type', 'employee')
            ->whereNull('left_date')
            ->where('is_inactive', false)
            ->get([
                'id',
                'department_id',
                'company_id',
                'location_id',
                'sub_department_id',
                'category_id',
                'sub_category_id',
                'designation_id',
                'grade_id',
                'unit_id',
                'bus_route_id',
                'team_id',
                'canteen_facility_id',
                'name',
                'fname',
                'mname',
                'lname',
                'profile_pic',
            ]);
        $employeeIds = $employees->pluck('id');
        $employeeStatusData = $this->employeeStatusSummary($employees, $today, $employeeStatusPeriod);
        $dailyAttendanceByUser = Attendance::year((int) $today->year)
            ->whereDate('date', $today->toDateString())
            ->whereIn('user_id', $employeeIds)
            ->get(['user_id', 'in_time', 'out_time', 'is_holiday', 'is_weekoff'])
            ->keyBy('user_id');
        $dailyStatusByUser = StatusMuster::query()
            ->with('status_master:id,code')
            ->whereDate('date', $today->toDateString())
            ->whereIn('user_id', $employeeIds)
            ->get()
            ->groupBy('user_id')
            ->map(static fn ($statuses) => $statuses->sortByDesc('updated_at')->first());
        $attendanceStates = [];
        $nonWorkingStatusCodes = ['WO', 'WOP', 'WHP', 'HL', 'HLP', 'PH', 'PHL'];

        foreach ($employeeIds as $employeeId) {
            $attendance = $dailyAttendanceByUser->get($employeeId);
            $statusCode = $dailyStatusByUser->get($employeeId)?->status_master?->code;

            if ($attendance?->in_time && $attendance?->out_time) {
                $attendanceStates[$employeeId] = 'present';

                continue;
            }

            if (
                in_array($statusCode, $nonWorkingStatusCodes, true)
                || $attendance?->is_holiday
                || $attendance?->is_weekoff
            ) {
                continue;
            }

            if (in_array($statusCode, ['AA', 'AP'], true)) {
                $attendanceStates[$employeeId] = 'absent';
            }
        }
        $employeeGroupingDefinitions = [
            'department' => ['label' => 'Department', 'field' => 'department_id', 'model' => Department::class],
            'company' => ['label' => 'Company', 'field' => 'company_id', 'model' => Company::class],
            'location' => ['label' => 'Location', 'field' => 'location_id', 'model' => Location::class],
            'sub_department' => ['label' => 'Sub Department', 'field' => 'sub_department_id', 'model' => SubDepartment::class],
            'category' => ['label' => 'Category', 'field' => 'category_id', 'model' => Category::class],
            'sub_category' => ['label' => 'Sub Category', 'field' => 'sub_category_id', 'model' => SubCategory::class],
            'designation' => ['label' => 'Designation', 'field' => 'designation_id', 'model' => Designation::class],
            'grade' => ['label' => 'Grade', 'field' => 'grade_id', 'model' => Grade::class],
            'unit' => ['label' => 'Unit', 'field' => 'unit_id', 'model' => Unit::class],
            'bus_route' => ['label' => 'Bus Route', 'field' => 'bus_route_id', 'model' => BusRoute::class],
            'team' => ['label' => 'Team', 'field' => 'team_id', 'model' => Team::class],
            'canteen_facility' => ['label' => 'Canteen Facility', 'field' => 'canteen_facility_id', 'model' => CanteenFacility::class],
            'area' => ['label' => 'Area', 'relation' => 'areas', 'model' => Area::class],
        ];
        $employeeGroupings = [];
        foreach ($employeeGroupingDefinitions as $key => $definition) {
            $masters = $definition['model']::query()->orderBy('id')->get(['id', 'name']);
            $counts = array_fill_keys($masters->pluck('id')->map(static fn ($id): int => (int) $id)->all(), 0);
            $presentCounts = $counts;
            $absentCounts = $counts;
            $employeeIdsWithMaster = [];
            $presentEmployeeIdsWithMaster = [];
            $absentEmployeeIdsWithMaster = [];

            foreach ($employees as $employee) {
                $employeeMasterIds = isset($definition['relation'])
                    ? $employee->{$definition['relation']}->pluck('id')->all()
                    : $this->idsFrom($employee->{$definition['field']});

                foreach ($employeeMasterIds as $id) {
                    if (array_key_exists($id, $counts)) {
                        $counts[$id]++;
                        $employeeIdsWithMaster[$employee->id] = true;

                        if (($attendanceStates[$employee->id] ?? null) === 'present') {
                            $presentCounts[$id]++;
                            $presentEmployeeIdsWithMaster[$employee->id] = true;
                        } elseif (($attendanceStates[$employee->id] ?? null) === 'absent') {
                            $absentCounts[$id]++;
                            $absentEmployeeIdsWithMaster[$employee->id] = true;
                        }
                    }
                }
            }

            $employeeGroupings[$key] = [
                'label' => $definition['label'],
                'categories' => $masters->pluck('name')->all(),
                'data' => array_values($counts),
                'summaryCount' => count($employeeIdsWithMaster),
                'present' => array_values($presentCounts),
                'absent' => array_values($absentCounts),
                'summaryPresentCount' => count($presentEmployeeIdsWithMaster),
                'summaryAbsentCount' => count($absentEmployeeIdsWithMaster),
            ];
        }
        $canApprovePendingRequests = TenantPermissions::userCan('PendingRequest', 'read')
            && TenantPermissions::userCan('PendingRequest', 'approve');
        $applicationModules = array_values(array_filter([
            [
                'label' => 'Leave Application',
                'permission' => 'LeaveApplication',
                'path' => 'time-management/leave-applications/create',
            ],
            [
                'label' => 'COFF Application',
                'permission' => 'CoffApplication',
                'path' => 'time-management/coff-applications/create',
            ],
            [
                'label' => 'Manual Attendance Application',
                'permission' => 'ManualAttendanceApplication',
                'path' => 'time-management/manual-attendance-applications/create',
            ],
            [
                'label' => 'Manual Punch Application',
                'permission' => 'ManualPunchApplication',
                'path' => 'time-management/manual-punch-applications/create',
            ],
            [
                'label' => 'Overtime Application',
                'permission' => 'OvertimeApplication',
                'path' => 'time-management/overtime-applications/create',
            ],
            [
                'label' => 'Short Leave Application',
                'permission' => 'ShortLeaveApplication',
                'path' => 'time-management/short-leave-applications/create',
            ],
            [
                'label' => 'Shift Change Application',
                'permission' => 'ShiftChangeApplication',
                'path' => 'time-management/shift-change-applications/create',
            ],
            [
                'label' => 'Week Off Change Application',
                'permission' => 'WeekOffChangeApplication',
                'path' => 'time-management/week-off-change-applications/create',
            ],
            [
                'label' => 'Week Off Swap Application',
                'permission' => 'WeekOffSwapApplication',
                'path' => 'time-management/week-off-swap-applications/create',
            ],
        ], static fn (array $module): bool => TenantPermissions::userCan($module['permission'], 'create')));
        $structureSections = [];
        foreach ([
            'Company Structure' => [
                ['label' => 'Companies', 'permission' => 'Company', 'path' => 'company-structure/companies', 'icon' => 'ti-building', 'count' => static fn (): int => Company::query()->count()],
                ['label' => 'Locations', 'permission' => 'Location', 'path' => 'company-structure/locations', 'icon' => 'ti-map-pin', 'count' => static fn (): int => Location::query()->count()],
                ['label' => 'Departments', 'permission' => 'Department', 'path' => 'company-structure/departments', 'icon' => 'ti-sitemap', 'count' => static fn (): int => Department::query()->count()],
                ['label' => 'Sub Departments', 'permission' => 'SubDepartment', 'path' => 'company-structure/sub-departments', 'icon' => 'ti-hierarchy-2', 'count' => static fn (): int => SubDepartment::query()->count()],
                ['label' => 'Unit (Mill)', 'permission' => 'Unit', 'path' => 'company-structure/units', 'icon' => 'ti-building-factory', 'count' => static fn (): int => Unit::query()->count()],
                ['label' => 'Teams', 'permission' => 'Team', 'path' => 'company-structure/teams', 'icon' => 'ti-users-group', 'count' => static fn (): int => Team::query()->count()],
                ['label' => 'Canteen Facilities', 'permission' => 'CanteenFacility', 'path' => 'company-structure/canteen-facilities', 'icon' => 'ti-tools-kitchen-2', 'count' => static fn (): int => CanteenFacility::query()->count()],
                ['label' => 'Areas', 'permission' => 'Area', 'path' => 'company-structure/areas', 'icon' => 'ti-map-2', 'count' => static fn (): int => Area::query()->count()],
                ['label' => 'Machines', 'permission' => 'Machine', 'path' => 'company-structure/machines', 'icon' => 'ti-settings', 'count' => static fn (): int => Machine::query()->count()],
            ],
            'Employee Structure' => [
                ['label' => 'Categories', 'permission' => 'Category', 'path' => 'employee-structure/categories', 'icon' => 'ti-category', 'count' => static fn (): int => Category::query()->count()],
                ['label' => 'Designations', 'permission' => 'Designation', 'path' => 'employee-structure/designations', 'icon' => 'ti-id-badge', 'count' => static fn (): int => Designation::query()->count()],
                ['label' => 'Employees', 'permission' => 'User', 'path' => 'employee-structure/employees', 'icon' => 'ti-users', 'count' => static fn (): int => User::query()->where('user_type', 'employee')->count()],
                ['label' => 'Approval Flows', 'permission' => 'ApprovalFlow', 'path' => 'employee-structure/approval-flows', 'icon' => 'ti-git-branch', 'count' => static fn (): int => ApprovalFlow::query()->count()],
            ],
        ] as $sectionTitle => $modules) {
            $cards = [];
            foreach ($modules as $module) {
                if (! TenantPermissions::userCan($module['permission'], 'read')) {
                    continue;
                }

                $cards[] = [
                    'label' => $module['label'],
                    'path' => $module['path'],
                    'icon' => $module['icon'],
                    'count' => $module['count'](),
                ];
            }

            if ($cards !== []) {
                $structureSections[] = [
                    'title' => $sectionTitle,
                    'cards' => $cards,
                ];
            }
        }
        $userCount = User::query()->count();
        $attendanceCount = Attendance::year((int) now()->year)
            ->whereDate('date', today())
            ->whereNotNull('in_time')
            ->whereNotNull('out_time')
            ->whereIn('user_id', User::query()->select('id'))
            ->distinct()
            ->count('user_id');

        return view('index', array_merge([
            'tenantId' => $tenantId,
            'domain' => request()->getHost(),
            'currentUserId' => $user?->getAuthIdentifier() ?? 'anonymous',
            'currentUserName' => $userName !== '' ? $userName : 'User',
            'currentUserProfilePic' => $user?->profile_pic,
            'currentUserDob' => $user?->dob,
            'currentUserJoinDate' => $user?->join_date,
            'birthdaysToday' => $birthdaysToday,
            'birthdaysTomorrow' => $birthdaysTomorrow,
            'employeeGroupings' => $employeeGroupings,
            'canApprovePendingRequests' => $canApprovePendingRequests,
            'pendingApprovalCount' => $canApprovePendingRequests
                ? PendingApprovalQuery::forUser((int) Auth::guard('tenant')->id())->count()
                : null,
            'applicationModules' => $applicationModules,
            'structureSections' => $structureSections,
            'userCount' => $userCount,
            'attendanceCount' => $attendanceCount,
            'recentUsers' => User::query()->latest('id')->limit(5)->get(),
        ], $employeeStatusData));
    }

    public function employeeStatus(Request $request): JsonResponse
    {
        $period = $request->validate([
            'employee_status_period' => ['required', 'in:today,yesterday,this_week,last_week,this_month,last_month'],
        ])['employee_status_period'];
        $employees = User::query()
            ->where('user_type', 'employee')
            ->whereNull('left_date')
            ->where('is_inactive', false)
            ->get([
                'id',
                'name',
                'fname',
                'mname',
                'lname',
                'designation_id',
                'profile_pic',
            ]);

        return response()->json($this->employeeStatusSummary($employees, now(), $period));
    }

    /**
     * @return array{
     *     employeeStatusPeriod: string,
     *     totalEmployees: int,
     *     employeeStatusCounts: array<string, int>,
     *     employeeStatusPercentages: array<string, float>,
     *     topPerformers: \Illuminate\Support\Collection<int, array<string, mixed>>
     * }
     */
    private function employeeStatusSummary(EloquentCollection $employees, Carbon $today, string $period): array
    {
        [$startDate, $endDate] = match ($period) {
            'yesterday' => [$today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay()],
            'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'last_week' => [
                $today->copy()->startOfWeek()->subWeek(),
                $today->copy()->startOfWeek()->subWeek()->endOfWeek(),
            ],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'last_month' => [
                $today->copy()->startOfMonth()->subMonth(),
                $today->copy()->startOfMonth()->subMonth()->endOfMonth(),
            ],
            default => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
        };
        $employeeIds = $employees->pluck('id');
        $leaveStatusCodes = LeaveType::query()
            ->pluck('code')
            ->flatMap(static function (string $code): array {
                $code = strtoupper($code);

                return [$code, 'A'.$code, $code.'A', 'P'.$code, $code.'P'];
            })
            ->unique()
            ->all();
        $employeeStatusCounts = [
            'present' => 0,
            'partial_absent' => 0,
            'complete_absent' => 0,
            'leave' => 0,
        ];
        $employeeAttendancePerformance = [];
        $employeeStatusAttendance = collect();

        for ($attendanceYear = (int) $startDate->year; $attendanceYear <= (int) $endDate->year; $attendanceYear++) {
            $yearStartDate = $attendanceYear === (int) $startDate->year
                ? $startDate->toDateString()
                : $attendanceYear.'-01-01';
            $yearEndDate = $attendanceYear === (int) $endDate->year
                ? $endDate->toDateString()
                : $attendanceYear.'-12-31';

            $employeeStatusAttendance = $employeeStatusAttendance->concat(
                Attendance::year($attendanceYear)
                    ->whereBetween('date', [$yearStartDate, $yearEndDate])
                    ->whereIn('user_id', $employeeIds)
                    ->with('status_master:id,code')
                    ->get([
                        'user_id',
                        'status_master_id',
                        'in_time',
                        'out_time',
                        'is_half_day',
                        'is_absent',
                    ]),
            );
        }

        foreach ($employeeStatusAttendance as $attendance) {
            $userId = (int) $attendance->user_id;
            $statusCode = strtoupper((string) ($attendance->status_master?->code ?? ''));
            $employeeAttendancePerformance[$userId] ??= ['present' => 0, 'total' => 0];
            $employeeAttendancePerformance[$userId]['total']++;

            $isPresent = (
                str_starts_with($statusCode, 'P')
                || str_ends_with($statusCode, 'P')
            ) && ! $attendance->is_half_day && ! $attendance->is_absent;

            if ($isPresent) {
                $employeeStatusCounts['present']++;
                $employeeAttendancePerformance[$userId]['present']++;
            }

            if (
                (str_starts_with($statusCode, 'A') || str_ends_with($statusCode, 'A'))
                && $attendance->in_time !== null
            ) {
                $employeeStatusCounts['partial_absent']++;
            }

            if (
                $statusCode === 'AA'
                && $attendance->in_time === null
                && $attendance->out_time === null
            ) {
                $employeeStatusCounts['complete_absent']++;
            }

            if (in_array($statusCode, $leaveStatusCodes, true)) {
                $employeeStatusCounts['leave']++;
            }
        }

        $categoryTotal = array_sum($employeeStatusCounts);
        $employeeStatusPercentages = array_map(
            static fn (int $count): float => $categoryTotal > 0 ? $count / $categoryTotal * 100 : 0,
            $employeeStatusCounts,
        );
        $designationIds = $employees
            ->flatMap(fn (User $employee): array => $this->idsFrom($employee->designation_id))
            ->unique()
            ->values();
        $designationNames = Designation::query()
            ->whereIn('id', $designationIds)
            ->pluck('name', 'id');
        $designationNamesByEmployee = $employees->mapWithKeys(function (User $employee) use ($designationNames): array {
            $names = collect($this->idsFrom($employee->designation_id))
                ->map(static fn (int $id): ?string => $designationNames->get($id))
                ->filter()
                ->implode(', ');

            return [$employee->id => $names !== '' ? $names : 'Employee'];
        });
        $topPerformers = $employees
            ->filter(static fn (User $employee): bool => ($employeeAttendancePerformance[$employee->id]['present'] ?? 0) > 0)
            ->map(static function (User $employee) use ($employeeAttendancePerformance, $designationNamesByEmployee): array {
                $performance = $employeeAttendancePerformance[$employee->id];
                $name = $employee->name ?: trim(implode(' ', array_filter([
                    $employee->fname,
                    $employee->mname,
                    $employee->lname,
                ])));
                $profilePicUrl = $employee->profile_pic
                    ? (\Illuminate\Support\Str::startsWith($employee->profile_pic, ['http://', 'https://'])
                        ? $employee->profile_pic
                        : tenant_asset($employee->profile_pic))
                    : null;

                return [
                    'id' => $employee->id,
                    'name' => $name !== '' ? $name : 'Employee '.$employee->id,
                    'designation' => $designationNamesByEmployee->get($employee->id, 'Employee'),
                    'profile_pic_url' => $profilePicUrl,
                    'performance' => (int) round($performance['present'] / $performance['total'] * 100),
                    'present_days' => $performance['present'],
                ];
            })
            ->sort(static function (array $first, array $second): int {
                return ($second['performance'] <=> $first['performance'])
                    ?: ($second['present_days'] <=> $first['present_days'])
                    ?: strcmp($first['name'], $second['name']);
            })
            ->take(5)
            ->values();

        return [
            'employeeStatusPeriod' => $period,
            'totalEmployees' => $employees->count(),
            'employeeStatusCounts' => $employeeStatusCounts,
            'employeeStatusPercentages' => $employeeStatusPercentages,
            'topPerformers' => $topPerformers,
        ];
    }

    /**
     * @return array<int, int>
     */
    private function idsFrom(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_values(array_unique(array_map(
            'intval',
            array_filter(
                (array) $value,
                static fn (mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
            ),
        )));
    }
}
