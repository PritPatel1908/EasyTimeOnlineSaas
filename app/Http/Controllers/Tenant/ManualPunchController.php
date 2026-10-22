<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreManualPunchRequest;
use App\Http\Requests\Tenant\UpdateManualPunchRequest;
use App\Jobs\Tenant\GenerateManualPunchExport;
use App\Jobs\Tenant\ProcessManualPunch;
use App\Jobs\Tenant\ProcessManualPunchImport;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualPunchController extends Controller
{
    private const INDEX_URL = 'time-management/manual-punches';

    public function index(): View
    {
        return view('time-management.manual-punches.index', [
            'punches' => ManualPunch::query()
                ->with('Users')
                ->latest('punch_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $punch = ManualPunch::query()
            ->with('Users')
            ->findOrFail($request->route('manual_punch'));

        return view('time-management.manual-punches.show', [
            'punch' => $punch,
        ]);
    }

    public function create(): View
    {
        return view('time-management.manual-punches.add', $this->formOptions(null));
    }

    public function store(StoreManualPunchRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }
        $validated = $this->preparePayload($validated);
        unset($validated['users']);
        $validated['created_by'] = Auth::guard('tenant')->id();
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $punch = DB::transaction(function () use ($validated, $userIds): ManualPunch {
            $punch = ManualPunch::query()->create($validated);
            $punch->Users()->sync($userIds);

            return $punch;
        });

        ProcessManualPunch::dispatch((int) $punch->id)
            ->onConnection('database_tenant')
            ->onQueue('processing');

        return redirect(url(self::INDEX_URL))->with('success', 'Manual punch created successfully.');
    }

    public function edit(Request $request): View
    {
        $punch = ManualPunch::query()
            ->with('Users')
            ->findOrFail($request->route('manual_punch'));

        return view('time-management.manual-punches.edit', $this->formOptions($punch));
    }

    public function update(UpdateManualPunchRequest $request): RedirectResponse
    {
        $validated = $this->preparePayload($request->validated());
        $userIds = $validated['users'];
        unset($validated['users']);
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $punch = ManualPunch::query()->findOrFail($request->route('manual_punch'));
        DB::transaction(function () use ($punch, $validated, $userIds): void {
            $punch->update($validated);
            $punch->Users()->sync($userIds);
        });

        ProcessManualPunch::dispatch((int) $punch->id)
            ->onConnection('database_tenant')
            ->onQueue('processing');

        return redirect(url(self::INDEX_URL))->with('success', 'Manual punch updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $punch = ManualPunch::query()->findOrFail($request->route('manual_punch'));

        DB::transaction(function () use ($punch): void {
            $punch->Users()->detach();
            $punch->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Manual punch deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'manual_punches_' . now()->format('Ymd_His') . '.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateManualPunchExport::dispatch($userId === null ? null : (int) $userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual punch export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ManualPunch::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by semicolons.',
                'location' => 'Optional; location names, codes or IDs separated by semicolons.',
                'company' => 'Optional; company names, codes or IDs separated by semicolons.',
                'punch_type' => 'Required; use 1 (In Punch), 2 (Out Punch), 3 (Break Start), or 4 (Break End).',
                'punch_date' => 'Required; date in YYYY-MM-DD format.',
                'punch_time' => 'Required; time in HH:MM or HH:MM:SS format.',
                'reason' => 'Optional; text up to 255 characters.',
            ]);
            fclose($handle);
        }, 'manual_punches_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'manual-punch-imports',
            'manual_punch_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension(),
        );

        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded manual punch import file.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessManualPunchImport::dispatch($userId === null ? null : (int) $userId, $storedPath)
            ->onConnection('database_tenant')
            ->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual punch import has started. You will receive a notification when the import finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Amanual_punches_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'manual-punch-exports/' . $fileName;

        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateManualPunchExport($userId === null ? null : (int) $userId, $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'manual_punches.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?ManualPunch $punch): array
    {
        return [
            'punch' => $punch,
            'users' => User::query()->orderBy('code')->get([
                'id',
                'name',
                'code',
                'location_id',
                'company_id',
                'department_id',
                'sub_department_id',
                'category_id',
                'sub_category_id',
                'designation_id',
                'grade_id',
                'unit_id',
                'bus_route_id',
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
        $validated['punch_date'] = $validated['punch_date'] . ' 00:00:00';
        $validated['punch_time'] = strlen($validated['punch_time']) === 5
            ? $validated['punch_time'] . ':00'
            : $validated['punch_time'];
        $validated['reason'] = $validated['reason'] ?? null;
        unset($validated['location_ids'], $validated['company_ids']);
        unset($validated['employee_filters']);

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

}
