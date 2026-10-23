<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreManualShiftChangeRequest;
use App\Http\Requests\Tenant\UpdateManualShiftChangeRequest;
use App\Jobs\Tenant\CreateShiftMuster;
use App\Jobs\Tenant\GenerateManualShiftChangeExport;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\Shift;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\ShiftRotation;
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
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManualShiftChangeController extends Controller
{
    private const INDEX_URL = 'time-management/manual-shift-changes';

    public function export(): RedirectResponse
    {
        $fileName = 'manual_shift_changes_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateManualShiftChangeExport::dispatch($userId === null ? null : (int) $userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Manual shift change export has started. You will receive a notification when the export is ready.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Amanual_shift_changes_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'manual-shift-change-exports/'.$fileName;

        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateManualShiftChangeExport($userId === null ? null : (int) $userId, $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'manual_shift_changes.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new RuntimeException('Unable to open the CSV output stream.');
            }

            CsvImportTemplate::write($handle, ShiftChange::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'shift_type' => 'Required; auto, fixed, or rotational.',
                'shifts' => 'Required for auto/fixed; shift codes separated by commas.',
                'shift_rotation_code' => 'Required for rotational shift type.',
                'from_date' => 'Required; date in YYYY-MM-DD format.',
                'is_forever' => 'Optional; use 1 for no end date or 0 otherwise.',
                'to_date' => 'Required unless is_forever is 1; YYYY-MM-DD format.',
                'reason' => 'Optional; text up to 255 characters.',
            ]);
            fclose($handle);
        }, 'manual_shift_changes_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);
        $path = $request->file('file')->getRealPath();
        $handle = is_string($path) ? fopen($path, 'r') : false;
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded manual shift change CSV.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);

            return redirect(url(self::INDEX_URL))->with('error', 'The manual shift change CSV is empty.');
        }
        $headers = array_map(static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header), $headers);
        $required = ['employee_codes', 'shift_type', 'from_date'];
        if (array_diff($required, $headers) !== []) {
            fclose($handle);

            return redirect(url(self::INDEX_URL))->with('error', 'The CSV must include employee_codes, shift_type, and from_date columns.');
        }

        $created = 0;
        $errors = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (CsvImportTemplate::isHintRow($values) || collect($values)->every(static fn ($value): bool => trim((string) $value) === '')) {
                continue;
            }
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }

            [$payload, $userIds, $shiftIds, $validationErrors] = $this->resolveImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row '.$line.': '.implode(', ', $validationErrors);
                continue;
            }

            $shiftChange = DB::transaction(function () use ($payload, $userIds, $shiftIds): ShiftChange {
                $shiftChange = ShiftChange::query()->create($payload);
                $shiftChange->Users()->sync($userIds);
                $shiftChange->shifts()->sync($shiftIds);

                return $shiftChange;
            });
            CreateShiftMuster::dispatch($shiftChange)
                ->onConnection('database_tenant')
                ->onQueue('processing')
                ->afterCommit();
            $created++;
        }
        fclose($handle);

        $message = $errors === []
            ? "Imported {$created} manual shift change record(s)."
            : 'Imported '.$created.' manual shift change record(s) with '.count($errors).' error(s): '.implode(' | ', array_slice($errors, 0, 3));

        return redirect(url(self::INDEX_URL))->with($errors === [] ? 'success' : 'error', $message);
    }

    public function index(): View
    {
        return view('time-management.manual-shift-changes.index', [
            'shiftChanges' => ShiftChange::query()
                ->with(['Users', 'shifts', 'shift_rotation'])
                ->latest('from_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.manual-shift-changes.add', $this->formOptions(null));
    }

    public function store(StoreManualShiftChangeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $shiftIds = $validated['shift_ids'] ?? [];
        $validated = $this->preparePayload($validated);
        $validated['created_by'] = Auth::guard('tenant')->id();
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $shiftChange = DB::transaction(function () use ($validated, $userIds, $shiftIds): ShiftChange {
            $shiftChange = ShiftChange::query()->create($validated);
            $shiftChange->Users()->sync($userIds);
            $shiftChange->shifts()->sync($shiftIds);

            return $shiftChange;
        });

        CreateShiftMuster::dispatch($shiftChange)
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();

        return redirect(url(self::INDEX_URL))->with('success', 'Manual shift change created successfully.');
    }

    public function show(Request $request): View
    {
        $shiftChange = ShiftChange::query()
            ->with(['Users', 'shifts', 'shift_rotation'])
            ->findOrFail($request->route('manual_shift_change'));

        return view('time-management.manual-shift-changes.show', [
            'shiftChange' => $shiftChange,
        ]);
    }

    public function edit(Request $request): View
    {
        $shiftChange = ShiftChange::query()
            ->with(['Users', 'shifts'])
            ->findOrFail($request->route('manual_shift_change'));

        return view('time-management.manual-shift-changes.edit', $this->formOptions($shiftChange));
    }

    public function update(UpdateManualShiftChangeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $shiftIds = $validated['shift_ids'] ?? [];
        $validated = $this->preparePayload($validated);
        $validated['updated_by'] = Auth::guard('tenant')->id();

        $shiftChange = ShiftChange::query()->findOrFail($request->route('manual_shift_change'));
        DB::transaction(function () use ($shiftChange, $validated, $userIds, $shiftIds): void {
            $shiftChange->update($validated);
            $shiftChange->Users()->sync($userIds);
            $shiftChange->shifts()->sync($shiftIds);
        });

        CreateShiftMuster::dispatch($shiftChange->fresh())
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();

        return redirect(url(self::INDEX_URL))->with('success', 'Manual shift change updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $shiftChange = ShiftChange::query()->findOrFail($request->route('manual_shift_change'));

        DB::transaction(function () use ($shiftChange): void {
            $shiftChange->Users()->detach();
            $shiftChange->shifts()->detach();
            $shiftChange->deleted_by = Auth::guard('tenant')->id();
            $shiftChange->save();
            $shiftChange->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Manual shift change deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?ShiftChange $shiftChange): array
    {
        return [
            'shiftChange' => $shiftChange,
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
        unset($validated['employee_filters'], $validated['location_ids'], $validated['company_ids'], $validated['shift_ids'], $validated['users']);

        return $validated;
    }

    /**
     * @param array<string, string> $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, int>, 3: array<int, string>}
     */
    private function resolveImportRow(array $row): array
    {
        $errors = [];
        $employeeCodes = $this->splitCsvValues($row['employee_codes'] ?? '');
        $users = User::query()->whereIn('code', $employeeCodes)->get(['id', 'code'])->keyBy('code');
        if ($employeeCodes === [] || $users->count() !== count($employeeCodes)) {
            $errors[] = 'employee_codes is required and every employee code must exist';
        }
        $userIds = $users->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $type = strtolower(trim($row['shift_type'] ?? ''));
        $shiftValues = $this->splitCsvValues($row['shifts'] ?? '');
        $shiftIds = [];
        $rotationId = null;
        if (in_array($type, ['auto', 'fixed'], true)) {
            $shifts = collect();
            foreach ($shiftValues as $shiftValue) {
                $shift = Shift::query()
                    ->where(function ($query) use ($shiftValue): void {
                        $query->where('code', $shiftValue)->orWhere('name', $shiftValue);
                    })
                    ->first(['id']);
                if ($shift === null) {
                    $errors[] = 'shift "'.$shiftValue.'" could not be found';
                    continue;
                }
                $shifts->push($shift);
            }
            if ($shiftValues === []) {
                $errors[] = 'at least one shift code or name is required';
            }
            if ($type === 'fixed' && count($shiftValues) !== 1) {
                $errors[] = 'fixed shift type requires exactly one shift';
            }
            $shiftIds = $shifts->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        } elseif ($type === 'rotational') {
            $rotation = ShiftRotation::query()->where('code', trim($row['shift_rotation_code'] ?? ''))->first(['id']);
            if ($rotation === null) {
                $errors[] = 'shift_rotation_code must match an existing rotation';
            } else {
                $rotationId = (int) $rotation->id;
            }
        } else {
            $errors[] = 'shift_type must be auto, fixed, or rotational';
        }

        $isForever = $this->importBoolean($row['is_forever'] ?? '');
        if (($row['is_forever'] ?? '') !== '' && ! in_array(strtolower($row['is_forever']), ['0', '1', 'true', 'false', 'yes', 'no'], true)) {
            $errors[] = 'is_forever must be 1 or 0';
        }
        $values = [
            'from_date' => $row['from_date'] ?? '',
            'to_date' => $isForever ? null : ($row['to_date'] ?? ''),
            'reason' => $row['reason'] ?? '',
        ];
        $validator = Validator::make($values, [
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => [$isForever ? 'nullable' : 'required', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $errors = array_merge($errors, $validator->errors()->all());

        return [[
            'shift_type' => $type,
            'shift_rotation_id' => $rotationId,
            'from_date' => $values['from_date'],
            'is_forever' => $isForever,
            'to_date' => $values['to_date'],
            'reason' => ($values['reason'] ?? '') !== '' ? $values['reason'] : null,
            'created_by' => Auth::guard('tenant')->id(),
            'updated_by' => Auth::guard('tenant')->id(),
        ], $userIds, $shiftIds, $errors];
    }

    /**
     * @return array<int, string>
     */
    private function splitCsvValues(string $value): array
    {
        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[,;]/', $value) ?: []),
            static fn (string $item): bool => $item !== '',
        )));
    }

    private function importBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
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
}
