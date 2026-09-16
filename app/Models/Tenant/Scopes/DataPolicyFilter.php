<?php

namespace App\Models\Tenant\Scopes;

use App\Models\Tenant\Setting;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DataPolicyFilter implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $guard = Auth::guard('tenant');

        // The tenant provider loads User through this scope. Avoid resolving the guard again during that query.
        if ($model instanceof User && ! $guard->hasUser()) {
            return;
        }

        $user = $guard->user();
        if (! $user) {
            return;
        }

        if ($guard->id() == 1 || $user->hasRole('super_admin')) {
            return;
        }

        if (method_exists($user, 'data_policy') && $data_policy = $user->data_policy()->first()) {
            $table = $model->getTable();

            if ($data_policy->self_only) {
                $this->applySelfScope($builder, $model);

                return;
            }

            // Special handling for ShiftChange model
            if ($table === 'shift_changes') {
                $this->applyShiftChangeScope($builder, $model, $data_policy);

                return;
            }

            // Special handling for weekOffChange model
            if ($table === 'week_off_changes') {
                $this->applyWeekOffChangeScope($builder, $model, $data_policy);

                return;
            }

            if ($table === 'canteen_facilities') {
                if (! $data_policy->all_locations) {
                    $locationIds = $data_policy->locations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('locations.id');
                    $builder->whereIn($table . '.location_id', $locationIds);
                }

                if (! $data_policy->all_canteen_facilities) {
                    $canteenFacilityIds = $data_policy->canteen_facilities()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('canteen_facilities.id');
                    $builder->where(function (Builder $query) use ($table, $canteenFacilityIds) {
                        $query->whereIn($table . '.id', $canteenFacilityIds);
                        $this->addSelfCreatedRecordAccess($query, $table);
                    });
                }

                $this->addSelfCreatedRecordAccess($builder, $table);
            } elseif (in_array($table, ['locations', 'companies', 'departments', 'teams', 'sub_departments', 'categories', 'sub_categories', 'designations', 'grades', 'units', 'bus_routes', 'areas', 'machines'])) {
                $ids = $data_policy->$table()->withoutGlobalScopes([DataPolicyFilter::class])->pluck($table . '.id');
                if (! $data_policy->{'all_' . $table}) {
                    $builder->where(function (Builder $query) use ($table, $ids) {
                        $query->whereIn($table . '.id', $ids);
                        $this->addSelfCreatedRecordAccess($query, $table);
                    });
                }
            } elseif ($table == 'users') {
                $builder->where(function (Builder $query) use ($table, $data_policy) {
                    $query->where(function (Builder $query) use ($table) {
                        if ($table == 'users' && Auth::guard('tenant')->user()) {
                            $query->where($table . '.id', '=', Auth::guard('tenant')->user()->id);
                        }
                    })->orWhere(function (Builder $query) use ($table, $data_policy) {
                        $query->with('areas');
                        if ($data_policy->all_locations == false) {
                            $locationIds = $data_policy->locations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('locations.id')->toArray();
                            $query->where(function ($q) use ($table, $locationIds) {
                                foreach ($locationIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.location_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.location_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        } else {
                            $query->where($table . '.location_id', '!=', null);
                        }
                        if ($data_policy->all_companies == false && $this->settingEnabled('companies')) {
                            // $query->whereIn($table . ".company_id", $data_policy->companies()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('companies.id'));
                            $companyIds = $data_policy->companies()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('companies.id')->toArray();
                            $query->where(function ($q) use ($table, $companyIds) {
                                foreach ($companyIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.company_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.company_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_departments == false && $this->settingEnabled('departments')) {
                            // $query->whereIn($table . ".department_id", $data_policy->departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('departments.id'));
                            $departmentIds = $data_policy->departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('departments.id')->toArray();
                            $query->where(function ($q) use ($table, $departmentIds) {
                                foreach ($departmentIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.department_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.department_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_teams == false && $this->settingEnabled('teams')) {
                            $teamIds = $data_policy->teams()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('teams.id')->toArray();
                            $query->where(function ($q) use ($table, $teamIds) {
                                foreach ($teamIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.team_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.team_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_sub_departments == false && $this->settingEnabled('sub_departments')) {
                            // $query->whereIn($table . ".sub_department_id", $data_policy->sub_departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_departments.id'));
                            $subDepartmentIds = $data_policy->sub_departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_departments.id')->toArray();
                            $query->where(function ($q) use ($table, $subDepartmentIds) {
                                foreach ($subDepartmentIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.sub_department_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.sub_department_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_categories == false && $this->settingEnabled('categories')) {
                            // $query->whereIn($table . ".category_id", $data_policy->categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('categories.id'));
                            $categoryIds = $data_policy->categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('categories.id')->toArray();
                            $query->where(function ($q) use ($table, $categoryIds) {
                                foreach ($categoryIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.category_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.category_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_sub_categories == false && $this->settingEnabled('sub_categories')) {
                            // $query->whereIn($table . ".sub_category_id", $data_policy->sub_categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_categories.id'));
                            $subCategoryIds = $data_policy->sub_categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_categories.id')->toArray();
                            $query->where(function ($q) use ($table, $subCategoryIds) {
                                foreach ($subCategoryIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.sub_category_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.sub_category_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_designations == false && $this->settingEnabled('designations')) {
                            // $query->whereIn($table . ".designation_id", $data_policy->designations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('designations.id'));
                            $designationIds = $data_policy->designations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('designations.id')->toArray();
                            $query->where(function ($q) use ($table, $designationIds) {
                                foreach ($designationIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.designation_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.designation_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_grades == false && $this->settingEnabled('grades')) {
                            // $query->whereIn($table . ".grade_id", $data_policy->grades()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('grades.id'));
                            $gradeIds = $data_policy->grades()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('grades.id')->toArray();
                            $query->where(function ($q) use ($table, $gradeIds) {
                                foreach ($gradeIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.grade_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.grade_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_units == false && $this->settingEnabled('units')) {
                            // $query->whereIn($table . ".unit_id", $data_policy->units()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('units.id'));
                            $unitIds = $data_policy->units()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('units.id')->toArray();
                            $query->where(function ($q) use ($table, $unitIds) {
                                foreach ($unitIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.unit_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.unit_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_bus_routes == false && $this->settingEnabled('bus_routes')) {
                            // $query->whereIn($table . ".bus_route_id", $data_policy->bus_routes()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('bus_routes.id'));
                            $busRouteIds = $data_policy->bus_routes()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('bus_routes.id')->toArray();
                            $query->where(function ($q) use ($table, $busRouteIds) {
                                foreach ($busRouteIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.bus_route_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.bus_route_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                        if ($data_policy->all_areas == false && $this->settingEnabled('areas')) {
                            $query->whereHas('areas', function ($squery) use ($data_policy) {
                                $squery->whereIn('areas.id', $data_policy->areas()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('areas.id'));
                            });
                        }
                        if ($data_policy->machines == false && $this->settingEnabled('machines')) {
                            // $query->whereIn($table . ".machine_id", $data_policy->machines()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('machines.id'));
                            $machineIds = $data_policy->machines()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('machines.id')->toArray();
                            $query->where(function ($q) use ($table, $machineIds) {
                                foreach ($machineIds as $id) {
                                    $q->orWhere(function ($subquery) use ($table, $id) {
                                        $subquery->whereRaw('CHARINDEX(?, ' . $table . '.machine_id) > 0', ['["' . $id . '"]']);
                                    })
                                        ->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('TRY_CAST(' . $table . '.machine_id AS INT) = ?', [$id]);
                                        });
                                }
                            });
                        }
                    });
                    $this->addSelfCreatedRecordAccess($query, $table);
                });
            } else {
                $builder->where(function (Builder $query) use ($table) {
                    $query->where($table . '.id', '=', Auth::guard('tenant')->id());
                    $this->addSelfCreatedRecordAccess($query, $table);
                });
            }
        } else {
            $this->applySelfScope($builder, $model);
        }
    }

    /**
     * Apply special scope for ShiftChange model
     */
    private function applyShiftChangeScope(Builder $builder, Model $model, $data_policy): void
    {
        // First, ensure the shift change record itself is accessible
        $builder->where(function (Builder $query) use ($data_policy) {
            // Allow access to shift changes created by the current user
            $this->addSelfCreatedRecordAccess($query, 'shift_changes', false);

            // Or where the user has access to the related users
            $query->orWhereHas('Users', function (Builder $userQuery) use ($data_policy) {
                // Apply the same user filtering logic as in the main scope
                $this->applyUserFilters($userQuery, $data_policy);
            });
        });
    }

    /**
     * Apply special scope for WeekOffChange model
     */
    private function applyWeekOffChangeScope(Builder $builder, Model $model, $data_policy): void
    {
        // First, ensure the shift change record itself is accessible
        $builder->where(function (Builder $query) use ($data_policy) {
            // Allow access to shift changes created by the current user
            $this->addSelfCreatedRecordAccess($query, 'week_off_changes', false);

            // Or where the user has access to the related users
            $query->orWhereHas('Users', function (Builder $userQuery) use ($data_policy) {
                // Apply the same user filtering logic as in the main scope
                $this->applyUserFilters($userQuery, $data_policy);
            });
        });
    }

    /**
     * Apply user filters based on data policy
     */
    private function applyUserFilters(Builder $query, $data_policy): void
    {
        // User is always allowed to see themselves
        $query->where(function (Builder $subquery) {
            $subquery->where('users.id', '=', Auth::guard('tenant')->id());
            $this->addSelfCreatedRecordAccess($subquery, 'users');
        });

        // Apply location filters
        if ($data_policy->all_locations == false) {
            $locationIds = $data_policy->locations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('locations.id')->toArray();
            $query->orWhere(function ($q) use ($locationIds) {
                foreach ($locationIds as $id) {
                    $q->orWhere(function ($subquery) use ($id) {
                        $subquery->whereRaw('CHARINDEX(?, users.location_id) > 0', ['["' . $id . '"]']);
                    })
                        ->orWhere(function ($subquery) use ($id) {
                            $subquery->whereRaw('TRY_CAST(users.location_id AS INT) = ?', [$id]);
                        });
                }
            });
        }

        // Apply company filters
        if ($data_policy->all_companies == false && $this->settingEnabled('companies')) {
            $companyIds = $data_policy->companies()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('companies.id')->toArray();
            $query->where(function ($q) use ($companyIds) {
                foreach ($companyIds as $id) {
                    $q->orWhere(function ($subquery) use ($id) {
                        $subquery->whereRaw('CHARINDEX(?, users.company_id) > 0', ['["' . $id . '"]']);
                    })
                        ->orWhere(function ($subquery) use ($id) {
                            $subquery->whereRaw('TRY_CAST(users.company_id AS INT) = ?', [$id]);
                        });
                }
            });
        }
    }

    private function applySelfScope(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        if (in_array($table, ['shift_changes', 'week_off_changes'])) {
            $builder->where(function (Builder $query) use ($table) {
                $this->addSelfCreatedRecordAccess($query, $table, false);
                $query->orWhereHas('Users', function (Builder $userQuery) {
                    $this->applyCurrentUserAssignmentFilters($userQuery);
                });
            });
        } elseif (in_array($table, ['locations', 'companies', 'departments', 'teams', 'sub_departments', 'categories', 'sub_categories', 'designations', 'grades', 'units', 'bus_routes'])) {
            $singular = Str::singular($table);
            $property = $singular . '_id';
            $user = Auth::guard('tenant')->user();

            $builder->where(function (Builder $query) use ($table, $property, $user) {
                // Check if the property exists on the user
                if ($user != null && $property != null) {
                    $ids = $this->normalizeIds($user->getRawOriginal($property));

                    if (! empty($ids)) {
                        $query->whereIn($table . '.id', $ids);
                    }
                }
                $this->addSelfCreatedRecordAccess($query, $table);
            });
        } elseif ($table == 'areas') {
            $user = Auth::guard('tenant')->user();
            $areaIds = $user && method_exists($user, 'areas')
                ? $user->areas()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('areas.id')->all()
                : [];
            $builder->where(function (Builder $query) use ($table, $areaIds) {
                $query->whereIn($table . '.id', $areaIds);
                $this->addSelfCreatedRecordAccess($query, $table);
            });
        } elseif ($table == 'users') {
            $builder->where(function (Builder $query) use ($table) {
                $query->where($table . '.id', '=', Auth::guard('tenant')->id());
                $query->orWhere(function (Builder $assignmentQuery) {
                    $this->applyCurrentUserAssignmentFilters($assignmentQuery);
                });
                $this->addSelfCreatedRecordAccess($query, 'users');
            });
        } else {
            $builder->where(function (Builder $query) use ($table) {
                $this->addSelfCreatedRecordAccess($query, $table, false);
            });
        }
    }

    private function applyCurrentUserAssignmentFilters(Builder $query): void
    {
        $user = Auth::guard('tenant')->user();

        if (! $user) {
            return;
        }

        foreach (['location', 'company', 'department', 'sub_department', 'category', 'sub_category', 'designation', 'grade', 'unit', 'bus_route'] as $property) {
            $ids = $this->normalizeIds($user->getRawOriginal($property . '_id'));

            if ($ids === []) {
                continue;
            }

            $query->where(function (Builder $assignmentQuery) use ($property, $ids) {
                foreach ($ids as $id) {
                    $assignmentQuery->orWhere(function (Builder $subquery) use ($property, $id) {
                        $subquery->whereRaw('CHARINDEX(?, users.' . $property . '_id) > 0', ['["' . $id . '"]']);
                    })->orWhere(function (Builder $subquery) use ($property, $id) {
                        $subquery->whereRaw('TRY_CAST(users.' . $property . '_id AS INT) = ?', [$id]);
                    });
                }
            });
        }

        if (! method_exists($user, 'areas')) {
            $query->whereRaw('1 = 0');

            return;
        }

        $areaIds = $user->areas()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('areas.id')->all();
        if ($areaIds === []) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereHas('areas', function (Builder $areaQuery) use ($areaIds) {
                $areaQuery->whereIn('areas.id', $areaIds);
            });
        }
    }

    /**
     * Normalise a raw database value from a JSON _id column into a clean array of integers.
     *
     * Handles all storage variants:
     *   - JSON array  : "[1,2]"  → [1, 2]
     *   - JSON scalar : "1"      → [1]   (stored as a JSON-encoded string, not a proper array)
     *   - Plain int   : 1        → [1]
     *   - PHP array   : [1, 2]   → [1, 2]
     *   - null / ""   : null     → []
     */
    private function normalizeIds(mixed $raw): array
    {
        if ($raw === null || $raw === '' || $raw === '[]') {
            return [];
        }

        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // json_decode on "[1,2]" → [1,2]  (array)
                // json_decode on "1"    → 1       (int scalar)
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('intval', $decoded)));
                }
                if (is_numeric($decoded)) {
                    return [(int) $decoded];
                }
            }
            // Fallback: plain numeric string stored without JSON encoding
            if (is_numeric($raw)) {
                return [(int) $raw];
            }

            return [];
        }

        if (is_numeric($raw)) {
            return [(int) $raw];
        }

        return [];
    }

    private function settingEnabled(string $key): bool
    {
        return (string) Setting::query()->where('key', $key)->value('value') === '1';
    }

    private function addSelfCreatedRecordAccess(Builder $query, string $table, bool $orWhere = true): void
    {
        $method = $orWhere ? 'orWhere' : 'where';
        $query->{$method}($table . '.created_by', '=', Auth::guard('tenant')->id());
    }
}
