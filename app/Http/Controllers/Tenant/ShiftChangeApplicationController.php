<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreShiftChangeApplicationRequest;
use App\Http\Requests\Tenant\UpdateShiftChangeApplicationRequest;
use App\Jobs\Tenant\GenerateShiftChangeApplicationExport;
use App\Jobs\Tenant\ProcessShiftChangeApplicationImport;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Services\Tenant\ShiftChangeApplicationWorkflow;
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

class ShiftChangeApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/shift-change-applications';

    public function index(): View
    {
        return view('time-management.shift-change-applications.index', [
            'applications' => ShiftChangeApplication::query()
                ->with(['Users', 'shifts', 'shift_rotation', 'approval_statuses'])
                ->latest('from_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request): View
    {
        $application = ShiftChangeApplication::query()
            ->with([
                'Users',
                'shifts',
                'shift_rotation',
                'approval_statuses.approval_flow',
                'approval_statuses.approval_status_details.user',
            ])
            ->findOrFail($request->route('shift_change_application'));

        return view('time-management.shift-change-applications.show', [
            'application' => $application,
            'employeesByApprovalFlow' => $application->Users->groupBy('shift_change_approval_flow_id'),
        ]);
    }

    public function create(): View
    {
        return view('time-management.shift-change-applications.add', $this->formOptions(null));
    }

    public function store(
        StoreShiftChangeApplicationRequest $request,
        ShiftChangeApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $shiftIds = array_map('intval', $validated['shift_ids'] ?? []);
        $workflow->create(
            $this->preparePayload($validated),
            $userIds,
            $shiftIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Shift change application submitted for approval.');
    }

    public function edit(Request $request): View
    {
        $application = ShiftChangeApplication::query()
            ->with(['Users', 'shifts'])
            ->findOrFail($request->route('shift_change_application'));

        return view('time-management.shift-change-applications.edit', $this->formOptions($application));
    }

    public function update(
        UpdateShiftChangeApplicationRequest $request,
        ShiftChangeApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $application = ShiftChangeApplication::query()
            ->findOrFail($request->route('shift_change_application'));
        $workflow->update(
            $application,
            $this->preparePayload($validated),
            $userIds,
            array_map('intval', $validated['shift_ids'] ?? []),
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Shift change application updated and resubmitted for approval.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $application = ShiftChangeApplication::query()
            ->findOrFail($request->route('shift_change_application'));
        $application->Users()->detach();
        $application->shifts()->detach();
        $application->deleted_by = Auth::guard('tenant')->id();
        $application->save();
        $application->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Shift change application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'shift_change_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateShiftChangeApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Shift change application export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ShiftChangeApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'shift_type' => 'Required; auto, fixed, or rotational.',
                'shifts' => 'Required for auto/fixed; shift codes separated by commas.',
                'shift_rotation_code' => 'Required for rotational shift type.',
                'from_date' => 'Required; date in YYYY-MM-DD format.',
                'is_forever' => 'Optional; use 1 for no end date or 0 otherwise.',
                'to_date' => 'Required unless is_forever is 1; YYYY-MM-DD format.',
                'reason' => 'Optional; text up to 255 characters.',
                'approval_status' => 'Generated by the application; this column is ignored on import.',
            ]);
            fclose($handle);
        }, 'shift_change_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $file = $request->file('file');
        $storedPath = Storage::disk('local')->putFileAs(
            'shift-change-application-imports',
            $file,
            'shift_change_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded shift change application CSV.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessShiftChangeApplicationImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Shift change application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Ashift_change_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'shift-change-application-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateShiftChangeApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'shift_change_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?ShiftChangeApplication $application): array
    {
        return [
            'shiftChange' => $application,
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
            'shifts' => Shift::query()->orderBy('name')->get(['id', 'name', 'code']),
            'shiftRotations' => ShiftRotation::query()->orderBy('code')->get(['id', 'code']),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function preparePayload(array $validated): array
    {
        $validated['shift_rotation_id'] = $validated['shift_type'] === 'rotational'
            ? (int) $validated['shift_rotation_id']
            : null;
        $validated['is_forever'] = (bool) ($validated['is_forever'] ?? false);
        $validated['to_date'] = $validated['is_forever'] ? null : $validated['to_date'];
        $validated['reason'] = $validated['reason'] ?? null;
        unset(
            $validated['employee_filters'],
            $validated['location_ids'],
            $validated['company_ids'],
            $validated['shift_ids'],
            $validated['users'],
        );

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
