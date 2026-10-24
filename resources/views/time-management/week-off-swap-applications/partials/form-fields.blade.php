@php
    $weekOffSwapApplication = $weekOffSwapApplication ?? null;
    $selectedUsers = array_map('strval', (array) old('users', $weekOffSwapApplication?->Users->pluck('id')->all() ?? []));
    $selectedCompanies = array_map('strval', (array) old('company_ids', []));
    $selectedLocations = array_map('strval', (array) old('location_ids', []));
    $toIds = static function ($value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_map('strval', (array) $value);
    };
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
    <div class="card-header"><h5 class="card-title mb-0">Week-off Swap Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="week_off_swap_application_week_date">Week Date <span class="text-danger">*</span></label>
                <input id="week_off_swap_application_week_date" name="week_date" type="date" required
                    class="form-control @error('week_date') is-invalid @enderror"
                    value="{{ old('week_date', $weekOffSwapApplication?->week_date?->format('Y-m-d')) }}">
                <x-validation-error field="week_date" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="week_off_swap_application_swap_date">Swap Date <span class="text-danger">*</span></label>
                <input id="week_off_swap_application_swap_date" name="swap_date" type="date" required
                    class="form-control @error('swap_date') is-invalid @enderror"
                    value="{{ old('swap_date', $weekOffSwapApplication?->swap_date?->format('Y-m-d')) }}">
                <x-validation-error field="swap_date" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="week_off_swap_application_reason">Reason</label>
                <textarea id="week_off_swap_application_reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $weekOffSwapApplication?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Filtration and Employees</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="week_off_swap_application_companies">Companies</label>
                <select id="week_off_swap_application_companies" name="company_ids[]" class="form-control select2" multiple>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanies, true))>{{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Select a scope or choose employees directly.</small>
                <x-validation-error field="company_ids" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="week_off_swap_application_locations">Locations</label>
                <select id="week_off_swap_application_locations" name="location_ids[]" class="form-control select2" multiple>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocations, true))>{{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}</option>
                    @endforeach
                </select>
                <x-validation-error field="location_ids" />
            </div>
            @foreach ($employeeFilterFields as $field => $filter)
                @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="week_off_swap_application_filter_{{ $field }}">{{ $filter['label'] }}</label>
                    <select id="week_off_swap_application_filter_{{ $field }}" name="employee_filters[{{ $field }}][]" class="form-control select2 week-off-swap-application-employee-filter" multiple>
                        @foreach ($filter['options'] as $option)
                            <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedFilterIds, true))>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-12 mb-3">
                <label class="form-label" for="week_off_swap_application_users">Employees <span class="text-danger">*</span></label>
                <select id="week_off_swap_application_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple>
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
                <small class="form-text text-muted">Choose employees or leave empty to include all employees matching the filters.</small>
                <x-validation-error field="users" />
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090">
    <div id="week_off_swap_application_week_off_toast" class="toast text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex"><div class="toast-body" id="week_off_swap_application_week_off_toast_body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const employeeSelect = document.getElementById('week_off_swap_application_users');
    const weekDateInput = document.getElementById('week_off_swap_application_week_date');
    const toastElement = document.getElementById('week_off_swap_application_week_off_toast');
    const toastBody = document.getElementById('week_off_swap_application_week_off_toast_body');
    const toast = new window.bootstrap.Toast(toastElement, { delay: 7000 });
    const checkWeekOffUrl = @json(url('time-management/week-off-swap-applications/check-week-off'));
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    const filters = [
        { selector: '#week_off_swap_application_companies', attribute: 'companyIds' },
        { selector: '#week_off_swap_application_locations', attribute: 'locationIds' },
        { selector: '#week_off_swap_application_filter_department_id', attribute: 'departmentIds' },
        { selector: '#week_off_swap_application_filter_sub_department_id', attribute: 'subDepartmentIds' },
        { selector: '#week_off_swap_application_filter_category_id', attribute: 'categoryIds' },
        { selector: '#week_off_swap_application_filter_sub_category_id', attribute: 'subCategoryIds' },
        { selector: '#week_off_swap_application_filter_designation_id', attribute: 'designationIds' },
        { selector: '#week_off_swap_application_filter_grade_id', attribute: 'gradeIds' },
        { selector: '#week_off_swap_application_filter_unit_id', attribute: 'unitIds' },
        { selector: '#week_off_swap_application_filter_bus_route_id', attribute: 'busRouteIds' },
    ];
    const applyEmployeeFilters = function () {
        const activeFilters = filters.map(function (filter) {
            const select = document.querySelector(filter.selector);
            return {
                attribute: filter.attribute,
                values: select ? Array.from(select.selectedOptions).map(function (option) { return option.value; }) : [],
            };
        }).filter(function (filter) { return filter.values.length > 0; });

        Array.from(employeeSelect.options).forEach(function (option) {
            const matches = activeFilters.every(function (filter) {
                const ids = (option.dataset[filter.attribute] || '').split(',').filter(Boolean);
                return filter.values.some(function (value) { return ids.includes(value); });
            });
            option.hidden = !matches && !option.selected;
            option.disabled = !matches && !option.selected;
            if (employeeSelect.tomselect && employeeSelect.tomselect.options[option.value]) {
                employeeSelect.tomselect.updateOption(option.value, {
                    ...employeeSelect.tomselect.options[option.value],
                    disabled: option.disabled,
                });
            }
        });
        if (employeeSelect.tomselect) employeeSelect.tomselect.refreshOptions(false);
    };
    filters.forEach(function (filter) {
        const select = document.querySelector(filter.selector);
        if (select) select.addEventListener('change', applyEmployeeFilters);
    });
    const syncFiltersWithEmployees = function () {
        const selectedEmployees = Array.from(employeeSelect.selectedOptions);
        if (selectedEmployees.length === 0) {
            applyEmployeeFilters();
            return;
        }
        filters.forEach(function (filter) {
            const select = document.querySelector(filter.selector);
            if (!select) return;
            const values = new Set();
            selectedEmployees.forEach(function (employee) {
                (employee.dataset[filter.attribute] || '').split(',').filter(Boolean).forEach(function (value) { values.add(value); });
            });
            if (select.tomselect) {
                select.tomselect.setValue(select.multiple ? Array.from(values) : (values.size === 1 ? Array.from(values)[0] : ''), true);
            } else if (select.multiple) {
                Array.from(select.options).forEach(function (option) { option.selected = values.has(option.value); });
            }
        });
        applyEmployeeFilters();
    };
    employeeSelect.addEventListener('change', syncFiltersWithEmployees);

    let activeRequest = null;
    let requestSequence = 0;
    const checkSelectedEmployeesWeekOff = function () {
        const sequence = ++requestSequence;
        if (activeRequest) activeRequest.abort();
        toast.hide();
        const weekDate = weekDateInput.value;
        const userIds = Array.from(employeeSelect.selectedOptions).map(function (option) { return option.value; });
        if (!weekDate || userIds.length === 0) return;

        activeRequest = new AbortController();
        fetch(checkWeekOffUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ week_date: weekDate, users: userIds }),
            signal: activeRequest.signal,
        }).then(function (response) {
            if (!response.ok) throw new Error('Week-off verification failed with status ' + response.status);
            return response.json();
        }).then(function (result) {
            if (sequence !== requestSequence || result.has_week_off) return;
            const names = result.employees_without_week_off.map(function (employee) {
                return employee.name + (employee.code ? ' (' + employee.code + ')' : '');
            }).slice(0, 5).join(', ');
            toastBody.textContent = 'No week-off exists for ' + names + ' on ' + weekDate + '.';
            toast.show();
        }).catch(function (error) {
            if (error.name === 'AbortError' || sequence !== requestSequence) return;
            console.error(error);
            toastBody.textContent = 'Unable to verify the selected employees\' week-off. Please try again.';
            toast.show();
        });
    };
    weekDateInput.addEventListener('change', checkSelectedEmployeesWeekOff);
    employeeSelect.addEventListener('change', checkSelectedEmployeesWeekOff);
    syncFiltersWithEmployees();
    checkSelectedEmployeesWeekOff();
});
</script>
@endpush
