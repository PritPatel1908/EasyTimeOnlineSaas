<?php

namespace App\Models\Tenant\Scopes;

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
        // if (Auth::hasUser() && Auth::user() && (Auth::id() == 1 || Auth::user()->hasRole('super_admin'))) {
        if (Auth::hasUser()) {
            if (Auth::hasUser() && Auth::user() && (Auth::id() == 1 || Auth::user()->hasRole('super_admin'))) {
                return;
            }

            if (Auth::user() && method_exists(Auth::user(), 'data_policy') && $data_policy = Auth::user()->data_policy()->first()) {
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

                if (in_array($table, ['locations', 'companies', 'departments', 'sub_departments', 'categories', 'sub_categories', 'designations', 'grades', 'units', 'bus_routes', 'areas', 'machines'])) {
                    $ids = $data_policy->$table()->withoutGlobalScopes([DataPolicyFilter::class])->pluck($table.'.id');
                    if (! $data_policy->{'all_'.$table}) {
                        $builder->where(function (Builder $query) use ($table, $ids) {
                            $query->whereIn($table.'.id', $ids);
                            // $query->orWhere($table . ".created_by", '=', Auth::id());
                        });
                    }
                } elseif ($table == 'users') {
                    $builder->where(function (Builder $query) use ($table, $data_policy) {
                        $query->where(function (Builder $query) use ($table) {
                            if ($table == 'users' && Auth::user()) {
                                $query->where($table.'.id', '=', Auth::user()->id);
                                // $query->orWhere($table . ".created_by", '=', Auth::id());
                            }
                        })->orWhere(function (Builder $query) use ($table, $data_policy) {
                            $query->with('areas');
                            if ($data_policy->all_locations == false) {
                                $locationIds = $data_policy->locations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('locations.id')->toArray();
                                $query->where(function ($q) use ($table, $locationIds) {
                                    foreach ($locationIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.location_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.location_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            } else {
                                $query->where($table.'.location_id', '!=', null);
                            }
                            if ($data_policy->all_companies == false && setting('companies') == 1) {
                                // $query->whereIn($table . ".company_id", $data_policy->companies()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('companies.id'));
                                $companyIds = $data_policy->companies()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('companies.id')->toArray();
                                $query->where(function ($q) use ($table, $companyIds) {
                                    foreach ($companyIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.company_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.company_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_departments == false && setting('departments') == 1) {
                                // $query->whereIn($table . ".department_id", $data_policy->departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('departments.id'));
                                $departmentIds = $data_policy->departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('departments.id')->toArray();
                                $query->where(function ($q) use ($table, $departmentIds) {
                                    foreach ($departmentIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.department_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.department_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_sub_departments == false && setting('sub_departments') == 1) {
                                // $query->whereIn($table . ".sub_department_id", $data_policy->sub_departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_departments.id'));
                                $subDepartmentIds = $data_policy->sub_departments()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_departments.id')->toArray();
                                $query->where(function ($q) use ($table, $subDepartmentIds) {
                                    foreach ($subDepartmentIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.sub_department_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.sub_department_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_categories == false && setting('categories') == 1) {
                                // $query->whereIn($table . ".category_id", $data_policy->categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('categories.id'));
                                $categoryIds = $data_policy->categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('categories.id')->toArray();
                                $query->where(function ($q) use ($table, $categoryIds) {
                                    foreach ($categoryIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.category_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.category_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_sub_categories == false && setting('sub_categories') == 1) {
                                // $query->whereIn($table . ".sub_category_id", $data_policy->sub_categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_categories.id'));
                                $subCategoryIds = $data_policy->sub_categories()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('sub_categories.id')->toArray();
                                $query->where(function ($q) use ($table, $subCategoryIds) {
                                    foreach ($subCategoryIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.sub_category_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.sub_category_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_designations == false && setting('designations') == 1) {
                                // $query->whereIn($table . ".designation_id", $data_policy->designations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('designations.id'));
                                $designationIds = $data_policy->designations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('designations.id')->toArray();
                                $query->where(function ($q) use ($table, $designationIds) {
                                    foreach ($designationIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.designation_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.designation_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_grades == false && setting('grades') == 1) {
                                // $query->whereIn($table . ".grade_id", $data_policy->grades()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('grades.id'));
                                $gradeIds = $data_policy->grades()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('grades.id')->toArray();
                                $query->where(function ($q) use ($table, $gradeIds) {
                                    foreach ($gradeIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.grade_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.grade_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_units == false && setting('units') == 1) {
                                // $query->whereIn($table . ".unit_id", $data_policy->units()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('units.id'));
                                $unitIds = $data_policy->units()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('units.id')->toArray();
                                $query->where(function ($q) use ($table, $unitIds) {
                                    foreach ($unitIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.unit_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.unit_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_bus_routes == false && setting('bus_routes') == 1) {
                                // $query->whereIn($table . ".bus_route_id", $data_policy->bus_routes()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('bus_routes.id'));
                                $busRouteIds = $data_policy->bus_routes()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('bus_routes.id')->toArray();
                                $query->where(function ($q) use ($table, $busRouteIds) {
                                    foreach ($busRouteIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.bus_route_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.bus_route_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                            if ($data_policy->all_areas == false && setting('areas') == 1) {
                                $query->whereHas('areas', function ($squery) use ($data_policy) {
                                    $squery->whereIn('areas.id', $data_policy->areas()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('areas.id'));
                                });
                            }
                            if ($data_policy->machines == false && setting('machines') == 1) {
                                // $query->whereIn($table . ".machine_id", $data_policy->machines()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('machines.id'));
                                $machineIds = $data_policy->machines()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('machines.id')->toArray();
                                $query->where(function ($q) use ($table, $machineIds) {
                                    foreach ($machineIds as $id) {
                                        $q->orWhere(function ($subquery) use ($table, $id) {
                                            $subquery->whereRaw('CHARINDEX(?, '.$table.'.machine_id) > 0', ['["'.$id.'"]']);
                                        })
                                            ->orWhere(function ($subquery) use ($table, $id) {
                                                $subquery->whereRaw('TRY_CAST('.$table.'.machine_id AS INT) = ?', [$id]);
                                            });
                                    }
                                });
                            }
                        });
                    });
                } else {
                    $builder->where(function (Builder $query) use ($table) {
                        $query->where($table.'.id', '=', Auth::id());
                        // $query->orWhere($table . '.created_by', '=', Auth::id());
                    });
                }
            } else {
                $this->applySelfScope($builder, $model);
            }
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
            $query->where('shift_changes.created_by', '=', Auth::id());

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
            $query->where('week_off_changes.created_by', '=', Auth::id());

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
            $subquery->where('users.id', '=', Auth::id());
        });

        // Apply location filters
        if ($data_policy->all_locations == false) {
            $locationIds = $data_policy->locations()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('locations.id')->toArray();
            $query->orWhere(function ($q) use ($locationIds) {
                foreach ($locationIds as $id) {
                    $q->orWhere(function ($subquery) use ($id) {
                        $subquery->whereRaw('CHARINDEX(?, users.location_id) > 0', ['["'.$id.'"]']);
                    })
                        ->orWhere(function ($subquery) use ($id) {
                            $subquery->whereRaw('TRY_CAST(users.location_id AS INT) = ?', [$id]);
                        });
                }
            });
        }

        // Apply company filters
        if ($data_policy->all_companies == false && setting('companies') == 1) {
            $companyIds = $data_policy->companies()->withoutGlobalScopes([DataPolicyFilter::class])->pluck('companies.id')->toArray();
            $query->where(function ($q) use ($companyIds) {
                foreach ($companyIds as $id) {
                    $q->orWhere(function ($subquery) use ($id) {
                        $subquery->whereRaw('CHARINDEX(?, users.company_id) > 0', ['["'.$id.'"]']);
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
        if (in_array($table, ['locations', 'companies', 'departments', 'sub_departments', 'categories', 'sub_categories', 'designations', 'grades', 'units', 'bus_routes'])) { // , , , , , 'machines'])) {
            $singular = Str::singular($table);
            $property = $singular.'_id';
            $user = Auth::user();

            $builder->where(function (Builder $query) use ($table, $property, $user) {
                // Check if the property exists on the user
                if ($user != null && $property != null) {
                    $idValue = $user->{$property};

                    // Handle JSON array stored in the database
                    if (is_string($idValue) && (str_starts_with($idValue, '[') || str_starts_with($idValue, '{'))) {
                        try {
                            $ids = json_decode($idValue, true);
                            if (is_array($ids) && ! empty($ids)) {
                                $query->whereIn($table.'.id', $ids);

                                return;
                            }
                        } catch (\Exception $e) {
                            // JSON decode failed, continue with normal handling
                        }
                    }

                    // Handle already decoded array
                    if (is_array($idValue)) {
                        $query->whereIn($table.'.id', $idValue);
                    }
                    // Handle single value
                    else {
                        $query->whereIn($table.'.id', [$idValue]);
                    }
                }
            });
        } elseif ($table == 'users') {
            $id = Auth::id();
            $builder->where(function (Builder $query) use ($table) {
                $query->where($table.'.id', '=', Auth::id());
            });
        } else {
            $builder->where(function (Builder $query) use ($table) {
                $query->where($table.'.created_by', '=', Auth::id());
            });
        }
    }
}
