@php
    $shiftChange = $shiftChange ?? null;
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
    $selectedUsers = array_map('strval', (array) old('users', $shiftChange?->Users->pluck('id')->all() ?? []));
    $selectedCompanies = array_map('strval', (array) old('company_ids', []));
    $selectedLocations = array_map('strval', (array) old('location_ids', []));
    $selectedShiftIds = array_map('strval', (array) old('shift_ids', $shiftChange?->shifts->pluck('id')->all() ?? []));
    $shiftType = old('shift_type', $shiftChange?->shift_type);
    $isForever = (bool) old('is_forever', $shiftChange?->is_forever ?? false);
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
    <div class="card-header"><h5 class="card-title mb-0">Shift Change Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_shift_change_type">Shift Type <span class="text-danger">*</span></label>
                <select id="manual_shift_change_type" name="shift_type" class="form-control select2 @error('shift_type') is-invalid @enderror" required>
                    <option value="">Select</option>
                    @foreach (['auto' => 'Auto', 'fixed' => 'Fixed', 'rotational' => 'Rotational'] as $key => $label)
                        <option value="{{ $key }}" @selected($shiftType === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-validation-error field="shift_type" />
            </div>
            <div class="col-md-6 mb-3 manual-shift-selection-field @class(['d-none' => !in_array($shiftType, ['auto', 'fixed'], true)])">
                <label class="form-label" for="manual_shift_change_shifts">Shifts <span class="text-danger">*</span></label>
                <select id="manual_shift_change_shifts" name="shift_ids[]" class="form-control select2 @error('shift_ids') is-invalid @enderror" multiple>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected(in_array((string) $shift->id, $selectedShiftIds, true))>{{ $shift->name }}{{ $shift->code ? ' ('.$shift->code.')' : '' }}</option>
                    @endforeach
                </select>
                <x-validation-error field="shift_ids" />
            </div>
            <div class="col-md-6 mb-3 manual-shift-rotation-field @class(['d-none' => $shiftType !== 'rotational'])">
                <label class="form-label" for="manual_shift_change_rotation">Shift Rotation <span class="text-danger">*</span></label>
                <select id="manual_shift_change_rotation" name="shift_rotation_id" class="form-control select2 @error('shift_rotation_id') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach ($shiftRotations as $rotation)
                        <option value="{{ $rotation->id }}" @selected((string) old('shift_rotation_id', $shiftChange?->shift_rotation_id) === (string) $rotation->id)>{{ $rotation->code }}</option>
                    @endforeach
                </select>
                <x-validation-error field="shift_rotation_id" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="manual_shift_change_from_date">From Date <span class="text-danger">*</span></label>
                <input id="manual_shift_change_from_date" name="from_date" type="date" required
                    class="form-control @error('from_date') is-invalid @enderror"
                    value="{{ old('from_date', $dateValue($shiftChange?->from_date)) }}">
                <x-validation-error field="from_date" />
            </div>
            <div class="col-md-4 mb-3 manual-shift-to-date-field @class(['d-none' => $isForever])">
                <label class="form-label" for="manual_shift_change_to_date">To Date <span class="text-danger">*</span></label>
                <input id="manual_shift_change_to_date" name="to_date" type="date" @required(!$isForever)
                    class="form-control @error('to_date') is-invalid @enderror"
                    value="{{ old('to_date', $dateValue($shiftChange?->to_date)) }}">
                <x-validation-error field="to_date" />
            </div>
            <div class="col-md-4 mb-3 d-flex align-items-end">
                <div class="form-check form-check-lg form-switch mb-2">
                    <input type="hidden" name="is_forever" value="0">
                    <input id="manual_shift_change_is_forever" name="is_forever" type="checkbox" role="switch" value="1" class="form-check-input" @checked($isForever)>
                    <label class="form-check-label" for="manual_shift_change_is_forever">No end date</label>
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_shift_change_reason">Reason</label>
                <textarea id="manual_shift_change_reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $shiftChange?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Filtration and Employees</h5></div>
    @if ($shiftChange !== null)
        @foreach ($selectedUsers as $userId)
            <input type="hidden" name="users[]" value="{{ $userId }}">
        @endforeach
    @endif
    <div class="card-body">
        <fieldset class="manual-shift-change-employee-fields border-0 p-0 m-0 w-100" @disabled($shiftChange !== null)>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_shift_change_companies">Companies</label>
                <select id="manual_shift_change_companies" name="company_ids[]" class="form-control select2" multiple @disabled($shiftChange !== null)>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanies, true))>{{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Select an organization scope or leave empty to choose employees directly.</small>
                <x-validation-error field="company_ids" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_shift_change_locations">Locations</label>
                <select id="manual_shift_change_locations" name="location_ids[]" class="form-control select2" multiple @disabled($shiftChange !== null)>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocations, true))>{{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}</option>
                    @endforeach
                </select>
                <x-validation-error field="location_ids" />
            </div>
            @foreach ($employeeFilterFields as $field => $filter)
                @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="manual_shift_change_employee_filter_{{ $field }}">{{ $filter['label'] }}</label>
                    <select id="manual_shift_change_employee_filter_{{ $field }}" name="employee_filters[{{ $field }}][]"
                        class="form-control select2 manual-shift-change-employee-filter" multiple @disabled($shiftChange !== null)>
                        @foreach ($filter['options'] as $option)
                            <option value="{{ $option->id }}" @selected(in_array((string) $option->id, $selectedFilterIds, true))>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_shift_change_users">Employees</label>
                <select id="manual_shift_change_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple @disabled($shiftChange !== null)>
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
                <small class="form-text text-muted">Leave empty to assign this shift change to all employees matching the selected filters, or all employees when no filters are selected.</small>
                <x-validation-error field="users" />
            </div>
        </div>
        </fieldset>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('manual_shift_change_type');
    const shiftField = document.querySelector('.manual-shift-selection-field');
    const rotationField = document.querySelector('.manual-shift-rotation-field');
    const shiftSelect = document.getElementById('manual_shift_change_shifts');
    const rotationSelect = document.getElementById('manual_shift_change_rotation');
    const foreverCheckbox = document.getElementById('manual_shift_change_is_forever');
    const toDateField = document.querySelector('.manual-shift-to-date-field');
    const toDateInput = document.getElementById('manual_shift_change_to_date');

    function updateShiftFields() {
        const isRotational = typeSelect.value === 'rotational';
        const hasShifts = ['auto', 'fixed'].includes(typeSelect.value);
        const multipleShifts = typeSelect.value === 'auto';

        if (shiftSelect.tomselect) {
            const selectedShiftIds = shiftSelect.tomselect.getValue();
            shiftSelect.tomselect.setMaxItems(multipleShifts ? null : 1);

            if (!multipleShifts && Array.isArray(selectedShiftIds) && selectedShiftIds.length > 1) {
                shiftSelect.tomselect.setValue(selectedShiftIds[0], true);
            }
        } else if (shiftSelect.multiple !== multipleShifts) {
            const selectedShiftIds = Array.from(shiftSelect.selectedOptions).map(function (option) {
                return option.value;
            });

            shiftSelect.multiple = multipleShifts;
            const retainedShiftIds = multipleShifts ? selectedShiftIds : selectedShiftIds.slice(0, 1);
            Array.from(shiftSelect.options).forEach(function (option) {
                option.selected = retainedShiftIds.includes(option.value);
            });
        }

        shiftField.classList.toggle('d-none', !hasShifts);
        rotationField.classList.toggle('d-none', !isRotational);
        shiftSelect.disabled = !hasShifts;
        rotationSelect.disabled = !isRotational;
    }

    function updateDateFields() {
        toDateField.classList.toggle('d-none', foreverCheckbox.checked);
        toDateInput.required = !foreverCheckbox.checked;
        toDateInput.disabled = foreverCheckbox.checked;
    }

    typeSelect.addEventListener('change', updateShiftFields);
    foreverCheckbox.addEventListener('change', updateDateFields);
    updateShiftFields();
    updateDateFields();

    const employeeSelect = document.getElementById('manual_shift_change_users');
    const filters = [
        { selector: '#manual_shift_change_companies', attribute: 'companyIds' },
        { selector: '#manual_shift_change_locations', attribute: 'locationIds' },
        { selector: '#manual_shift_change_employee_filter_department_id', attribute: 'departmentIds' },
        { selector: '#manual_shift_change_employee_filter_sub_department_id', attribute: 'subDepartmentIds' },
        { selector: '#manual_shift_change_employee_filter_category_id', attribute: 'categoryIds' },
        { selector: '#manual_shift_change_employee_filter_sub_category_id', attribute: 'subCategoryIds' },
        { selector: '#manual_shift_change_employee_filter_designation_id', attribute: 'designationIds' },
        { selector: '#manual_shift_change_employee_filter_grade_id', attribute: 'gradeIds' },
        { selector: '#manual_shift_change_employee_filter_unit_id', attribute: 'unitIds' },
        { selector: '#manual_shift_change_employee_filter_bus_route_id', attribute: 'busRouteIds' },
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
        if (employeeSelect.tomselect) {
            employeeSelect.tomselect.refreshOptions(false);
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
