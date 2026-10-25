<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\GenerateLeaveApplicationExport;
use App\Jobs\Tenant\ProcessLeaveApplicationImport;
use App\Jobs\Tenant\ReverseLeaveApplicationAccountBalance;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\GradeWiseLeave;
use App\Models\Tenant\GradeWiseMonthlyLeaveDetail;
use App\Models\Tenant\GradeWiseYearlyLeaveDetail;
use App\Models\Tenant\Location;
use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\LeaveReason;
use App\Models\Tenant\LeaveType;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Services\Tenant\LeaveApplicationApprovalWorkflow;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/leave-applications';

    public function index(): View
    {
        return view('time-management.leave-applications.index', [
            'applications' => LeaveApplication::query()
                ->with(['users', 'leave_type', 'leave_reason'])
                ->latest('application_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.leave-applications.add', $this->formOptions(null));
    }

    public function store(
        Request $request,
        LeaveApplicationApprovalWorkflow $approvalWorkflow,
    ): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'users' => 'Select employees or at least one employee filter.',
            ]);
        }
        $this->ensureGradeWiseLeaveIsAssigned($userIds);
        $this->ensureLeaveTypeIsAvailableForEmployees($validated['leave_type_id'] ?? null, $userIds);
        $this->ensureLeaveRequestMeetsPolicies($validated, $userIds);
        $actorId = Auth::guard('tenant')->id();
        $approvalFlowIds = $approvalWorkflow->approvalFlowIds($userIds);

        DB::transaction(function () use ($validated, $userIds, $actorId, $approvalFlowIds, $approvalWorkflow): void {
            $application = LeaveApplication::query()->create($this->applicationPayload($validated, $actorId, true));
            $application->users()->sync($userIds);
            $approvalWorkflow->dispatch($application, $approvalFlowIds, (int) $actorId);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Leave application created successfully.');
    }

    public function show(Request $request): View
    {
        $application = LeaveApplication::query()
            ->with([
                'users',
                'leave_type',
                'leave_reason',
                'approval_statuses.approval_flow',
                'approval_statuses.approval_status_details.user',
            ])
            ->findOrFail($this->applicationId($request));

        return view('time-management.leave-applications.show', compact('application'));
    }

    public function edit(Request $request): View
    {
        $application = LeaveApplication::query()
            ->with('users')
            ->findOrFail($this->applicationId($request));

        return view('time-management.leave-applications.edit', $this->formOptions($application));
    }

    public function update(
        Request $request,
        LeaveApplicationApprovalWorkflow $approvalWorkflow,
    ): RedirectResponse
    {
        $applicationId = $this->applicationId($request);
        $validated = $this->validatedData($request);
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'users' => 'Select employees or at least one employee filter.',
            ]);
        }
        $this->ensureGradeWiseLeaveIsAssigned($userIds);
        $this->ensureLeaveTypeIsAvailableForEmployees($validated['leave_type_id'] ?? null, $userIds);
        $this->ensureLeaveRequestMeetsPolicies($validated, $userIds, $applicationId);
        $actorId = Auth::guard('tenant')->id();
        $approvalFlowIds = $approvalWorkflow->approvalFlowIds($userIds);

        DB::transaction(function () use (
            $validated,
            $userIds,
            $actorId,
            $applicationId,
            $approvalFlowIds,
            $approvalWorkflow,
        ): void {
            $application = LeaveApplication::query()->findOrFail($applicationId);
            $application->update($this->applicationPayload($validated, $actorId, false));
            $application->users()->sync($userIds);
            $approvalWorkflow->dispatch($application, $approvalFlowIds, (int) $actorId);
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Leave application updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $applicationId = $this->applicationId($request);
        $actorId = Auth::guard('tenant')->id();

        DB::transaction(function () use ($applicationId, $actorId): void {
            $application = LeaveApplication::query()->findOrFail($applicationId);
            $userIds = $application->users()
                ->pluck('users.id')
                ->map(static fn (mixed $userId): int => (int) $userId)
                ->all();
            ReverseLeaveApplicationAccountBalance::dispatch(
                (int) $application->id,
                $userIds,
                $actorId === null ? null : (int) $actorId,
                false,
            )->onConnection('database_tenant')->onQueue('processing')->afterCommit();
            $application->users()->detach();
            $application->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Leave application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'leave_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateLeaveApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Leave application export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, LeaveApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'leave_type_code' => 'Optional; leave type code.',
                'leave_reason' => 'Optional; exact leave reason text.',
                'reason_explanation' => 'Optional; text up to 255 characters.',
                'is_only_half' => 'Optional; Yes/No or 1/0.',
                'from_date' => 'Required; date in YYYY-MM-DD format.',
                'is_second_half' => 'Optional; Yes/No or 1/0.',
                'application_date' => 'Required; YYYY-MM-DD HH:MM:SS.',
                'to_date' => 'Required; date in YYYY-MM-DD format.',
                'is_first_half' => 'Optional; Yes/No or 1/0.',
                'leave_count' => 'Optional; non-negative decimal leave days.',
                'status' => 'Optional; Active/Inactive or 1/0.',
            ]);
            fclose($handle);
        }, 'leave_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $file = $request->file('file');
        $storedPath = Storage::disk('local')->putFileAs(
            'leave-application-imports',
            $file,
            'leave_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded leave application CSV.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessLeaveApplicationImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Leave application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aleave_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'leave-application-exports/'.$fileName;

        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateLeaveApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'leave_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    private function applicationId(Request $request): int
    {
        $routeId = $request->route('leave_application');
        $applicationId = filter_var($routeId, FILTER_VALIDATE_INT);
        abort_unless($applicationId !== false && $applicationId > 0, 404);

        return $applicationId;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?LeaveApplication $application): array
    {
        $leavePoliciesByGroup = GradeWiseLeave::query()
            ->with([
                'grade_wise_monthly_leave_details',
                'grade_wise_yearly_leave_details',
            ])
            ->get(['id', 'is_monthly'])
            ->mapWithKeys(static function (GradeWiseLeave $group): array {
                $details = $group->is_monthly
                    ? $group->grade_wise_monthly_leave_details
                    : $group->grade_wise_yearly_leave_details;

                return [
                    (string) $group->id => $details
                        ->mapWithKeys(static fn (GradeWiseMonthlyLeaveDetail|GradeWiseYearlyLeaveDetail $detail): array => [
                            (string) $detail->leave_type_id => [
                                'keeps_balance' => (bool) $detail->keeps_balance,
                                'allow_negative_balance' => (bool) $detail->allow_negative_balance,
                                'negative_balance' => (float) ($detail->negative_balance ?? 0),
                                'allow_backdate_leave' => (bool) ($detail->allow_backdate_leave ?? false),
                                'backdate_day_limit' => $detail->backdate_day_limit,
                                'allow_advance_leave' => (bool) ($detail->allow_advance_leave ?? false),
                                'advance_day_limit' => $detail->advance_day_limit,
                                'maximum_request_in_a_month' => (int) ($detail->maximum_request_in_a_month ?? 0),
                                'maximum_request_in_a_year' => (int) ($detail->maximum_request_in_a_year ?? 0),
                                'eligible_after_days' => (int) ($detail->eligible_after_days ?? 0),
                                'leave_types_not_to_be_clubbed' => $detail->leave_types_not_to_be_clubbed ?? [],
                            ],
                        ])
                        ->all(),
                ];
            })
            ->all();

        $users = User::query()
            ->with([
                'grade_wise_leave:id,is_monthly',
                'leave_account.leave_account_details',
            ])
            ->orderBy('code')
            ->get([
                'id', 'name', 'code', 'location_id', 'company_id', 'department_id',
                'sub_department_id', 'category_id', 'sub_category_id', 'designation_id',
                'grade_id', 'grade_wise_leave_id', 'unit_id', 'bus_route_id',
            ]);
        $leaveBalancesByUser = $users->mapWithKeys(static function (User $user): array {
            $balances = $user->leave_account?->leave_account_details
                ->mapWithKeys(static fn ($detail): array => [
                    (string) $detail->leave_type_id => (float) $detail->balance,
                ])
                ->all() ?? [];

            return [(string) $user->id => $balances];
        })->all();

        return [
            'application' => $application,
            'leavePoliciesByGroup' => $leavePoliciesByGroup,
            'leaveBalancesByUser' => $leaveBalancesByUser,
            'leaveTypesByGroup' => collect($leavePoliciesByGroup)
                ->map(static fn (array $policies): array => array_keys($policies))
                ->all(),
            'users' => $users,
            'leaveTypes' => LeaveType::query()->orderBy('code')->get(['id', 'code', 'description']),
            'leaveReasons' => LeaveReason::query()->orderBy('leave_reason')->get(['id', 'leave_reason']),
            'selectedUserIds' => $application?->users->pluck('id')->map(static fn ($id): string => (string) $id)->all() ?? [],
            'locations' => Location::query()->orderBy('name')->get(['id', 'name', 'code']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'subDepartments' => SubDepartment::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'subCategories' => SubCategory::query()->orderBy('name')->get(['id', 'name']),
            'designations' => Designation::query()->orderBy('name')->get(['id', 'name']),
            'grades' => Grade::query()->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->orderBy('name')->get(['id', 'name']),
            'busRoutes' => BusRoute::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function ensureGradeWiseLeaveIsAssigned(array $userIds): void
    {
        $employeesWithoutLeaveGroup = User::query()
            ->whereIn('id', $userIds)
            ->whereNull('grade_wise_leave_id')
            ->pluck('name');

        if ($employeesWithoutLeaveGroup->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'users' => 'Grade-wise leave is not assigned to: '.$employeesWithoutLeaveGroup->implode(', '),
            ]);
        }
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private function ensureLeaveTypeIsAvailableForEmployees(mixed $leaveTypeId, array $userIds): void
    {
        if ($leaveTypeId === null) {
            return;
        }

        $groupIds = User::query()
            ->whereIn('id', $userIds)
            ->pluck('grade_wise_leave_id')
            ->filter()
            ->unique();
        $groups = GradeWiseLeave::query()
            ->whereIn('id', $groupIds)
            ->get(['id', 'is_monthly']);
        $monthlyGroupIds = $groups->where('is_monthly', true)->pluck('id');
        $yearlyGroupIds = $groups->where('is_monthly', false)->pluck('id');

        $eligibleGroupIds = collect();
        if ($monthlyGroupIds->isNotEmpty()) {
            $eligibleGroupIds = $eligibleGroupIds->merge(
                GradeWiseMonthlyLeaveDetail::query()
                    ->whereIn('grade_wise_leave_id', $monthlyGroupIds)
                    ->where('leave_type_id', $leaveTypeId)
                    ->pluck('grade_wise_leave_id'),
            );
        }
        if ($yearlyGroupIds->isNotEmpty()) {
            $eligibleGroupIds = $eligibleGroupIds->merge(
                GradeWiseYearlyLeaveDetail::query()
                    ->whereIn('grade_wise_leave_id', $yearlyGroupIds)
                    ->where('leave_type_id', $leaveTypeId)
                    ->pluck('grade_wise_leave_id'),
            );
        }

        $unavailableGroupIds = $groupIds
            ->diff($groups->pluck('id'))
            ->merge($groups->pluck('id')->diff($eligibleGroupIds));
        if ($unavailableGroupIds->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'leave_type_id' => 'The selected leave type is not available for all selected employees.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, int>  $userIds
     */
    private function ensureLeaveRequestMeetsPolicies(array $validated, array $userIds, ?int $excludeApplicationId = null): void
    {
        $leaveTypeId = $validated['leave_type_id'] ?? null;
        if ($leaveTypeId === null) {
            return;
        }

        $fromDate = Carbon::parse($validated['from_date'])->startOfDay();
        $toDate = Carbon::parse($validated['to_date'])->startOfDay();
        $today = Carbon::today();
        $leaveCount = $this->calculateLeaveCount(
            $validated['from_date'],
            $validated['to_date'],
            (bool) ($validated['is_only_half'] ?? false),
            (bool) ($validated['is_first_half'] ?? false),
            (bool) ($validated['is_second_half'] ?? false),
        );
        if ($leaveCount <= 0) {
            $this->throwLeavePolicyError('from_date', 'The selected half-day options produce a zero-day leave request.');
        }
        $users = User::query()
            ->whereIn('id', $userIds)
            ->with([
                'grade_wise_leave.grade_wise_monthly_leave_details',
                'grade_wise_leave.grade_wise_yearly_leave_details',
                'leave_account.leave_account_details',
            ])
            ->get()
            ->keyBy('id');
        $periodStart = $fromDate->copy()->startOfYear();
        $periodEnd = $toDate->copy()->endOfYear();
        $existingApplications = DB::table('leave_application_users')
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_users.leave_application_id')
            ->whereIn('leave_application_users.user_id', $userIds)
            ->whereNull('leave_applications.deleted_at')
            ->where('leave_applications.status', 1)
            ->whereDate('leave_applications.to_date', '>=', $periodStart->toDateString())
            ->whereDate('leave_applications.from_date', '<=', $periodEnd->toDateString())
            ->when($excludeApplicationId !== null, fn ($query) => $query->where('leave_applications.id', '!=', $excludeApplicationId))
            ->select([
                'leave_application_users.user_id',
                'leave_applications.leave_type_id',
                'leave_applications.from_date',
                'leave_applications.to_date',
            ])
            ->get()
            ->groupBy('user_id');
        $weekOffHolidayCounts = DB::table('status_musters')
            ->join('status_masters', 'status_masters.id', '=', 'status_musters.status_master_id')
            ->whereIn('status_musters.user_id', $userIds)
            ->whereBetween('status_musters.date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereIn('status_masters.code', ['WO', 'HL'])
            ->groupBy('status_musters.user_id')
            ->selectRaw('status_musters.user_id, COUNT(*) as day_count')
            ->pluck('day_count', 'user_id');

        foreach ($userIds as $userId) {
            $user = $users->get($userId);
            $group = $user?->grade_wise_leave;
            $monthly = (bool) $group?->is_monthly;
            $details = $monthly
                ? $group?->grade_wise_monthly_leave_details
                : $group?->grade_wise_yearly_leave_details;
            $policy = $details?->firstWhere('leave_type_id', $leaveTypeId);
            if ($policy === null) {
                continue;
            }

            if ($monthly) {
                if (! $policy->allow_backdate_leave && $fromDate->lt($today)) {
                    $this->throwLeavePolicyError('from_date', 'This leave type does not allow backdated leave.');
                }
                if ($policy->allow_backdate_leave && (int) $policy->backdate_day_limit > 0
                    && $fromDate->lt($today->copy()->subDays((int) $policy->backdate_day_limit))) {
                    $this->throwLeavePolicyError('from_date', 'The selected date is outside the allowed backdated leave limit.');
                }
                if (! $policy->allow_advance_leave && $toDate->gt($today)) {
                    $this->throwLeavePolicyError('to_date', 'This leave type does not allow advance leave.');
                }
                if ($policy->allow_advance_leave && (int) $policy->advance_day_limit > 0
                    && $toDate->gt($today->copy()->addDays((int) $policy->advance_day_limit))) {
                    $this->throwLeavePolicyError('to_date', 'The selected date is outside the allowed advance leave limit.');
                }
            }

            $eligibleAfterDays = (int) ($policy->eligible_after_days ?? 0);
            if ($eligibleAfterDays > 0 && $user?->join_date !== null
                && $fromDate->lt(Carbon::parse($user->join_date)->startOfDay()->addDays($eligibleAfterDays))) {
                $this->throwLeavePolicyError('from_date', 'The employee is not yet eligible for this leave type.');
            }

            $userApplications = $existingApplications->get($userId, collect());
            $forbiddenTypes = array_map('intval', (array) ($policy->leave_types_not_to_be_clubbed ?? []));
            foreach ($userApplications as $existing) {
                if ((int) $existing->leave_type_id === (int) $leaveTypeId) {
                    continue;
                }

                $existingFrom = Carbon::parse($existing->from_date)->startOfDay();
                $existingTo = Carbon::parse($existing->to_date)->startOfDay();
                if ($existingFrom->gt($toDate) || $existingTo->lt($fromDate)) {
                    continue;
                }

                $existingPolicy = $details?->firstWhere('leave_type_id', $existing->leave_type_id);
                $existingForbiddenTypes = array_map(
                    'intval',
                    (array) ($existingPolicy?->leave_types_not_to_be_clubbed ?? []),
                );
                if (in_array((int) $existing->leave_type_id, $forbiddenTypes, true)
                    || in_array((int) $leaveTypeId, $existingForbiddenTypes, true)) {
                    $this->throwLeavePolicyError('leave_type_id', 'This leave type cannot be clubbed with another leave already requested for the selected dates.');
                }
            }

            $monthLimit = (int) ($policy->maximum_request_in_a_month ?? 0);
            $yearLimit = (int) ($policy->maximum_request_in_a_year ?? 0);
            for ($month = $fromDate->copy()->startOfMonth(); $month->lte($toDate); $month->addMonth()) {
                if ($monthLimit > 0 && $this->countApplicationsInPeriod(
                    $userApplications,
                    (int) $leaveTypeId,
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ) >= $monthLimit) {
                    $this->throwLeavePolicyError('from_date', 'The employee has reached the maximum leave requests allowed in a month.');
                }
            }
            for ($year = $fromDate->copy()->startOfYear(); $year->lte($toDate); $year->addYear()) {
                if ($yearLimit > 0 && $this->countApplicationsInPeriod(
                    $userApplications,
                    (int) $leaveTypeId,
                    $year->copy()->startOfYear(),
                    $year->copy()->endOfYear(),
                ) >= $yearLimit) {
                    $this->throwLeavePolicyError('from_date', 'The employee has reached the maximum leave requests allowed in a year.');
                }
            }

            if ($policy->keeps_balance) {
                $accountBalance = (float) ($user?->leave_account?->leave_account_details
                    ?->firstWhere('leave_type_id', $leaveTypeId)?->balance ?? 0);
                $availableBalance = $accountBalance;
                $balanceLeaveCount = $leaveCount;
                if (! ($validated['is_only_half'] ?? false)
                    && ! ($policy->allow_sandwich && (int) $policy->sandwich_leave_id === (int) $leaveTypeId)) {
                    $balanceLeaveCount = max(0, $balanceLeaveCount - (int) ($weekOffHolidayCounts->get($userId) ?? 0));
                }
                $negativeLimit = abs((float) ($policy->negative_balance ?? 0));
                if ((! $policy->allow_negative_balance || $negativeLimit > 0)
                    && $balanceLeaveCount > $availableBalance + ($policy->allow_negative_balance ? $negativeLimit : 0)) {
                    $this->throwLeavePolicyError('from_date', 'The requested leave exceeds the employee\'s available leave balance.');
                }
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $applications
     */
    private function countApplicationsInPeriod(\Illuminate\Support\Collection $applications, int $leaveTypeId, Carbon $periodStart, Carbon $periodEnd): int
    {
        return $applications->filter(static function (object $application) use ($leaveTypeId, $periodStart, $periodEnd): bool {
            return (int) $application->leave_type_id === $leaveTypeId
                && Carbon::parse($application->from_date)->startOfDay()->lte($periodEnd)
                && Carbon::parse($application->to_date)->startOfDay()->gte($periodStart);
        })->count();
    }

    private function throwLeavePolicyError(string $field, string $message): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([$field => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'users' => ['nullable', 'array'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'employee_filters' => ['nullable', 'array:department_id,sub_department_id,category_id,sub_category_id,designation_id,grade_id,unit_id,bus_route_id'],
            'employee_filters.department_id' => ['nullable', 'array'],
            'employee_filters.department_id.*' => ['integer', 'distinct', 'exists:departments,id'],
            'employee_filters.sub_department_id' => ['nullable', 'array'],
            'employee_filters.sub_department_id.*' => ['integer', 'distinct', 'exists:sub_departments,id'],
            'employee_filters.category_id' => ['nullable', 'array'],
            'employee_filters.category_id.*' => ['integer', 'distinct', 'exists:categories,id'],
            'employee_filters.sub_category_id' => ['nullable', 'array'],
            'employee_filters.sub_category_id.*' => ['integer', 'distinct', 'exists:sub_categories,id'],
            'employee_filters.designation_id' => ['nullable', 'array'],
            'employee_filters.designation_id.*' => ['integer', 'distinct', 'exists:designations,id'],
            'employee_filters.grade_id' => ['nullable', 'array'],
            'employee_filters.grade_id.*' => ['integer', 'distinct', 'exists:grades,id'],
            'employee_filters.unit_id' => ['nullable', 'array'],
            'employee_filters.unit_id.*' => ['integer', 'distinct', 'exists:units,id'],
            'employee_filters.bus_route_id' => ['nullable', 'array'],
            'employee_filters.bus_route_id.*' => ['integer', 'distinct', 'exists:bus_routes,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'distinct', 'exists:locations,id'],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', 'distinct', 'exists:companies,id'],
            'leave_type_id' => ['nullable', 'integer', 'exists:leave_types,id'],
            'leave_reason_id' => ['nullable', 'integer', 'exists:leave_reasons,id'],
            'reason_explanation' => ['nullable', 'string', 'max:255'],
            'is_only_half' => ['sometimes', 'boolean'],
            'from_date' => ['required', 'date'],
            'is_second_half' => ['sometimes', 'boolean'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'is_first_half' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function resolveUserIds(array $validated): array
    {
        if (($validated['users'] ?? []) !== []) {
            return array_values(array_map('intval', $validated['users']));
        }

        $filters = [
            'company_id' => $validated['company_ids'] ?? [],
            'location_id' => $validated['location_ids'] ?? [],
            'department_id' => $validated['employee_filters']['department_id'] ?? [],
            'sub_department_id' => $validated['employee_filters']['sub_department_id'] ?? [],
            'category_id' => $validated['employee_filters']['category_id'] ?? [],
            'sub_category_id' => $validated['employee_filters']['sub_category_id'] ?? [],
            'designation_id' => $validated['employee_filters']['designation_id'] ?? [],
            'grade_id' => $validated['employee_filters']['grade_id'] ?? [],
            'unit_id' => $validated['employee_filters']['unit_id'] ?? [],
            'bus_route_id' => $validated['employee_filters']['bus_route_id'] ?? [],
        ];
        $filters = array_filter($filters, static fn (mixed $ids): bool => is_array($ids) && $ids !== []);
        if ($filters === []) {
            return [];
        }

        $query = User::query();
        foreach ($filters as $column => $ids) {
            $query->where(function ($query) use ($column, $ids): void {
                foreach ($ids as $id) {
                    $query->orWhereJsonContains($column, (int) $id);
                    $query->orWhereJsonContains($column, (string) (int) $id);
                }
            });
        }

        return $query->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applicationPayload(array $validated, mixed $actorId, bool $creating): array
    {
        $payload = [
            'leave_type_id' => $validated['leave_type_id'] ?? null,
            'leave_reason_id' => $validated['leave_reason_id'] ?? null,
            'reason_explanation' => $validated['reason_explanation'] ?? null,
            'is_only_half' => (bool) ($validated['is_only_half'] ?? false),
            'from_date' => $validated['from_date'],
            'is_second_half' => (bool) ($validated['is_second_half'] ?? false),
            'to_date' => $validated['to_date'],
            'is_first_half' => (bool) ($validated['is_first_half'] ?? false),
            'leave_count' => $this->calculateLeaveCount(
                $validated['from_date'],
                $validated['to_date'],
                (bool) ($validated['is_only_half'] ?? false),
                (bool) ($validated['is_first_half'] ?? false),
                (bool) ($validated['is_second_half'] ?? false),
            ),
            $creating ? 'created_by' : 'updated_by' => $actorId,
        ];

        if ($creating) {
            $payload['application_date'] = now();
            $payload['status'] = 1;
        }

        return $payload;
    }

    private function calculateLeaveCount(
        string $fromDate,
        string $toDate,
        bool $isOnlyHalf,
        bool $isFirstHalf,
        bool $isSecondHalf,
    ): float {
        if ($isOnlyHalf) {
            return 0.5;
        }

        $leaveCount = Carbon::parse($fromDate)->startOfDay()->diffInDays(
            Carbon::parse($toDate)->startOfDay(),
        ) + 1;

        if ($isFirstHalf) {
            $leaveCount -= 0.5;
        }
        if ($isSecondHalf) {
            $leaveCount -= 0.5;
        }

        return max(0, round($leaveCount, 2));
    }
}
