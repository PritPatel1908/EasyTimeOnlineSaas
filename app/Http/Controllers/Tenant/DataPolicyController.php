<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreDataPolicyRequest;
use App\Http\Requests\Tenant\UpdateDataPolicyRequest;
use App\Jobs\Tenant\ActivityLog;
use App\Models\Tenant\Area;
use App\Models\Tenant\BusRoute;
use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\Category;
use App\Models\Tenant\Company;
use App\Models\Tenant\DataPolicy;
use App\Models\Tenant\Department;
use App\Models\Tenant\Designation;
use App\Models\Tenant\Grade;
use App\Models\Tenant\Location;
use App\Models\Tenant\Machine;
use App\Models\Tenant\SubCategory;
use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\Team;
use App\Models\Tenant\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DataPolicyController extends Controller
{
    private const INDEX_URL = 'data-policy';

    private const RELATIONS = [
        'locations' => Location::class,
        'companies' => Company::class,
        'departments' => Department::class,
        'teams' => Team::class,
        'sub_departments' => SubDepartment::class,
        'categories' => Category::class,
        'sub_categories' => SubCategory::class,
        'designations' => Designation::class,
        'grades' => Grade::class,
        'units' => Unit::class,
        'bus_routes' => BusRoute::class,
        'areas' => Area::class,
        'canteen_facilities' => CanteenFacility::class,
        'machines' => Machine::class,
    ];

    public function index(): View
    {
        return view('company-structure.data-policies.index', [
            'dataPolicies' => DataPolicy::query()->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('company-structure.data-policies.add', $this->formOptions());
    }

    public function store(StoreDataPolicyRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $dataPolicy = DataPolicy::query()->create($this->attributes($request->validated()));
            $this->syncRelations($dataPolicy, $request->validated());
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Data policy created successfully.');
    }

    public function edit(string $tenant, DataPolicy $dataPolicy): View
    {
        $dataPolicy->load(array_keys(self::RELATIONS));

        return view('company-structure.data-policies.edit', array_merge(
            $this->formOptions(),
            ['dataPolicy' => $dataPolicy],
        ));
    }

    public function update(string $tenant, UpdateDataPolicyRequest $request, DataPolicy $dataPolicy): RedirectResponse
    {
        DB::transaction(function () use ($request, $dataPolicy): void {
            $oldRelations = $this->relationSnapshot($dataPolicy);
            $dataPolicy->update($this->attributes($request->validated()));
            $this->syncRelations($dataPolicy, $request->validated());
            $newRelations = $this->relationSnapshot($dataPolicy);

            if ($oldRelations !== $newRelations) {
                ActivityLog::dispatch(
                    auth('tenant')->user(),
                    $dataPolicy,
                    ['relations' => $oldRelations],
                    ['relations' => $newRelations],
                    'relations_updated'
                )->afterCommit()->onConnection('database_tenant')->onQueue('processing');
            }
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Data policy updated successfully.');
    }

    public function destroy(string $tenant, DataPolicy $dataPolicy): RedirectResponse
    {
        if ($dataPolicy->users()->exists()) {
            return redirect(url(self::INDEX_URL))
                ->with('error', 'This data policy is assigned to users and cannot be deleted.');
        }

        DB::transaction(function () use ($dataPolicy): void {
            foreach (array_keys(self::RELATIONS) as $relation) {
                $dataPolicy->{$relation}()->detach();
            }

            $dataPolicy->delete();
        });

        return redirect(url(self::INDEX_URL))->with('success', 'Data policy deleted successfully.');
    }

    private function formOptions(): array
    {
        $options = [];
        foreach (self::RELATIONS as $relation => $model) {
            $query = $model::query()->orderBy('name');

            if (Schema::hasColumn((new $model)->getTable(), 'status')) {
                $query->where('status', 1);
            }

            $options[$relation] = $query->get();
        }

        return ['relationOptions' => $options];
    }

    private function attributes(array $validated): array
    {
        return array_merge(
            array_intersect_key($validated, array_flip([
                'code',
                'name',
                'status',
                'self_only',
                'all_locations',
                'all_companies',
                'all_departments',
                'all_teams',
                'all_sub_departments',
                'all_categories',
                'all_sub_categories',
                'all_designations',
                'all_grades',
                'all_units',
                'all_bus_routes',
                'all_areas',
                'all_canteen_facilities',
                'all_machines',
            ])),
            [
                'created_by' => auth('tenant')->id(),
                'updated_by' => auth('tenant')->id(),
            ],
        );
    }

    private function syncRelations(DataPolicy $dataPolicy, array $validated): void
    {
        foreach (array_keys(self::RELATIONS) as $relation) {
            $dataPolicy->{$relation}()->sync($validated[$relation] ?? []);
        }
    }

    private function relationSnapshot(DataPolicy $dataPolicy): array
    {
        $snapshot = [];

        foreach (array_keys(self::RELATIONS) as $relation) {
            $snapshot[$relation] = $dataPolicy->{$relation}()->pluck($dataPolicy->{$relation}()->getRelated()->getTable() . '.id')->sort()->values()->all();
        }

        return $snapshot;
    }
}
