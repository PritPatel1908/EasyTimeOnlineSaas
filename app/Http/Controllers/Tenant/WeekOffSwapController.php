<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreWeekOffSwapRequest;
use App\Jobs\Tenant\CreateWeekOffSwap;
use App\Jobs\Tenant\GenerateWeekOffSwapExport;
use App\Jobs\Tenant\ProcessWeekOffSwapImport;
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
use App\Models\Tenant\WeekOffSwap;
use App\Support\CsvImportTemplate;
use App\Support\WeekOffSwapMusterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WeekOffSwapController extends Controller
{
    private const INDEX_URL = 'time-management/week-off-swaps';

    public function index(): View
    {
        return view('time-management.week-off-swaps.index', [
            'weekOffSwaps' => WeekOffSwap::query()
                ->with('Users')
                ->latest('week_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.week-off-swaps.add', $this->formOptions(null));
    }

    public function store(StoreWeekOffSwapRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages(['users' => 'No employees match the selected filters.']);
        }
        $this->ensureWeekDateIsOffForUsers($userIds, $validated['week_date']);

        $payload = $this->preparePayload($validated);
        $payload['created_by'] = Auth::guard('tenant')->id();
        $payload['updated_by'] = Auth::guard('tenant')->id();
        $weekOffSwap = DB::transaction(function () use ($payload, $userIds): WeekOffSwap {
            $weekOffSwap = WeekOffSwap::query()->create($payload);
            $weekOffSwap->Users()->sync($userIds);

            return $weekOffSwap;
        });

        $this->dispatchSwap($weekOffSwap);

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off swap created successfully.');
    }

    public function show(Request $request): View
    {
        $weekOffSwap = WeekOffSwap::query()
            ->with('Users')
            ->findOrFail($request->route('week_off_swap'));

        return view('time-management.week-off-swaps.show', [
            'weekOffSwap' => $weekOffSwap,
        ]);
    }

    public function edit(Request $request): View
    {
        $weekOffSwap = WeekOffSwap::query()
            ->with('Users')
            ->findOrFail($request->route('week_off_swap'));

        return view('time-management.week-off-swaps.edit', $this->formOptions($weekOffSwap));
    }

    public function update(StoreWeekOffSwapRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages(['users' => 'No employees match the selected filters.']);
        }
        $weekOffSwap = WeekOffSwap::query()->findOrFail($request->route('week_off_swap'));
        $this->ensureWeekDateIsOffForUsers($userIds, $validated['week_date']);

        $payload = $this->preparePayload($validated);
        $payload['updated_by'] = Auth::guard('tenant')->id();

        DB::transaction(function () use ($weekOffSwap, $payload, $userIds): void {
            $weekOffSwap->update($payload);
            $weekOffSwap->Users()->sync($userIds);
        });

        $this->dispatchSwap($weekOffSwap->refresh());

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off swap updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $weekOffSwap = WeekOffSwap::query()->findOrFail($request->route('week_off_swap'));
        DB::transaction(function () use ($weekOffSwap): void {
            $weekOffSwap->Users()->detach();
            $weekOffSwap->deleted_by = Auth::guard('tenant')->id();
            $weekOffSwap->save();
            $weekOffSwap->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off swap deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'week_off_swaps_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateWeekOffSwapExport::dispatch($userId === null ? null : (int) $userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off swap export has started. You will receive a notification when the export is ready.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aweek_off_swaps_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'week-off-swap-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateWeekOffSwapExport($userId === null ? null : (int) $userId, $fileName))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'week_off_swaps.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }
            CsvImportTemplate::write($handle, WeekOffSwap::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'week_date' => 'Required; date in YYYY-MM-DD format.',
                'swap_date' => 'Required; date in YYYY-MM-DD format and different from week_date.',
                'reason' => 'Optional; text up to 255 characters.',
            ]);
            fclose($handle);
        }, 'week_off_swaps_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $file = $request->file('file');
        $storedPath = Storage::disk('local')->putFileAs(
            'week-off-swap-imports',
            $file,
            'week_off_swap_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded week-off swap CSV.');
        }
        $userId = Auth::guard('tenant')->id();
        ProcessWeekOffSwapImport::dispatch($userId === null ? null : (int) $userId, $storedPath)
            ->onConnection('database_tenant')
            ->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off swap import has started. You will receive a notification when it finishes.');
    }

    public function checkWeekOff(Request $request): JsonResponse
    {
        abort_unless(
            \App\Support\TenantPermissions::userCan('WeekOffSwap', 'create')
                || \App\Support\TenantPermissions::userCan('WeekOffSwap', 'write'),
            403,
        );

        $validated = $request->validate([
            'week_date' => ['required', 'date_format:Y-m-d'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);
        $users = User::query()->whereIn('id', $validated['users'])->get(['id', 'name', 'code']);
        abort_unless($users->count() === count($validated['users']), 403);

        $withoutWeekOff = WeekOffSwapMusterService::usersWithoutWeekOff(
            $users,
            $validated['week_date'],
        );

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
    private function formOptions(?WeekOffSwap $weekOffSwap): array
    {
        return [
            'weekOffSwap' => $weekOffSwap,
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
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function preparePayload(array $validated): array
    {
        unset($validated['employee_filters'], $validated['location_ids'], $validated['company_ids'], $validated['users']);
        $validated['reason'] = $validated['reason'] ?? null;

        return $validated;
    }

    /**
     * @param array<string, mixed> $validated
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

    private function dispatchSwap(WeekOffSwap $weekOffSwap): void
    {
        CreateWeekOffSwap::dispatch($weekOffSwap)
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();
    }

    /**
     * @param array<int, int> $userIds
     */
    private function ensureWeekDateIsOffForUsers(
        array $userIds,
        string $weekDate,
    ): void
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
