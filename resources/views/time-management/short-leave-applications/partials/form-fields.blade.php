@php
    $application = $application ?? null;
    $selectedUserIds = array_map('strval', (array) old('users', $selectedUserIds ?? ($application?->user_id === null ? [] : [$application->user_id])));
    $selectedType = (string) old('short_leave_type', $application?->short_leave_type ?? 1);
    $toIds = static function ($value): array {
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
    <div class="card-header"><h5 class="mb-0">Short Leave Details</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label" for="short_leave_type">Short Leave Type <span class="text-danger">*</span></label>
                <select id="short_leave_type" name="short_leave_type" class="form-control select2 @error('short_leave_type') is-invalid @enderror" required>
                    @foreach (\App\Enums\ShortLeaveType::cases() as $type)
                        <option value="{{ $type->value }}" @selected($selectedType === (string) $type->value)>{{ $type->getLabel() }}</option>
                    @endforeach
                </select>
                <x-validation-error field="short_leave_type" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                <input id="date" name="date" type="date" required
                    class="form-control @error('date') is-invalid @enderror"
                    value="{{ old('date', $application?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                <x-validation-error field="date" />
            </div>
            <div class="col-md-3 mb-3 short-leave-minutes">
                <label class="form-label" for="minutes">Minutes <span class="text-danger">*</span></label>
                <input id="minutes" name="minutes" type="number" min="1" max="1440"
                    class="form-control @error('minutes') is-invalid @enderror"
                    value="{{ old('minutes', $application?->minutes) }}">
                <x-validation-error field="minutes" />
            </div>
            <div class="col-md-3 mb-3 short-leave-times">
                <label class="form-label" for="from_time">From Time <span class="text-danger">*</span></label>
                <input id="from_time" name="from_time" type="time" step="60"
                    class="form-control @error('from_time') is-invalid @enderror"
                    value="{{ old('from_time', $application?->from_time?->format('H:i')) }}">
                <x-validation-error field="from_time" />
            </div>
            <div class="col-md-3 mb-3 short-leave-times">
                <label class="form-label" for="to_time">To Time <span class="text-danger">*</span></label>
                <input id="to_time" name="to_time" type="time" step="60"
                    class="form-control @error('to_time') is-invalid @enderror"
                    value="{{ old('to_time', $application?->to_time?->format('H:i')) }}">
                <x-validation-error field="to_time" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="reason">Reason</label>
                <textarea id="reason" name="reason" rows="2" maxlength="255"
                    class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $application?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
        <small class="text-muted">For break between working hours, minutes are calculated from the selected time range.</small>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Filtration And Employees</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="short_leave_application_companies">Companies</label>
                <select id="short_leave_application_companies" name="company_ids[]" class="form-control select2" multiple>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, array_map('strval', (array) old('company_ids', [])), true))>
                            {{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Select an organization scope or leave empty to choose employees directly.</small>
                <x-validation-error field="company_ids" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="short_leave_application_locations">Locations</label>
                <select id="short_leave_application_locations" name="location_ids[]" class="form-control select2" multiple>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, array_map('strval', (array) old('location_ids', [])), true))>
                            {{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="location_ids" />
            </div>
            @foreach ($employeeFilterFields as $field => $filter)
                @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="short_leave_application_filter_{{ $field }}">{{ $filter['label'] }}</label>
                    <select id="short_leave_application_filter_{{ $field }}" name="employee_filters[{{ $field }}][]"
                        class="form-control select2 short-leave-employee-filter" multiple>
                        @foreach ($filter['options'] as $option)
                            <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedFilterIds, true))>
                                {{ $option->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-validation-error field="employee_filters.{{ $field }}" />
                </div>
            @endforeach
            <div class="col-12 mb-3">
                <label class="form-label" for="short_leave_application_users">Employees <span class="text-danger">*</span></label>
                <select id="short_leave_application_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple>
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
                            @selected(in_array((string) $user->id, $selectedUserIds, true))>
                            {{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    Select employees directly or leave empty to include employees matching the selected filters.
                    @if ($application)
                        Editing updates one employee's application; select exactly one employee.
                    @endif
                </small>
                <x-validation-error field="users" />
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var type = document.getElementById('short_leave_type');
        if (!type) return;
        var minutes = document.querySelector('.short-leave-minutes');
        var times = document.querySelectorAll('.short-leave-times');
        var minutesInput = document.getElementById('minutes');
        var fromInput = document.getElementById('from_time');
        var toInput = document.getElementById('to_time');

        function updateFields() {
            var isBreak = type.value === '3';
            minutes.hidden = isBreak;
            times.forEach(function (field) { field.hidden = !isBreak; });
            minutesInput.required = !isBreak;
            fromInput.required = isBreak;
            toInput.required = isBreak;
        }

        type.addEventListener('change', updateFields);
        updateFields();

        var employeeSelect = document.getElementById('short_leave_application_users');
        if (!employeeSelect) return;

        var filters = [
            { selector: '#short_leave_application_companies', attribute: 'companyIds' },
            { selector: '#short_leave_application_locations', attribute: 'locationIds' },
            { selector: '#short_leave_application_filter_department_id', attribute: 'departmentIds' },
            { selector: '#short_leave_application_filter_sub_department_id', attribute: 'subDepartmentIds' },
            { selector: '#short_leave_application_filter_category_id', attribute: 'categoryIds' },
            { selector: '#short_leave_application_filter_sub_category_id', attribute: 'subCategoryIds' },
            { selector: '#short_leave_application_filter_designation_id', attribute: 'designationIds' },
            { selector: '#short_leave_application_filter_grade_id', attribute: 'gradeIds' },
            { selector: '#short_leave_application_filter_unit_id', attribute: 'unitIds' },
            { selector: '#short_leave_application_filter_bus_route_id', attribute: 'busRouteIds' }
        ];

        function getActiveFilters() {
            return filters.map(function (filter) {
                var select = document.querySelector(filter.selector);
                return {
                    attribute: filter.attribute,
                    values: select ? Array.from(select.selectedOptions).map(function (option) { return option.value; }) : []
                };
            }).filter(function (filter) { return filter.values.length > 0; });
        }

        function matchesActiveFilters(option, activeFilters) {
            return activeFilters.every(function (filter) {
                var ids = (option.dataset[filter.attribute] || '').split(',').filter(Boolean);
                return filter.values.some(function (value) { return ids.includes(value); });
            });
        }

        function applyEmployeeFilters() {
            var activeFilters = getActiveFilters();

            Array.from(employeeSelect.options).forEach(function (option) {
                var matches = matchesActiveFilters(option, activeFilters);
                option.hidden = !matches && !option.selected;
                option.disabled = !matches && !option.selected;
                if (employeeSelect.tomselect && employeeSelect.tomselect.options[option.value]) {
                    employeeSelect.tomselect.updateOption(option.value, {
                        ...employeeSelect.tomselect.options[option.value],
                        disabled: option.disabled
                    });
                }
            });
            if (employeeSelect.tomselect) employeeSelect.tomselect.refreshOptions(false);
        }

        function syncFiltersWithEmployees() {
            var selectedEmployees = Array.from(employeeSelect.selectedOptions);
            if (selectedEmployees.length === 0) {
                applyEmployeeFilters();
                return;
            }

            filters.forEach(function (filter) {
                var select = document.querySelector(filter.selector);
                if (!select) return;

                var values = new Set();
                selectedEmployees.forEach(function (employee) {
                    (employee.dataset[filter.attribute] || '')
                        .split(',')
                        .filter(Boolean)
                        .forEach(function (value) { values.add(value); });
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
        }

        filters.forEach(function (filter) {
            var select = document.querySelector(filter.selector);
            if (select) select.addEventListener('change', applyEmployeeFilters);
        });
        employeeSelect.addEventListener('change', syncFiltersWithEmployees);
        syncFiltersWithEmployees();
    })();
</script>
@endpush
