<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreManualPunchApplicationRequest;
use App\Http\Requests\Tenant\UpdateManualPunchApplicationRequest;
use App\Jobs\Tenant\GenerateManualPunchApplicationExport;
use App\Jobs\Tenant\ProcessManualPunchImport;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualPunchApplication;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Services\Tenant\ManualPunchApplicationWorkflow;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualPunchApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/manual-punch-applications';

    public function index(): View
    {
        return view('time-management.manual-punch-applications.index', [
            'applications' => ManualPunchApplication::query()
                ->with(['Users', 'approval_statuses'])
                ->latest('punch_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $application = ManualPunchApplication::query()
            ->with(['Users', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'])
            ->findOrFail($request->route('manual_punch_application'));

        return view('time-management.manual-punch-applications.show', [
            'application' => $application,
        ]);
    }

    public function create(): View
    {
        return view('time-management.manual-punch-applications.add', $this->formOptions(null));
    }

    public function store(
        StoreManualPunchApplicationRequest $request,
        ManualPunchApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $payload = $this->preparePayload($validated);
        $workflow->create($payload, $userIds, (int) Auth::guard('tenant')->id());

        return redirect(url(self::INDEX_URL))->with('success', 'Manual punch application submitted for approval.');
    }

    public function edit(Request $request): View
    {
        $application = ManualPunchApplication::query()
            ->with('Users')
            ->findOrFail($request->route('manual_punch_application'));

        return view('time-management.manual-punch-applications.edit', $this->formOptions($application));
    }

    public function update(
        UpdateManualPunchApplicationRequest $request,
        ManualPunchApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $application = ManualPunchApplication::query()
            ->findOrFail($request->route('manual_punch_application'));
        $workflow->update(
            $application,
            $this->preparePayload($validated),
            $userIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Manual punch application updated and resubmitted for approval.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $application = ManualPunchApplication::query()
            ->findOrFail($request->route('manual_punch_application'));
        $application->Users()->detach();
        $application->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Manual punch application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'manual_punch_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateManualPunchApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual punch application export has started. You will receive a notification when the export is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ManualPunchApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'punch_type' => 'Required; use 1 (In Punch), 2 (Out Punch), 3 (Break Start), or 4 (Break End).',
                'punch_date' => 'Required; date in YYYY-MM-DD format.',
                'punch_time' => 'Required; time in HH:MM or HH:MM:SS format.',
                'reason' => 'Optional; text up to 255 characters.',
                'approval_status' => 'Generated by the application; this column is ignored on import.',
            ]);
            fclose($handle);
        }, 'manual_punch_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'manual-punch-application-imports',
            'manual_punch_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded manual punch application import file.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessManualPunchImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
            true,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual punch application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Amanual_punch_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'manual-punch-application-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateManualPunchApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'manual_punch_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?ManualPunchApplication $application): array
    {
        return [
            'punch' => $application,
            'lockEmployees' => false,
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
        $validated['punch_time'] = strlen($validated['punch_time']) === 5
            ? $validated['punch_time'].':00'
            : $validated['punch_time'];
        $validated['reason'] = $validated['reason'] ?? null;
        unset($validated['users'], $validated['location_ids'], $validated['company_ids'], $validated['employee_filters']);

        return $validated;
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
}
