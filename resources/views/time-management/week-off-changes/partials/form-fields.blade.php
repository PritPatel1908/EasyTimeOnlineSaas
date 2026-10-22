@php
    $weekOffChange = $weekOffChange ?? null;
    $dateValue = static function ($value) {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d');
        }

        return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
    };
    $toIds = static function ($value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_map('strval', (array) $value);
    };
    $selectedUsers = array_map('strval', (array) old('users', $weekOffChange?->Users->pluck('id')->all() ?? []));
    $selectedCompanies = array_map('strval', (array) old('company_ids', []));
    $selectedLocations = array_map('strval', (array) old('location_ids', []));
    $detailRows = old('details', $weekOffChange?->WeekOffChangeDetails->map(fn ($detail) => $detail->toArray())->all() ?? []);
    $detailRows = $detailRows ?: [[]];
    $isForever = (bool) old('is_forever', $weekOffChange?->is_forever ?? false);
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
    $weekOffWeekFields = [
        'first_week' => '1st Week',
        'second_week' => '2nd Week',
        'third_week' => '3rd Week',
        'fourth_week' => '4th Week',
        'fifth_week' => '5th Week',
    ];
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Week-off Change Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" for="week_off_change_from_date">From Date <span class="text-danger">*</span></label>
                <input id="week_off_change_from_date" name="from_date" type="date" required
                    class="form-control @error('from_date') is-invalid @enderror"
                    value="{{ old('from_date', $dateValue($weekOffChange?->from_date)) }}">
                <x-validation-error field="from_date" />
            </div>
            <div class="col-md-4 mb-3 week-off-to-date-field @class(['d-none' => $isForever])">
                <label class="form-label" for="week_off_change_to_date">To Date <span class="text-danger">*</span></label>
                <input id="week_off_change_to_date" name="to_date" type="date" @required(!$isForever)
                    class="form-control @error('to_date') is-invalid @enderror"
                    value="{{ old('to_date', $dateValue($weekOffChange?->to_date)) }}">
                <x-validation-error field="to_date" />
            </div>
            <div class="col-md-4 mb-3 d-flex align-items-end">
                <div class="form-check form-check-lg form-switch mb-2">
                    <input type="hidden" name="is_forever" value="0">
                    <input id="week_off_change_is_forever" name="is_forever" type="checkbox" role="switch" value="1" class="form-check-input" @checked($isForever)>
                    <label class="form-check-label" for="week_off_change_is_forever">No end date</label>
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="week_off_change_reason">Reason</label>
                <textarea id="week_off_change_reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $weekOffChange?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="border rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Weekly Off Details</h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-week-off-detail"><i class="ti ti-plus me-1"></i>Add Day</button>
            </div>
            <x-validation-error field="details" />
            <div id="week-off-detail-rows">
                @foreach ($detailRows as $index => $detail)
                    <div class="week-off-detail-row border rounded p-3 mb-3" data-detail-index="{{ $index }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Day <span class="week-off-day-number">{{ $index + 1 }}</span></h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-week-off-detail"><i class="ti ti-trash me-1"></i>Remove</button>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Day</label>
                                <select name="details[{{ $index }}][week_days]" class="form-control @error('details.'.$index.'.week_days') is-invalid @enderror" required>
                                    <option value="">Select day</option>
                                    @foreach ($weekDays as $day => $label)
                                        <option value="{{ $day }}" @selected((string) data_get($detail, 'week_days') === (string) $day)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-validation-error field="details.{{ $index }}.week_days" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Week-off Type</label>
                                <select name="details[{{ $index }}][wo_type]" class="form-control @error('details.'.$index.'.wo_type') is-invalid @enderror" required>
                                    <option value="">Select type</option>
                                    @foreach ($weekOffTypes as $type => $label)
                                        <option value="{{ $type }}" @selected((string) data_get($detail, 'wo_type', 1) === (string) $type)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-validation-error field="details.{{ $index }}.wo_type" />
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label d-block">Applicable Weeks</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($weekOffWeekFields as $field => $label)
                                        <label class="form-check form-check-inline mb-0">
                                            <input type="hidden" name="details[{{ $index }}][{{ $field }}]" value="0">
                                            <input class="form-check-input" type="checkbox" name="details[{{ $index }}][{{ $field }}]" value="1" @checked((bool) data_get($detail, $field, false))>
                                            <span class="form-check-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        <template id="week-off-detail-template">
            <div class="week-off-detail-row border rounded p-3 mb-3" data-detail-index="__INDEX__">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Day <span class="week-off-day-number"></span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-week-off-detail"><i class="ti ti-trash me-1"></i>Remove</button>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Day</label>
                        <select name="details[__INDEX__][week_days]" class="form-control" required>
                            <option value="">Select day</option>
                            @foreach ($weekDays as $day => $label)<option value="{{ $day }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Week-off Type</label>
                        <select name="details[__INDEX__][wo_type]" class="form-control" required>
                            <option value="">Select type</option>
                            @foreach ($weekOffTypes as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label d-block">Applicable Weeks</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($weekOffWeekFields as $field => $label)
                                <label class="form-check form-check-inline mb-0">
                                    <input type="hidden" name="details[__INDEX__][{{ $field }}]" value="0">
                                    <input class="form-check-input" type="checkbox" name="details[__INDEX__][{{ $field }}]" value="1">
                                    <span class="form-check-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </template>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Filtration and Employees</h5></div>
    @if ($weekOffChange !== null)
        @foreach ($selectedUsers as $userId)
            <input type="hidden" name="users[]" value="{{ $userId }}">
        @endforeach
    @endif
    <div class="card-body">
        <fieldset class="week-off-change-employee-fields border-0 p-0 m-0 w-100" @disabled($weekOffChange !== null)>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="week_off_change_companies">Companies</label>
                    <select id="week_off_change_companies" name="company_ids[]" class="form-control select2" multiple @disabled($weekOffChange !== null)>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanies, true))>{{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Select a scope or leave empty to choose employees directly.</small>
                    <x-validation-error field="company_ids" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="week_off_change_locations">Locations</label>
                    <select id="week_off_change_locations" name="location_ids[]" class="form-control select2" multiple @disabled($weekOffChange !== null)>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocations, true))>{{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}</option>
                        @endforeach
                    </select>
                    <x-validation-error field="location_ids" />
                </div>
                @foreach ($employeeFilterFields as $field => $filter)
                    @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="week_off_change_filter_{{ $field }}">{{ $filter['label'] }}</label>
                        <select id="week_off_change_filter_{{ $field }}" name="employee_filters[{{ $field }}][]" class="form-control select2 week-off-change-employee-filter" multiple @disabled($weekOffChange !== null)>
                            @foreach ($filter['options'] as $option)
                                <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedFilterIds, true))>{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                <div class="col-12 mb-3">
                    <label class="form-label" for="week_off_change_users">Employees</label>
                    <select id="week_off_change_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple @disabled($weekOffChange !== null)>
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
                    <small class="form-text text-muted">Leave empty to assign this week-off change to all employees matching the selected filters, or all employees when no filters are selected.</small>
                    <x-validation-error field="users" />
                </div>
            </div>
        </fieldset>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const foreverCheckbox = document.getElementById('week_off_change_is_forever');
    const toDateField = document.querySelector('.week-off-to-date-field');
    const toDateInput = document.getElementById('week_off_change_to_date');
    const updateDateFields = function () {
        toDateField.classList.toggle('d-none', foreverCheckbox.checked);
        toDateInput.required = !foreverCheckbox.checked;
        toDateInput.disabled = foreverCheckbox.checked;
    };
    foreverCheckbox.addEventListener('change', updateDateFields);
    updateDateFields();

    const detailRows = document.getElementById('week-off-detail-rows');
    const detailTemplate = document.getElementById('week-off-detail-template');
    let nextDetailIndex = Array.from(detailRows.querySelectorAll('.week-off-detail-row'))
        .reduce(function (nextIndex, row) {
            return Math.max(nextIndex, Number(row.dataset.detailIndex) + 1);
        }, 0);
    const refreshDetailNumbers = function () {
        detailRows.querySelectorAll('.week-off-detail-row').forEach(function (row, index) {
            row.querySelector('.week-off-day-number').textContent = index + 1;
        });
    };
    document.getElementById('add-week-off-detail').addEventListener('click', function () {
        detailRows.insertAdjacentHTML('beforeend', detailTemplate.innerHTML.replaceAll('__INDEX__', nextDetailIndex++));
        refreshDetailNumbers();
    });
    detailRows.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-week-off-detail');
        if (!button) return;
        if (detailRows.querySelectorAll('.week-off-detail-row').length === 1) {
            window.alert('At least one weekly-off detail is required.');
            return;
        }
        button.closest('.week-off-detail-row').remove();
        refreshDetailNumbers();
    });
    refreshDetailNumbers();

    const employeeSelect = document.getElementById('week_off_change_users');
    const filters = [
        { selector: '#week_off_change_companies', attribute: 'companyIds' },
        { selector: '#week_off_change_locations', attribute: 'locationIds' },
        { selector: '#week_off_change_filter_department_id', attribute: 'departmentIds' },
        { selector: '#week_off_change_filter_sub_department_id', attribute: 'subDepartmentIds' },
        { selector: '#week_off_change_filter_category_id', attribute: 'categoryIds' },
        { selector: '#week_off_change_filter_sub_category_id', attribute: 'subCategoryIds' },
        { selector: '#week_off_change_filter_designation_id', attribute: 'designationIds' },
        { selector: '#week_off_change_filter_grade_id', attribute: 'gradeIds' },
        { selector: '#week_off_change_filter_unit_id', attribute: 'unitIds' },
        { selector: '#week_off_change_filter_bus_route_id', attribute: 'busRouteIds' },
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
                const employeeIds = (option.dataset[filter.attribute] || '').split(',').filter(Boolean);
                return filter.values.some(function (value) { return employeeIds.includes(value); });
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
    filters.forEach(function (filter) {
        const select = document.querySelector(filter.selector);
        if (select) select.addEventListener('change', applyEmployeeFilters);
    });
    employeeSelect.addEventListener('change', syncFiltersWithEmployees);
    syncFiltersWithEmployees();
});
</script>
@endpush
