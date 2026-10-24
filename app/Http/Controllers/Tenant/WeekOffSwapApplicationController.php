<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreWeekOffSwapApplicationRequest;
use App\Jobs\Tenant\GenerateWeekOffSwapApplicationExport;
use App\Jobs\Tenant\ProcessWeekOffSwapApplicationImport;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Services\Tenant\WeekOffSwapApplicationWorkflow;
use App\Support\CsvImportTemplate;
use App\Support\WeekOffSwapMusterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WeekOffSwapApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/week-off-swap-applications';

    public function index(): View
    {
        return view('time-management.week-off-swap-applications.index', [
            'applications' => WeekOffSwapApplication::query()
                ->with(['Users', 'approval_statuses'])
                ->latest('week_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $application = WeekOffSwapApplication::query()
            ->with(['Users', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'])
            ->findOrFail($request->route('week_off_swap_application'));

        return view('time-management.week-off-swap-applications.show', [
            'application' => $application,
            'employeesByApprovalFlow' => $application->Users->groupBy('week_off_swap_approval_flow_id'),
        ]);
    }

    public function create(): View
    {
        return view('time-management.week-off-swap-applications.add', $this->formOptions(null));
    }

    public function store(
        StoreWeekOffSwapApplicationRequest $request,
        WeekOffSwapApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }
        $this->ensureWeekDateIsOffForUsers($userIds, $validated['week_date']);

        $workflow->create(
            $this->preparePayload($validated),
            $userIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off swap application submitted for approval.');
    }

    public function edit(Request $request): View
    {
        $application = WeekOffSwapApplication::query()
            ->with('Users')
            ->findOrFail($request->route('week_off_swap_application'));

        return view('time-management.week-off-swap-applications.edit', $this->formOptions($application));
    }

    public function update(
        StoreWeekOffSwapApplicationRequest $request,
        WeekOffSwapApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }
        $application = WeekOffSwapApplication::query()
            ->findOrFail($request->route('week_off_swap_application'));
        $this->ensureWeekDateIsOffForUsers($userIds, $validated['week_date']);

        $workflow->update(
            $application,
            $this->preparePayload($validated),
            $userIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off swap application updated and resubmitted for approval.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $application = WeekOffSwapApplication::query()
            ->findOrFail($request->route('week_off_swap_application'));
        DB::transaction(function () use ($application): void {
            $application->Users()->detach();
            $application->deleted_by = Auth::guard('tenant')->id();
            $application->save();
            $application->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off swap application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'week_off_swap_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateWeekOffSwapApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off swap application export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, WeekOffSwapApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'week_date' => 'Required; date in YYYY-MM-DD format.',
                'swap_date' => 'Required; date in YYYY-MM-DD format and different from week_date.',
                'reason' => 'Optional; text up to 255 characters.',
                'approval_status' => 'Generated by the application; this column is ignored on import.',
            ]);
            fclose($handle);
        }, 'week_off_swap_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $file = $request->file('file');
        $storedPath = Storage::disk('local')->putFileAs(
            'week-off-swap-application-imports',
            $file,
            'week_off_swap_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded week-off swap application CSV.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessWeekOffSwapApplicationImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off swap application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aweek_off_swap_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'week-off-swap-application-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateWeekOffSwapApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'week_off_swap_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function checkWeekOff(Request $request): JsonResponse
    {
        abort_unless(
            \App\Support\TenantPermissions::userCan('WeekOffSwapApplication', 'create')
                || \App\Support\TenantPermissions::userCan('WeekOffSwapApplication', 'write'),
            403,
        );

        $validated = $request->validate([
            'week_date' => ['required', 'date_format:Y-m-d'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);
        $users = User::query()->whereIn('id', $validated['users'])->get(['id', 'name', 'code']);
        abort_unless($users->count() === count($validated['users']), 403);
        $withoutWeekOff = WeekOffSwapMusterService::usersWithoutWeekOff($users, $validated['week_date']);

        return response()->json([
            'has_week_off' => $withoutWeekOff->isEmpty(),
            'week_date' => $validated['week_date'],
            'employees_without_week_off' => $withoutWeekOff->map(static fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'code' => $user->code,
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?WeekOffSwapApplication $application): array
    {
        return [
            'weekOffSwapApplication' => $application,
            'users' => User::query()->orderBy('code')->get([
                'id', 'name', 'code', 'location_id', 'company_id', 'department_id',
                'sub_department_id', 'category_id', 'sub_category_id', 'designation_id',
                'grade_id', 'unit_id', 'bus_route_id',
            ]),
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
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function preparePayload(array $validated): array
    {
        unset($validated['employee_filters'], $validated['location_ids'], $validated['company_ids'], $validated['users']);
        $validated['reason'] = $validated['reason'] ?? null;

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function resolveUserIds(array $validated): array
    {
        $selectedUsers = $validated['users'] ?? [];
        if ($selectedUsers !== []) {
            return array_values(array_map('intval', $selectedUsers));
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
     * @param  array<int, int>  $userIds
     */
    private function ensureWeekDateIsOffForUsers(array $userIds, string $weekDate): void
    {
        $users = User::query()->whereIn('id', $userIds)->get(['id', 'name', 'code']);
        if ($users->count() !== count($userIds)) {
            throw ValidationException::withMessages(['users' => 'One or more selected employees are not available.']);
        }
        $withoutWeekOff = WeekOffSwapMusterService::usersWithoutWeekOff($users, $weekDate);
        if ($withoutWeekOff->isNotEmpty()) {
            $names = $withoutWeekOff
                ->map(static fn (User $user): string => $user->name.($user->code ? ' ('.$user->code.')' : ''))
                ->implode(', ');
            throw ValidationException::withMessages([
                'week_date' => 'The selected date is not a week-off for: '.$names.'.',
            ]);
        }
    }
}
