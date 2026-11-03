<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreShortLeaveApplicationRequest;
use App\Http\Requests\Tenant\UpdateShortLeaveApplicationRequest;
use App\Jobs\Tenant\GenerateShortLeaveApplicationExport;
use App\Jobs\Tenant\ProcessShortLeaveApplicationImport;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Services\Tenant\ShortLeaveApplicationWorkflow;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShortLeaveApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/short-leave-applications';

    public function index(): View
    {
        return view('time-management.short-leave-applications.index', [
            'applications' => ShortLeaveApplication::query()
                ->with(['user', 'approval_statuses'])
                ->latest('date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.short-leave-applications.add', $this->formOptions());
    }

    public function clone(Request $request): View
    {
        $source = ShortLeaveApplication::query()
            ->with('user')
            ->findOrFail($request->route('short_leave_application'));
        $application = $source->replicate();
        $application->setRelation('user', $source->user);

        return view('time-management.short-leave-applications.add', $this->formOptions($application, true));
    }

    public function store(
        StoreShortLeaveApplicationRequest $request,
        ShortLeaveApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $workflow->createForUsers(
            $this->preparePayload($validated),
            $userIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Selected employees\' short leave applications submitted for approval.');
    }

    public function show(Request $request): View
    {
        $application = ShortLeaveApplication::query()
            ->with(['user', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'])
            ->findOrFail($request->route('short_leave_application'));

        return view('time-management.short-leave-applications.show', compact('application'));
    }

    public function edit(Request $request): View
    {
        $application = ShortLeaveApplication::query()
            ->with('user')
            ->findOrFail($request->route('short_leave_application'));

        return view('time-management.short-leave-applications.edit', $this->formOptions($application));
    }

    public function update(
        UpdateShortLeaveApplicationRequest $request,
        ShortLeaveApplicationWorkflow $workflow,
    ): RedirectResponse {
        $application = ShortLeaveApplication::query()
            ->findOrFail($request->route('short_leave_application'));
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if (count($userIds) !== 1) {
            throw ValidationException::withMessages([
                'users' => 'Select exactly one employee when editing a short leave application.',
            ]);
        }

        $workflow->update(
            $application,
            $this->preparePayload($validated) + ['user_id' => $userIds[0]],
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Short leave application updated and resubmitted for approval.');
    }

    public function destroy(Request $request, ShortLeaveApplicationWorkflow $workflow): RedirectResponse
    {
        $application = ShortLeaveApplication::query()
            ->findOrFail($request->route('short_leave_application'));
        $workflow->delete($application);

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Short leave application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'short_leave_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateShortLeaveApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Short leave application export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ShortLeaveApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_code' => 'Required; exact employee code.',
                'reason' => 'Optional; text up to 255 characters.',
                'date' => 'Required; date in YYYY-MM-DD format.',
                'from_time' => 'Required for type 3; time in HH:MM or HH:MM:SS format.',
                'to_time' => 'Required for type 3; later than from_time.',
                'minutes' => 'Required for types 1 and 2; positive whole number of minutes. Type 3 is calculated from its times.',
                'short_leave_type' => 'Required; 1 (Late Coming), 2 (Early Going), or 3 (Break Between Working Hours).',
                'approval_status' => 'Generated by the application; this column is ignored on import.',
            ]);
            fclose($handle);
        }, 'short_leave_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'short-leave-application-imports',
            'short_leave_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded short leave application import file.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessShortLeaveApplicationImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Short leave application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Ashort_leave_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'short-leave-application-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateShortLeaveApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'short_leave_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?ShortLeaveApplication $application = null, bool $isClone = false): array
    {
        return [
            'application' => $application,
            'isClone' => $isClone,
            'selectedUserIds' => $application?->user_id === null ? [] : [(string) $application->user_id],
            'users' => User::query()
                ->orderBy('code')
                ->get([
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
    private function preparePayload(array $validated): array
    {
        $type = (int) $validated['short_leave_type'];
        if ($type === 3) {
            $from = Carbon::createFromFormat('H:i', $validated['from_time']);
            $to = Carbon::createFromFormat('H:i', $validated['to_time']);
            $validated['minutes'] = (int) $from->diffInMinutes($to);
            $validated['from_time'] = $from->format('H:i:s');
            $validated['to_time'] = $to->format('H:i:s');
        } else {
            $validated['from_time'] = null;
            $validated['to_time'] = null;
        }

        $validated['reason'] = $validated['reason'] ?? null;
        unset($validated['users'], $validated['company_ids'], $validated['location_ids'], $validated['employee_filters']);

        return $validated;
    }
}
