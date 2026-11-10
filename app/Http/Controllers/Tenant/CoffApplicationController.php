<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\GenerateCoffApplicationExport;
use App\Jobs\Tenant\ProcessCoffApplicationImport;
use App\Models\Tenant\Category;
use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\User;
use App\Services\Tenant\CoffApplicationWorkflow;
use App\Services\Tenant\CoffApplicationPolicy;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoffApplicationController extends Controller
{
    private const INDEX_URL = 'time-management/coff-applications';

    public function index(): View
    {
        return view('time-management.coff-applications.index', [
            'applications' => CoffApplication::query()
                ->with(['user', 'approval_statuses'])
                ->latest('coff_against_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.coff-applications.add', $this->formOptions());
    }

    public function eligibleDates(Request $request, CoffApplicationPolicy $policy): JsonResponse
    {
        $validated = $request->validate($this->employeeSelectionRules());
        $userIds = $this->resolveUserIds($validated);
        $applicationId = $request->route('coff_application');

        return response()->json([
            'dates' => $policy->eligibleDates(
                $userIds,
                is_numeric($applicationId) ? (int) $applicationId : null,
            ),
        ]);
    }

    public function validateLive(Request $request, CoffApplicationPolicy $policy): JsonResponse
    {
        $validated = $request->validate($this->employeeSelectionRules() + [
            'coff_against_date' => ['required', 'date_format:Y-m-d'],
            'coff_from_date' => ['required', 'date_format:Y-m-d'],
            'coff_to_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:coff_from_date'],
            'is_half_day' => ['nullable', 'boolean'],
            'is_first_half' => ['nullable', 'boolean'],
            'is_second_half' => ['nullable', 'boolean'],
        ]);
        $userIds = $this->resolveUserIds($validated);
        $errors = [];
        $applicationId = $request->route('coff_application');
        $excludeApplicationId = is_numeric($applicationId) ? (int) $applicationId : null;

        if ($userIds === []) {
            $errors['users'] = ['Select employees or at least one employee filter.'];
        } else {
            foreach ($userIds as $userId) {
                try {
                    $policy->validate(
                        $validated,
                        [$userId],
                        $excludeApplicationId,
                    );
                } catch (ValidationException $exception) {
                    foreach ($exception->errors() as $field => $messages) {
                        $errors[$field] = array_values(array_unique(array_merge($errors[$field] ?? [], $messages)));
                    }
                }
            }
        }

        return response()->json([
            'valid' => $errors === [],
            'errors' => $errors,
        ]);
    }

    public function store(Request $request, CoffApplicationWorkflow $workflow): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'Select employees or at least one employee filter.',
            ]);
        }
        $workflow->createForUsers($this->applicationPayload($validated), $userIds, (int) Auth::guard('tenant')->id());

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Selected employees\' COFF applications submitted for approval.');
    }

    public function show(Request $request): View
    {
        $application = CoffApplication::query()
            ->with(['user', 'approval_statuses.approval_flow', 'approval_statuses.approval_status_details.user'])
            ->findOrFail($this->applicationId($request));

        return view('time-management.coff-applications.show', compact('application'));
    }

    public function edit(Request $request): View
    {
        $application = CoffApplication::query()
            ->with('user')
            ->findOrFail($this->applicationId($request));
        abort_if($application->status === 'approved', 409, 'Approved COFF applications cannot be changed.');

        return view('time-management.coff-applications.edit', $this->formOptions($application));
    }

    public function update(Request $request, CoffApplicationWorkflow $workflow): RedirectResponse
    {
        $application = CoffApplication::query()->findOrFail($this->applicationId($request));
        abort_if($application->status === 'approved', 409, 'Approved COFF applications cannot be changed.');
        $validated = $this->validatedData($request);
        $userIds = $this->resolveUserIds($validated);
        if (count($userIds) !== 1) {
            throw ValidationException::withMessages([
                'users' => 'Select exactly one employee when editing a COFF application.',
            ]);
        }
        $workflow->update(
            $application,
            $this->applicationPayload($validated) + ['user_id' => $userIds[0]],
            (int) Auth::guard('tenant')->id(),
        );

        return redirect(url(self::INDEX_URL))->with('success', 'COFF application updated and resubmitted for approval.');
    }

    public function destroy(Request $request, CoffApplicationWorkflow $workflow): RedirectResponse
    {
        $application = CoffApplication::query()->findOrFail($this->applicationId($request));
        abort_if($application->status === 'approved', 409, 'Approved COFF applications cannot be deleted.');
        $workflow->delete($application, (int) Auth::guard('tenant')->id());

        return redirect(url(self::INDEX_URL))->with('success', 'COFF application deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'coff_applications_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateCoffApplicationExport::dispatch(
            $userId === null ? null : (int) $userId,
            $fileName,
        )->onConnection('database_tenant')->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'COFF application export started. You will be notified when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, CoffApplication::IMPORT_EXPORT_COLUMNS, [
                'employee_code' => 'Required; exact employee code with a COFF approval flow.',
                'coff_against_date' => 'Required; date in YYYY-MM-DD format.',
                'coff_from_date' => 'Required; date in YYYY-MM-DD format.',
                'coff_to_date' => 'Required; on or after coff_from_date.',
                'is_half_day' => 'Optional; Yes/No or 1/0. Half-day applications must cover one date and select exactly one half.',
                'is_first_half' => 'Optional; Yes/No or 1/0. For a half day, CO in the first half (COA); otherwise use for the range end.',
                'is_second_half' => 'Optional; Yes/No or 1/0. For a half day, CO in the second half (ACO); otherwise use for the range start.',
                'approval_status' => 'Generated by the application; ignored on import.',
            ]);
            fclose($handle);
        }, 'coff_applications_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $file = $request->file('file');
        $storedPath = $file->storeAs(
            'coff-application-imports',
            'coff_applications_'.now()->format('Ymd_His').'_'.bin2hex(random_bytes(5)).'.csv',
            'local',
        );
        if ($storedPath === false) {
            throw new RuntimeException('Unable to store the COFF application import file.');
        }

        ProcessCoffApplicationImport::dispatch(
            (int) Auth::guard('tenant')->id(),
            $storedPath,
        )->onConnection('database_tenant')->onQueue('import');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'COFF application import started. You will be notified when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Acoff_applications_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'coff-application-exports/'.$fileName;
        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateCoffApplicationExport(
                $userId === null ? null : (int) $userId,
                $fileName,
            ))->handle();
        }
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'coff_applications.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    private function applicationId(Request $request): int
    {
        $id = filter_var($request->route('coff_application'), FILTER_VALIDATE_INT);
        abort_unless($id !== false && $id > 0, 404);

        return $id;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?CoffApplication $application = null): array
    {
        $users = User::query()->orderBy('code')->get([
            'id', 'name', 'code', 'category_id',
        ]);
        $categoryIds = $users->flatMap(static fn (User $user): array => (array) $user->category_id)
            ->filter(static fn (mixed $id): bool => is_numeric($id))
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();
        $categoriesById = Category::query()
            ->whereIn('id', $categoryIds)
            ->get(['id', 'allow_halfday_c_off'])
            ->keyBy('id');
        $halfDayAllowedByUser = $users->mapWithKeys(static function (User $user) use ($categoriesById): array {
            $userCategories = collect((array) $user->category_id)
                ->map(static fn (mixed $id): ?Category => is_numeric($id) ? $categoriesById->get((int) $id) : null)
                ->filter(static fn (?Category $category): bool => $category !== null);

            return [(int) $user->id => $userCategories->isNotEmpty()
                && $userCategories->every(static fn (Category $category): bool => (bool) $category->allow_halfday_c_off)];
        })->all();

        return [
            'application' => $application,
            'users' => $users,
            'halfDayAllowedByUser' => $halfDayAllowedByUser,
            'selectedUserIds' => $application === null ? [] : [(string) $application->user_id],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate($this->employeeSelectionRules() + [
            'coff_against_date' => ['required', 'date_format:Y-m-d'],
            'coff_from_date' => ['required', 'date_format:Y-m-d'],
            'coff_to_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:coff_from_date'],
            'is_half_day' => ['nullable', 'boolean'],
            'is_first_half' => ['nullable', 'boolean'],
            'is_second_half' => ['nullable', 'boolean'],
        ]);

        $validated['is_half_day'] = $request->boolean('is_half_day');
        $validated['is_first_half'] = $request->boolean('is_first_half');
        $validated['is_second_half'] = $request->boolean('is_second_half');
        $sameDate = $validated['coff_from_date'] === $validated['coff_to_date'];

        if ($validated['is_half_day'] && (! $sameDate || $validated['is_first_half'] === $validated['is_second_half'])) {
            throw ValidationException::withMessages([
                'is_half_day' => 'A half-day COFF must cover one date and select exactly one half.',
            ]);
        }

        if (! $validated['is_half_day'] && $sameDate && ($validated['is_first_half'] || $validated['is_second_half'])) {
            throw ValidationException::withMessages([
                'is_half_day' => 'For a single full day, leave both half selectors unchecked.',
            ]);
        }

        return $validated;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function employeeSelectionRules(): array
    {
        return [
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
    private function applicationPayload(array $validated): array
    {
        return [
            'coff_against_date' => $validated['coff_against_date'],
            'coff_from_date' => $validated['coff_from_date'],
            'coff_to_date' => $validated['coff_to_date'],
            'is_half_day' => $validated['is_half_day'],
            'is_first_half' => $validated['is_first_half'],
            'is_second_half' => $validated['is_second_half'],
        ];
    }
}
