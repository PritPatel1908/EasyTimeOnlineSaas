@php
    $attendance = $attendance ?? null;
    $dateTimeValue = static function ($value) {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d\TH:i');
        }

        return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : '';
    };
    $toIds = static function ($value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_map('strval', (array) $value);
    };
    $selectedUsers = array_map('strval', (array) old('users', $attendance?->Users->pluck('id')->all() ?? []));
    $selectedCompanies = array_map('strval', (array) old(
        'company_ids',
        $toIds($attendance?->company_ids),
    ));
    $selectedLocations = array_map('strval', (array) old(
        'location_ids',
        $toIds($attendance?->location_ids),
    ));
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
    <div class="card-header"><h5 class="card-title mb-0">Attendance Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_in_time">In Time <span class="text-danger">*</span></label>
                <input id="manual_attendance_in_time" name="in_time" type="datetime-local" step="1" required
                    class="form-control @error('in_time') is-invalid @enderror"
                    value="{{ old('in_time', $dateTimeValue($attendance?->in_time)) }}">
                <x-validation-error field="in_time" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_out_time">Out Time <span class="text-danger">*</span></label>
                <input id="manual_attendance_out_time" name="out_time" type="datetime-local" step="1" required
                    class="form-control @error('out_time') is-invalid @enderror"
                    value="{{ old('out_time', $dateTimeValue($attendance?->out_time)) }}">
                <x-validation-error field="out_time" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_attendance_reason">Reason</label>
                <textarea id="manual_attendance_reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $attendance?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090">
    <div id="manual_attendance_time_range_toast" class="toast text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">Out Time must be within 24 hours of In Time.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Filtration and Employees</h5></div>
    @if ($attendance !== null)
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
        <fieldset class="manual-attendance-organization-fields border-0 p-0 m-0 w-100" @disabled($attendance !== null)>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_companies">Companies</label>
                <select id="manual_attendance_companies" name="company_ids[]" class="form-control select2 @error('company_ids') is-invalid @enderror" multiple @disabled($attendance !== null)>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanies, true))>
                            {{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Saved with the attendance and used to filter employees.</small>
                <x-validation-error field="company_ids" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_locations">Locations</label>
                <select id="manual_attendance_locations" name="location_ids[]" class="form-control select2 @error('location_ids') is-invalid @enderror" multiple @disabled($attendance !== null)>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocations, true))>
                            {{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Saved with the attendance and used to filter employees.</small>
                <x-validation-error field="location_ids" />
            </div>
            @foreach ($employeeFilterFields as $field => $filter)
                @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="manual_attendance_employee_filter_{{ $field }}">{{ $filter['label'] }}</label>
                    <select id="manual_attendance_employee_filter_{{ $field }}" name="employee_filters[{{ $field }}][]"
                        class="form-control select2 manual-attendance-employee-filter" multiple @disabled($attendance !== null)>
                        @foreach ($filter['options'] as $option)
                            <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedFilterIds, true))>
                                {{ $option->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_attendance_users">Employees</label>
                <select id="manual_attendance_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple @disabled($attendance !== null)>
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
                <small class="form-text text-muted">Leave empty to assign the attendance to all employees matching the selected filters, or to all employees if no filters are selected.</small>
                <x-validation-error field="users" />
            </div>
        </div>
        </fieldset>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inTime = document.getElementById('manual_attendance_in_time');
            const outTime = document.getElementById('manual_attendance_out_time');
            const timeRangeToast = document.getElementById('manual_attendance_time_range_toast');
            let durationToast;
            let toastRestartPending = false;

            function showTimeRangeToast() {
                if (!timeRangeToast || !window.bootstrap?.Toast) {
                    return;
                }

                durationToast = durationToast || new window.bootstrap.Toast(timeRangeToast);
                if (!timeRangeToast.classList.contains('show')) {
                    durationToast.show();
                    return;
                }

                if (!toastRestartPending) {
                    toastRestartPending = true;
                    timeRangeToast.addEventListener('hidden.bs.toast', function () {
                        toastRestartPending = false;
                        durationToast.show();
                    }, { once: true });
                }
                durationToast.hide();
            }

            function validateTimeRange() {
                outTime.setCustomValidity('');
                if (!inTime.value || !outTime.value) {
                    return;
                }

                const start = new Date(inTime.value).getTime();
                const end = new Date(outTime.value).getTime();
                const exceedsLimit = Number.isFinite(start)
                    && Number.isFinite(end)
                    && end - start > 24 * 60 * 60 * 1000;

                if (exceedsLimit) {
                    outTime.setCustomValidity('Out Time must be within 24 hours of In Time.');
                    showTimeRangeToast();
                }
            }

            inTime.addEventListener('input', validateTimeRange);
            inTime.addEventListener('change', validateTimeRange);
            outTime.addEventListener('input', validateTimeRange);
            outTime.addEventListener('change', validateTimeRange);
            validateTimeRange();

            const employeeSelect = document.getElementById('manual_attendance_users');
            const filters = [
                { selector: '#manual_attendance_companies', attribute: 'companyIds' },
                { selector: '#manual_attendance_locations', attribute: 'locationIds' },
                { selector: '#manual_attendance_employee_filter_department_id', attribute: 'departmentIds' },
                { selector: '#manual_attendance_employee_filter_sub_department_id', attribute: 'subDepartmentIds' },
                { selector: '#manual_attendance_employee_filter_category_id', attribute: 'categoryIds' },
                { selector: '#manual_attendance_employee_filter_sub_category_id', attribute: 'subCategoryIds' },
                { selector: '#manual_attendance_employee_filter_designation_id', attribute: 'designationIds' },
                { selector: '#manual_attendance_employee_filter_grade_id', attribute: 'gradeIds' },
                { selector: '#manual_attendance_employee_filter_unit_id', attribute: 'unitIds' },
                { selector: '#manual_attendance_employee_filter_bus_route_id', attribute: 'busRouteIds' },
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

                    const value = Array.from(values);
                    if (select.tomselect) {
                        select.tomselect.setValue(select.multiple ? value : (value.length === 1 ? value[0] : ''), true);
                    } else if (select.multiple) {
                        Array.from(select.options).forEach(function (option) {
                            option.selected = values.has(option.value);
                        });
                    } else {
                        select.value = value.length === 1 ? value[0] : '';
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
