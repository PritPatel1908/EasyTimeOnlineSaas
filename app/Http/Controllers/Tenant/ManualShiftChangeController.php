<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreManualShiftChangeRequest;
use App\Http\Requests\Tenant\UpdateManualShiftChangeRequest;
use App\Jobs\Tenant\CreateShiftMuster;
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
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualShiftChangeController extends Controller
{
    private const INDEX_URL = 'time-management/manual-shift-changes';

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
