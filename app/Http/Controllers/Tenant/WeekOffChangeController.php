<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\WOTypeEnumn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreWeekOffChangeRequest;
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
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeekOffChangeController extends Controller
{
    private const INDEX_URL = 'time-management/week-off-changes';

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
