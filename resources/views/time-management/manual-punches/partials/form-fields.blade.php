@php
    $punch = $punch ?? null;
    $toIds = static function ($value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_map('strval', (array) $value);
    };
    $selectedUsers = array_map('strval', (array) old('users', $punch?->Users->pluck('id')->all() ?? []));
    $selectedLocations = $toIds(old('location_ids', $punch?->location_id ?? []));
    $selectedCompanies = $toIds(old('company_ids', $punch?->company_id ?? []));
    $selectedPunchType = \App\Models\Tenant\ManualPunch::normalizePunchType(old('punch_type', $punch?->punch_type));
    $employeeFilterFields = [
        'department_id' => ['label' => 'Department', 'options' => $departments],
        'sub_department_id' => ['label' => 'Sub Department', 'options' => $subDepartments],
        'category_id' => ['label' => 'Category', 'options' => $categories],
        'sub_category_id' => ['label' => 'Sub Category', 'options' => $subCategories],
        'designation_id' => ['label' => 'Designation', 'options' => $designations],
        'grade_id' => ['label' => 'Grade', 'options' => $grades],
        'unit_id' => ['label' => 'Unit', 'options' => $units],
        'bus_route_id' => ['label' => 'Bus Route', 'options' => $busRoutes],
    ];
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Punch Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label" for="punch_type">Punch Type <span class="text-danger">*</span></label>
                <select id="punch_type" name="punch_type" class="form-control select2 @error('punch_type') is-invalid @enderror" required>
                    <option value="">Select</option>
                    @foreach (\App\Models\Tenant\ManualPunch::PUNCH_TYPES as $value => $label)
                        <option value="{{ $value }}" @selected($selectedPunchType === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-validation-error field="punch_type" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="punch_date">Punch Date <span class="text-danger">*</span></label>
                <input id="punch_date" name="punch_date" type="date" required
                    class="form-control @error('punch_date') is-invalid @enderror"
                    value="{{ old('punch_date', $punch?->punch_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                <x-validation-error field="punch_date" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="punch_time">Punch Time <span class="text-danger">*</span></label>
                <input id="punch_time" name="punch_time" type="time" step="1" required
                    class="form-control @error('punch_time') is-invalid @enderror"
                    value="{{ old('punch_time', $punch?->punch_time?->format('H:i:s') ?? now()->format('H:i:s')) }}">
                <x-validation-error field="punch_time" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_punch_reason">Reason</label>
                <textarea id="manual_punch_reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $punch?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Organization</h5></div>
    @if ($punch !== null)
        @foreach ($selectedCompanies as $companyId)
            <input type="hidden" name="company_ids[]" value="{{ $companyId }}">
        @endforeach
        @foreach ($selectedLocations as $locationId)
            <input type="hidden" name="location_ids[]" value="{{ $locationId }}">
        @endforeach
        @foreach ($selectedUsers as $userId)
            <input type="hidden" name="users[]" value="{{ $userId }}">
        @endforeach
    @endif
    <div class="card-body">
        <fieldset class="manual-punch-organization-fields border-0 p-0 m-0 w-100" @disabled($punch !== null)>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_punch_companies">Companies</label>
                <select id="manual_punch_companies" name="company_ids[]" class="form-control select2 @error('company_ids') is-invalid @enderror" multiple @disabled($punch !== null)>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanies, true))>
                            {{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Saved with the punch and used to filter employees.</small>
                <x-validation-error field="company_ids" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_punch_locations">Locations</label>
                <select id="manual_punch_locations" name="location_ids[]" class="form-control select2 @error('location_ids') is-invalid @enderror" multiple @disabled($punch !== null)>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocations, true))>
                            {{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Saved with the punch and used to filter employees.</small>
                <x-validation-error field="location_ids" />
            </div>
            @foreach ($employeeFilterFields as $field => $filter)
                @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="employee_filter_{{ $field }}">{{ $filter['label'] }}</label>
                    <select id="employee_filter_{{ $field }}" name="employee_filters[{{ $field }}][]"
                        class="form-control select2 manual-punch-employee-filter" multiple @disabled($punch !== null)>
                        @foreach ($filter['options'] as $option)
                            <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedFilterIds, true))>
                                {{ $option->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_punch_users">Employees</label>
                <select id="manual_punch_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple @disabled($punch !== null)>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            data-company-ids="{{ implode(',', $toIds($user->company_id)) }}"
                            data-location-ids="{{ implode(',', $toIds($user->location_id)) }}"
                            data-department-ids="{{ implode(',', $toIds($user->department_id)) }}"
                            data-sub-department-ids="{{ implode(',', $toIds($user->sub_department_id)) }}"
                            data-category-ids="{{ implode(',', $toIds($user->category_id)) }}"
                            data-sub-category-ids="{{ implode(',', $toIds($user->sub_category_id)) }}"
                            data-designation-ids="{{ implode(',', $toIds($user->designation_id)) }}"
                            data-grade-ids="{{ implode(',', $toIds($user->grade_id)) }}"
                            data-unit-ids="{{ implode(',', $toIds($user->unit_id)) }}"
                            data-bus-route-ids="{{ implode(',', $toIds($user->bus_route_id)) }}"
                            @selected(in_array((string) $user->id, $selectedUsers, true))>
                            {{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Leave empty to assign the punch to all employees matching the selected filters, or to all employees if no filters are selected.</small>
                <x-validation-error field="users" />
            </div>
        </div>
        </fieldset>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const employeeSelect = document.getElementById('manual_punch_users');
                    const filters = [
                        { selector: '#manual_punch_companies', attribute: 'companyIds' },
                        { selector: '#manual_punch_locations', attribute: 'locationIds' },
                        { selector: '#employee_filter_department_id', attribute: 'departmentIds' },
                        { selector: '#employee_filter_sub_department_id', attribute: 'subDepartmentIds' },
                        { selector: '#employee_filter_category_id', attribute: 'categoryIds' },
                        { selector: '#employee_filter_sub_category_id', attribute: 'subCategoryIds' },
                        { selector: '#employee_filter_designation_id', attribute: 'designationIds' },
                        { selector: '#employee_filter_grade_id', attribute: 'gradeIds' },
                        { selector: '#employee_filter_unit_id', attribute: 'unitIds' },
                        { selector: '#employee_filter_bus_route_id', attribute: 'busRouteIds' },
                    ];

                    if (!employeeSelect) {
                        return;
                    }

                    const applyEmployeeFilters = function () {
                        const employeeTomSelect = employeeSelect.tomselect;
                        const activeFilters = filters
                            .map(function (filter) {
                                const select = document.querySelector(filter.selector);
                                return {
                                    attribute: filter.attribute,
                                    values: select ? Array.from(select.selectedOptions).map(function (option) {
                                        return option.value;
                                    }) : [],
                                };
                            })
                            .filter(function (filter) {
                                return filter.values.length > 0;
                            });

                        Array.from(employeeSelect.options).forEach(function (option) {
                            const matches = activeFilters.every(function (filter) {
                                const employeeIds = (option.dataset[filter.attribute] || '').split(',').filter(Boolean);
                                return filter.values.some(function (value) {
                                    return employeeIds.includes(value);
                                });
                            });

                            option.hidden = !matches && !option.selected;
                            option.disabled = !matches && !option.selected;

                            if (employeeTomSelect && employeeTomSelect.options[option.value]) {
                                employeeTomSelect.updateOption(option.value, {
                                    ...employeeTomSelect.options[option.value],
                                    disabled: option.disabled,
                                });
                            }
                        });

                        if (employeeTomSelect) {
                            employeeTomSelect.refreshOptions(false);
                        }
                    };

                    const syncFiltersWithEmployees = function () {
                        const selectedEmployees = Array.from(employeeSelect.selectedOptions);

                        if (selectedEmployees.length === 0) {
                            applyEmployeeFilters();
                            return;
                        }

                        filters.forEach(function (filter) {
                            const select = document.querySelector(filter.selector);
                            if (!select) {
                                return;
                            }

                            const values = new Set();
                            selectedEmployees.forEach(function (employee) {
                                (employee.dataset[filter.attribute] || '')
                                    .split(',')
                                    .filter(Boolean)
                                    .forEach(function (value) {
                                        values.add(value);
                                    });
                            });

                            if (select.tomselect) {
                                select.tomselect.setValue(Array.from(values), true);
                            } else {
                                Array.from(select.options).forEach(function (option) {
                                    option.selected = values.has(option.value);
                                });
                            }
                        });

                        applyEmployeeFilters();
                    };

                    filters.forEach(function (filter) {
                        const select = document.querySelector(filter.selector);
                        if (select) {
                            select.addEventListener('change', applyEmployeeFilters);
                        }
                    });

                    employeeSelect.addEventListener('change', syncFiltersWithEmployees);
                    syncFiltersWithEmployees();
                });
            </script>
        @endpush
    </div>
</div>
