<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\WOTypeEnumn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreWeekOffChangeApplicationRequest;
use App\Jobs\Tenant\GenerateWeekOffChangeApplicationExport;
use App\Jobs\Tenant\ProcessWeekOffChangeApplicationImport;
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
use App\Models\Tenant\WeekOffChangeApplication;
use App\Services\Tenant\WeekOffChangeApplicationWorkflow;
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

class WeekOffChangeApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/week-off-change-applications';

    public function index(): View
    {
        return view('time-management.week-off-change-applications.index', [
            'applications' => WeekOffChangeApplication::query()
                ->with(['Users', 'WeekOffChangeDetails', 'approval_statuses'])
                ->latest('from_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.week-off-change-applications.add', $this->formOptions(null));
    }

    public function store(
        StoreWeekOffChangeApplicationRequest $request,
        WeekOffChangeApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $workflow->create(
            $this->preparePayload($validated),
            $userIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off change application submitted for approval.');
    }

    public function show(Request $request): View
    {
        $application = WeekOffChangeApplication::query()
            ->with(['Users', 'WeekOffChangeDetails', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'])
            ->findOrFail($request->route('week_off_change_application'));

        return view('time-management.week-off-change-applications.show', [
            'application' => $application,
            'weekDays' => $this->weekDays(),
            'weekOffTypes' => $this->weekOffTypes(),
            'employeesByApprovalFlow' => $application->Users->groupBy('week_off_change_approval_flow_id'),
        ]);
    }

    public function edit(Request $request): View
    {
        $application = WeekOffChangeApplication::query()
            ->with(['Users', 'WeekOffChangeDetails'])
            ->findOrFail($request->route('week_off_change_application'));

        return view('time-management.week-off-change-applications.edit', $this->formOptions($application));
    }

    public function update(
        StoreWeekOffChangeApplicationRequest $request,
        WeekOffChangeApplicationWorkflow $workflow,
    ): RedirectResponse {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $application = WeekOffChangeApplication::query()
            ->findOrFail($request->route('week_off_change_application'));
        $workflow->update(
            $application,
            $this->preparePayload($validated),
            $userIds,
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off change application updated and resubmitted for approval.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $application = WeekOffChangeApplication::query()
            ->findOrFail($request->route('week_off_change_application'));
        $application->Users()->detach();
        $application->WeekOffChangeDetails()->delete();
        $application->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off change application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'week_off_change_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateWeekOffChangeApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off change application export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, WeekOffChangeApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'from_date' => 'Required; date in YYYY-MM-DD format.',
                'is_forever' => 'Optional; use 1 for no end date or 0 otherwise.',
                'to_date' => 'Required unless is_forever is 1; YYYY-MM-DD format.',
                'reason' => 'Optional; text up to 255 characters.',
                'week_days' => 'Required; one weekday name or day number (0 Sunday to 6 Saturday) per detail, comma-separated.',
                'wo_types' => 'Required; one type (Full Day, First Half, Second Half, or 1, 2, 3) per detail, comma-separated.',
                'first_week' => 'Required; one Yes/No value per detail, comma-separated.',
                'second_week' => 'Required; one Yes/No value per detail, comma-separated.',
                'third_week' => 'Required; one Yes/No value per detail, comma-separated.',
                'fourth_week' => 'Required; one Yes/No value per detail, comma-separated.',
                'fifth_week' => 'Required; one Yes/No value per detail, comma-separated.',
                'approval_status' => 'Generated by the approval workflow; this column is ignored on import.',
            ]);
            fclose($handle);
        }, 'week_off_change_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);

        $file = $request->file('file');
        $storedPath = Storage::disk('local')->putFileAs(
            'week-off-change-application-imports',
            $file,
            'week_off_change_application_import_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension(),
        );
        if (! is_string($storedPath)) {
            throw new RuntimeException('Unable to store the uploaded week-off change application CSV.');
        }

        $userId = Auth::guard('tenant')->id();
        ProcessWeekOffChangeApplicationImport::dispatch(
            $userId === null ? null : (int) $userId,
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off change application import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aweek_off_change_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'week-off-change-application-exports/'.$fileName;

        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateWeekOffChangeApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'week_off_change_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?WeekOffChangeApplication $application): array
    {
        return [
            'weekOffChange' => $application,
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
            'weekDays' => $this->weekDays(),
            'weekOffTypes' => $this->weekOffTypes(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function preparePayload(array $validated): array
    {
        $isForever = (bool) ($validated['is_forever'] ?? false);
        unset(
            $validated['employee_filters'],
            $validated['location_ids'],
            $validated['company_ids'],
            $validated['users'],
        );
        $validated['is_forever'] = $isForever;
        $validated['to_date'] = $isForever ? null : $validated['to_date'];
        $validated['reason'] = $validated['reason'] ?? null;

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

    /**
     * @return array<int, string>
     */
    private function weekDays(): array
    {
        return [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function weekOffTypes(): array
    {
        return collect(WOTypeEnumn::cases())
            ->mapWithKeys(static fn (WOTypeEnumn $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
