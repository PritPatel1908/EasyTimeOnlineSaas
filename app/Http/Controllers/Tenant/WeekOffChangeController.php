<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\WOTypeEnumn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreWeekOffChangeRequest;
use App\Jobs\Tenant\GenerateWeekOffChangeExport;
use App\Jobs\Tenant\CreateWeekOffMuster;
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
use App\Models\Tenant\WeekOffChange;
use App\Support\CsvImportTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WeekOffChangeController extends Controller
{
    private const INDEX_URL = 'time-management/week-off-changes';

    public function export(): RedirectResponse
    {
        $fileName = 'week_off_changes_'.now()->format('Ymd_His').'.csv';
        $userId = Auth::guard('tenant')->id();
        GenerateWeekOffChangeExport::dispatch($userId === null ? null : (int) $userId, $fileName)
            ->onConnection('database_tenant')
            ->onQueue('export');

        return redirect(url(self::INDEX_URL))
            ->with('success', 'Week-off change export has started. You will receive a notification when the export is ready.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Aweek_off_changes_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'week-off-change-exports/'.$fileName;

        if (! Storage::disk('local')->exists($path)) {
            $userId = Auth::guard('tenant')->id();
            (new GenerateWeekOffChangeExport($userId === null ? null : (int) $userId, $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'week_off_changes.csv',
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

            CsvImportTemplate::write($handle, WeekOffChange::IMPORT_EXPORT_COLUMNS, [
                'employee_codes' => 'Required; employee codes separated by commas.',
                'from_date' => 'Required; date in YYYY-MM-DD format.',
                'is_forever' => 'Optional; use 1 for no end date or 0 otherwise.',
                'to_date' => 'Required unless is_forever is 1; YYYY-MM-DD format.',
                'reason' => 'Optional; text up to 255 characters.',
                'week_days' => 'Required; one day number (0 Sunday to 6 Saturday) per detail, comma-separated.',
                'wo_types' => 'Required; one type (1 FullDay, 2 FirstHalf, 3 SecondHalf) per detail, comma-separated.',
                'first_week' => 'Required; one 1/0 value per detail, comma-separated.',
                'second_week' => 'Required; one 1/0 value per detail, comma-separated.',
                'third_week' => 'Required; one 1/0 value per detail, comma-separated.',
                'fourth_week' => 'Required; one 1/0 value per detail, comma-separated.',
                'fifth_week' => 'Required; one 1/0 value per detail, comma-separated.',
            ]);
            fclose($handle);
        }, 'week_off_changes_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
            throw new RuntimeException('Unable to open the uploaded week-off change CSV.');
        }

        $headers = fgetcsv($handle);
        if ($headers === false || $headers === []) {
            fclose($handle);

            return redirect(url(self::INDEX_URL))->with('error', 'The week-off change CSV is empty.');
        }
        $headers = array_map(static fn ($header): string => CsvImportTemplate::normalizeHeader((string) $header), $headers);
        if (array_diff(WeekOffChange::IMPORT_EXPORT_COLUMNS, $headers) !== []) {
            fclose($handle);

            return redirect(url(self::INDEX_URL))->with('error', 'The CSV is missing one or more required week-off change columns.');
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

            [$payload, $userIds, $details, $validationErrors] = $this->resolveImportRow($row);
            if ($validationErrors !== []) {
                $errors[] = 'Row '.$line.': '.implode(', ', $validationErrors);
                continue;
            }

            $change = DB::transaction(function () use ($payload, $userIds, $details): WeekOffChange {
                $change = WeekOffChange::query()->create($payload);
                $change->Users()->sync($userIds);
                $change->WeekOffChangeDetails()->createMany($details);

                return $change;
            });
            $this->dispatchMusterCreation($change);
            $created++;
        }
        fclose($handle);

        $message = $errors === []
            ? "Imported {$created} week-off change record(s)."
            : 'Imported '.$created.' week-off change record(s) with '.count($errors).' error(s): '.implode(' | ', array_slice($errors, 0, 3));

        return redirect(url(self::INDEX_URL))->with($errors === [] ? 'success' : 'error', $message);
    }

    public function index(): View
    {
        return view('time-management.week-off-changes.index', [
            'weekOffChanges' => WeekOffChange::query()
                ->with(['Users', 'WeekOffChangeDetails'])
                ->latest('from_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('time-management.week-off-changes.add', $this->formOptions(null));
    }

    public function store(StoreWeekOffChangeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $details = $validated['details'];
        $payload = $this->preparePayload($validated);
        $payload['created_by'] = Auth::guard('tenant')->id();
        $payload['updated_by'] = Auth::guard('tenant')->id();

        $weekOffChange = DB::transaction(function () use ($payload, $userIds, $details): WeekOffChange {
            $weekOffChange = WeekOffChange::query()->create($payload);
            $weekOffChange->Users()->sync($userIds);
            $weekOffChange->WeekOffChangeDetails()->createMany($details);

            return $weekOffChange;
        });

        $this->dispatchMusterCreation($weekOffChange);

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off change created successfully.');
    }

    public function show(Request $request): View
    {
        $weekOffChange = WeekOffChange::query()
            ->with(['Users', 'WeekOffChangeDetails'])
            ->findOrFail($request->route('week_off_change'));

        return view('time-management.week-off-changes.show', [
            'weekOffChange' => $weekOffChange,
            'weekDays' => $this->weekDays(),
            'weekOffTypes' => $this->weekOffTypes(),
        ]);
    }

    public function edit(Request $request): View
    {
        $weekOffChange = WeekOffChange::query()
            ->with(['Users', 'WeekOffChangeDetails'])
            ->findOrFail($request->route('week_off_change'));

        return view('time-management.week-off-changes.edit', $this->formOptions($weekOffChange));
    }

    public function update(StoreWeekOffChangeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userIds = $this->resolveUserIds($validated);
        if ($userIds === []) {
            throw ValidationException::withMessages([
                'users' => 'No employees match the selected filters.',
            ]);
        }

        $details = $validated['details'];
        $payload = $this->preparePayload($validated);
        $payload['updated_by'] = Auth::guard('tenant')->id();
        $weekOffChange = WeekOffChange::query()->findOrFail($request->route('week_off_change'));

        DB::transaction(function () use ($weekOffChange, $payload, $userIds, $details): void {
            $weekOffChange->update($payload);
            $weekOffChange->Users()->sync($userIds);
            $weekOffChange->WeekOffChangeDetails()->delete();
            $weekOffChange->WeekOffChangeDetails()->createMany($details);
        });

        $this->dispatchMusterCreation($weekOffChange->fresh());

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off change updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $weekOffChange = WeekOffChange::query()->findOrFail($request->route('week_off_change'));

        DB::transaction(function () use ($weekOffChange): void {
            $weekOffChange->Users()->detach();
            $weekOffChange->WeekOffChangeDetails()->delete();
            $weekOffChange->deleted_by = Auth::guard('tenant')->id();
            $weekOffChange->save();
            $weekOffChange->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Week-off change deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?WeekOffChange $weekOffChange): array
    {
        return [
            'weekOffChange' => $weekOffChange,
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
            $validated['details'],
            $validated['employee_filters'],
            $validated['location_ids'],
            $validated['company_ids'],
            $validated['users']
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

    private function dispatchMusterCreation(WeekOffChange $weekOffChange): void
    {
        CreateWeekOffMuster::dispatch($weekOffChange)
            ->onConnection('database_tenant')
            ->onQueue('processing')
            ->afterCommit();
    }

    /**
     * @param array<string, string> $row
     * @return array{0: array<string, mixed>, 1: array<int, int>, 2: array<int, array<string, int>>, 3: array<int, string>}
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

        $days = $this->splitCsvValues($row['week_days'] ?? '');
        $types = $this->splitCsvValues($row['wo_types'] ?? '');
        $weekFields = ['first_week', 'second_week', 'third_week', 'fourth_week', 'fifth_week'];
        $weekValues = [];
        foreach ($weekFields as $field) {
            $weekValues[$field] = $this->splitCsvValues($row[$field] ?? '');
        }
        $count = count($days);
        if ($count === 0 || count($types) !== $count) {
            $errors[] = 'week_days and wo_types must contain the same non-zero number of values';
        }
        foreach ($weekValues as $field => $values) {
            if (count($values) !== $count) {
                $errors[] = $field.' must contain one value per week-off detail';
            }
        }

        $details = [];
        $validDays = $this->weekDays();
        for ($index = 0; $index < $count; $index++) {
            $day = $days[$index] ?? '';
            $type = $types[$index] ?? '';
            $dayNumber = is_numeric($day) ? (int) $day : array_search(ucfirst(strtolower($day)), $validDays, true);
            if ($dayNumber === false || $dayNumber < 0 || $dayNumber > 6) {
                $errors[] = 'week_days values must be day numbers 0 through 6 or weekday names';
                continue;
            }
            if (! in_array((int) $type, array_map(static fn (WOTypeEnumn $item): int => $item->value, WOTypeEnumn::cases()), true)) {
                $errors[] = 'wo_types values must be 1, 2, or 3';
                continue;
            }

            $detail = [
                'week_days' => $dayNumber,
                'wo_type' => (int) $type,
            ];
            $hasWeek = false;
            foreach ($weekFields as $field) {
                $value = strtolower($weekValues[$field][$index] ?? '');
                if (! in_array($value, ['0', '1', 'false', 'true', 'no', 'yes'], true)) {
                    $errors[] = $field.' values must be 1 or 0';
                    $detail[$field] = 0;
                    continue;
                }
                $detail[$field] = in_array($value, ['1', 'true', 'yes'], true) ? 1 : 0;
                $hasWeek = $hasWeek || $detail[$field] === 1;
            }
            if (! $hasWeek) {
                $errors[] = 'each weekly-off detail must apply to at least one week';
            }
            $details[] = $detail;
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
            'from_date' => $values['from_date'],
            'is_forever' => $isForever,
            'to_date' => $values['to_date'],
            'reason' => ($values['reason'] ?? '') !== '' ? $values['reason'] : null,
            'created_by' => Auth::guard('tenant')->id(),
            'updated_by' => Auth::guard('tenant')->id(),
        ], $userIds, $details, array_values(array_unique($errors))];
    }

    /**
     * @return array<int, string>
     */
    private function splitCsvValues(string $value): array
    {
        return array_values(array_map('trim', preg_split('/[,;]/', $value) ?: []));
    }

    private function importBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
    }

    /**
     * @return array<int, string>
     */
    private function weekDays(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function weekOffTypes(): array
    {
        return collect(WOTypeEnumn::cases())
            ->mapWithKeys(fn (WOTypeEnumn $type): array => [$type->value => $type->getLabel()])
            ->all();
    }
}
