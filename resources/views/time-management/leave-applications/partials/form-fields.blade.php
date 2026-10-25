@php
    $selectedUserIds = old('users', $selectedUserIds ?? []);
    $selectedUserIds = array_map('strval', (array) $selectedUserIds);
    $selectedCompanies = array_map('strval', (array) old('company_ids', []));
    $selectedLocations = array_map('strval', (array) old('location_ids', []));
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
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Leave Application Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="leave_type_id" class="form-label">Leave Type</label>
                <select id="leave_type_id" name="leave_type_id" class="form-control select2 @error('leave_type_id') is-invalid @enderror">
                    <option value="">Select leave type</option>
                    @foreach ($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}" @selected((string) old('leave_type_id', $application?->leave_type_id) === (string) $leaveType->id)>
                            {{ $leaveType->code }} — {{ $leaveType->description }}
                        </option>
                    @endforeach
                </select>
                @error('leave_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="leave_reason_id" class="form-label">Leave Reason</label>
                <select id="leave_reason_id" name="leave_reason_id" class="form-control select2 @error('leave_reason_id') is-invalid @enderror">
                    <option value="">Select leave reason</option>
                    @foreach ($leaveReasons as $leaveReason)
                        <option value="{{ $leaveReason->id }}" @selected((string) old('leave_reason_id', $application?->leave_reason_id) === (string) $leaveReason->id)>
                            {{ $leaveReason->leave_reason }}
                        </option>
                    @endforeach
                </select>
                @error('leave_reason_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-12 mb-3">
                <label for="reason_explanation" class="form-label">Reason Explanation</label>
                <input id="reason_explanation" name="reason_explanation" type="text" maxlength="255" class="form-control"
                    value="{{ old('reason_explanation', $application?->reason_explanation) }}">
                @error('reason_explanation')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                <input id="from_date" name="from_date" type="date" class="form-control"
                    value="{{ old('from_date', $application?->from_date?->format('Y-m-d')) }}" required>
                @error('from_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                <input id="to_date" name="to_date" type="date" class="form-control"
                    value="{{ old('to_date', $application?->to_date?->format('Y-m-d')) }}" required>
                @error('to_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="leave_count_preview" class="form-label">Leave Count (Preview)</label>
                <input id="leave_count_preview" type="text" class="form-control" value="{{ $application?->leave_count ?? '—' }}" readonly>
                <small class="form-text text-muted">Preview only; actual leave debit may exclude weekly offs and holidays.</small>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch form-check-lg mb-2">
                    <input class="form-check-input" id="is_only_half" name="is_only_half" type="checkbox" value="1"
                        @checked(old('is_only_half', $application?->is_only_half ?? false))>
                    <label class="form-check-label" for="is_only_half">Single half-day application</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch form-check-lg mb-2">
                    <input class="form-check-input" id="is_first_half" name="is_first_half" type="checkbox" value="1"
                        @checked(old('is_first_half', $application?->is_first_half ?? false))>
                    <label class="form-check-label" for="is_first_half">First half</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch form-check-lg mb-2">
                    <input class="form-check-input" id="is_second_half" name="is_second_half" type="checkbox" value="1"
                        @checked(old('is_second_half', $application?->is_second_half ?? false))>
                    <label class="form-check-label" for="is_second_half">Second half</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Filtration and Employees</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="leave_application_companies">Companies</label>
                <select id="leave_application_companies" name="company_ids[]" class="form-control select2" multiple>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanies, true))>
                            {{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Select an organization scope or leave empty to choose employees directly.</small>
                <x-validation-error field="company_ids" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="leave_application_locations">Locations</label>
                <select id="leave_application_locations" name="location_ids[]" class="form-control select2" multiple>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocations, true))>
                            {{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="location_ids" />
            </div>
            @foreach ($employeeFilterFields as $field => $filter)
                @php($selectedFilterIds = array_map('strval', (array) old('employee_filters.'.$field, [])))
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="leave_application_filter_{{ $field }}">{{ $filter['label'] }}</label>
                    <select id="leave_application_filter_{{ $field }}" name="employee_filters[{{ $field }}][]"
                        class="form-control select2 leave-application-employee-filter" multiple>
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
                <label class="form-label" for="leave_application_users">Employees</label>
                <select id="leave_application_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple>
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
                            data-grade-wise-leave-id="{{ $user->grade_wise_leave_id }}"
                            data-leave-type-ids="{{ implode(',', $leaveTypesByGroup[(string) $user->grade_wise_leave_id] ?? []) }}"
                            data-leave-policies="{{ json_encode($leavePoliciesByGroup[(string) $user->grade_wise_leave_id] ?? []) }}"
                            data-leave-balances="{{ json_encode($leaveBalancesByUser[(string) $user->id] ?? []) }}"
                            data-is-monthly-leave-group="{{ (int) ($user->grade_wise_leave?->is_monthly ?? false) }}"
                            data-unit-ids="{{ implode(',', $toIds($user->unit_id)) }}"
                            data-bus-route-ids="{{ implode(',', $toIds($user->bus_route_id)) }}"
                            @selected(in_array((string) $user->id, $selectedUserIds, true))>
                            {{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Select employees directly or leave empty to include employees matching the selected filters.</small>
                <x-validation-error field="users" />
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090">
    <div id="leave_application_grade_wise_leave_toast" class="toast text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="leave_application_grade_wise_leave_toast_body" class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="leave_application_balance_toast" class="toast text-bg-warning border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="leave_application_balance_toast_body" class="toast-body"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect = document.getElementById('leave_application_users');
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const fromDate = document.getElementById('from_date');
        const toDate = document.getElementById('to_date');
        const leaveCountPreview = document.getElementById('leave_count_preview');
        const singleHalfToggle = document.getElementById('is_only_half');
        const firstHalfToggle = document.getElementById('is_first_half');
        const secondHalfToggle = document.getElementById('is_second_half');
        const firstHalfLabel = document.querySelector('label[for="is_first_half"]');
        const secondHalfLabel = document.querySelector('label[for="is_second_half"]');
        const leaveBalanceToastElement = document.getElementById('leave_application_balance_toast');
        const leaveBalanceToastBody = document.getElementById('leave_application_balance_toast_body');
        const leaveBalanceToast = new window.bootstrap.Toast(leaveBalanceToastElement, { delay: 7000 });
        let employeesInCurrentScope = [];

        const updateLeaveBalanceAlert = function (leaveCount) {
            if (!leaveTypeSelect || !leaveTypeSelect.value || leaveCount <= 0) {
                leaveBalanceToast.hide();
                return;
            }

            const employeesWithInsufficientBalance = [];
            employeesInCurrentScope.forEach(function (employee) {
                const policies = JSON.parse(employee.dataset.leavePolicies || '{}');
                const policy = policies[leaveTypeSelect.value];
                if (!policy || !policy.keeps_balance) return;

                const balances = JSON.parse(employee.dataset.leaveBalances || '{}');
                const availableBalance = Number(balances[leaveTypeSelect.value] || 0);
                const negativeLimit = Math.abs(Number(policy.negative_balance || 0));
                if (policy.allow_negative_balance && negativeLimit === 0) return;

                const allowedBalance = availableBalance + (policy.allow_negative_balance ? negativeLimit : 0);
                if (leaveCount > allowedBalance) {
                    employeesWithInsufficientBalance.push(
                        employee.textContent.trim() + ' (available: ' + availableBalance.toFixed(2) +
                        ', requested: ' + leaveCount.toFixed(2) + ')'
                    );
                }
            });

            if (employeesWithInsufficientBalance.length === 0) {
                leaveBalanceToast.hide();
                return;
            }

            const displayedEmployees = employeesWithInsufficientBalance.slice(0, 5);
            const remainingCount = employeesWithInsufficientBalance.length - displayedEmployees.length;
            const remainingText = remainingCount > 0 ? ' and ' + remainingCount + ' more' : '';
            leaveBalanceToastBody.textContent =
                'Insufficient leave balance for: ' + displayedEmployees.join(', ') + remainingText + '.';
            leaveBalanceToast.show();
        };

        const updateLeaveCountPreview = function () {
            if (!fromDate || !toDate || !leaveCountPreview) return;
            if (!fromDate.value || !toDate.value || toDate.value < fromDate.value) {
                leaveCountPreview.value = '—';
                updateLeaveBalanceAlert(0);
                return;
            }
            if (singleHalfToggle && singleHalfToggle.checked) {
                leaveCountPreview.value = '0.50';
                updateLeaveBalanceAlert(0.5);
                return;
            }

            const start = new Date(fromDate.value + 'T00:00:00Z');
            const end = new Date(toDate.value + 'T00:00:00Z');
            let count = Math.round((end - start) / 86400000) + 1;
            if (firstHalfToggle && firstHalfToggle.checked) count -= 0.5;
            if (secondHalfToggle && secondHalfToggle.checked) count -= 0.5;
            count = Math.max(0, count);
            leaveCountPreview.value = count.toFixed(2);
            updateLeaveBalanceAlert(count);
        };

        const updateDateBounds = function (employees) {
            if (!fromDate || !toDate || !leaveTypeSelect || !leaveTypeSelect.value) {
                if (fromDate) fromDate.removeAttribute('min');
                if (fromDate) fromDate.removeAttribute('max');
                if (toDate) toDate.removeAttribute('min');
                if (toDate) toDate.removeAttribute('max');
                return;
            }

            const today = new Date();
            const todayText = [
                today.getFullYear(),
                String(today.getMonth() + 1).padStart(2, '0'),
                String(today.getDate()).padStart(2, '0'),
            ].join('-');
            const shiftDate = function (dateText, days) {
                const date = new Date(dateText + 'T00:00:00');
                date.setDate(date.getDate() + days);
                return [
                    date.getFullYear(),
                    String(date.getMonth() + 1).padStart(2, '0'),
                    String(date.getDate()).padStart(2, '0'),
                ].join('-');
            };
            let minimumDate = null;
            let maximumDate = null;

            employees.forEach(function (employee) {
                if (employee.dataset.isMonthlyLeaveGroup !== '1') return;

                const policies = JSON.parse(employee.dataset.leavePolicies || '{}');
                const policy = policies[leaveTypeSelect.value];
                if (!policy) return;

                let employeeMinimum = null;
                if (!policy.allow_backdate_leave) {
                    employeeMinimum = todayText;
                } else if (Number(policy.backdate_day_limit) > 0) {
                    employeeMinimum = shiftDate(todayText, -Number(policy.backdate_day_limit));
                }
                if (employeeMinimum && (!minimumDate || employeeMinimum > minimumDate)) {
                    minimumDate = employeeMinimum;
                }

                let employeeMaximum = null;
                if (!policy.allow_advance_leave) {
                    employeeMaximum = todayText;
                } else if (Number(policy.advance_day_limit) > 0) {
                    employeeMaximum = shiftDate(todayText, Number(policy.advance_day_limit));
                }
                if (employeeMaximum && (!maximumDate || employeeMaximum < maximumDate)) {
                    maximumDate = employeeMaximum;
                }
            });

            [fromDate, toDate].forEach(function (input) {
                if (minimumDate) input.min = minimumDate;
                else input.removeAttribute('min');
                if (maximumDate) input.max = maximumDate;
                else input.removeAttribute('max');
            });
        };

        const updateHalfDayControls = function () {
            if (!singleHalfToggle || !fromDate || !toDate) return;
            const isSingleHalf = singleHalfToggle.checked;
            toDate.disabled = isSingleHalf;
            if (firstHalfLabel && secondHalfLabel) {
                firstHalfLabel.textContent = isSingleHalf ? 'First half' : 'Second half';
                secondHalfLabel.textContent = isSingleHalf ? 'Second half' : 'First half';
            }
            if (isSingleHalf) {
                toDate.value = fromDate.value;
            }
            updateLeaveCountPreview();
        };

        [fromDate, toDate, singleHalfToggle, firstHalfToggle, secondHalfToggle].forEach(function (control) {
            if (control) control.addEventListener('change', function () {
                if (control === fromDate && singleHalfToggle && singleHalfToggle.checked) {
                    toDate.value = fromDate.value;
                }
                updateHalfDayControls();
            });
        });

        if (firstHalfToggle && secondHalfToggle) {
            firstHalfToggle.addEventListener('change', function () {
                if (singleHalfToggle && singleHalfToggle.checked && firstHalfToggle.checked) {
                    secondHalfToggle.checked = false;
                }
                updateLeaveCountPreview();
            });
            secondHalfToggle.addEventListener('change', function () {
                if (singleHalfToggle && singleHalfToggle.checked && secondHalfToggle.checked) {
                    firstHalfToggle.checked = false;
                }
                updateLeaveCountPreview();
            });
        }
        updateHalfDayControls();

        if (!employeeSelect) return;

        const filters = [
            { selector: '#leave_application_companies', attribute: 'companyIds' },
            { selector: '#leave_application_locations', attribute: 'locationIds' },
            { selector: '#leave_application_filter_department_id', attribute: 'departmentIds' },
            { selector: '#leave_application_filter_sub_department_id', attribute: 'subDepartmentIds' },
            { selector: '#leave_application_filter_category_id', attribute: 'categoryIds' },
            { selector: '#leave_application_filter_sub_category_id', attribute: 'subCategoryIds' },
            { selector: '#leave_application_filter_designation_id', attribute: 'designationIds' },
            { selector: '#leave_application_filter_grade_id', attribute: 'gradeIds' },
            { selector: '#leave_application_filter_unit_id', attribute: 'unitIds' },
            { selector: '#leave_application_filter_bus_route_id', attribute: 'busRouteIds' },
        ];

        const gradeWiseLeaveToastElement = document.getElementById('leave_application_grade_wise_leave_toast');
        const gradeWiseLeaveToastBody = document.getElementById('leave_application_grade_wise_leave_toast_body');
        const gradeWiseLeaveToast = new window.bootstrap.Toast(gradeWiseLeaveToastElement, { delay: 7000 });
        const getActiveFilters = function () {
            return filters.map(function (filter) {
                const select = document.querySelector(filter.selector);
                return {
                    attribute: filter.attribute,
                    values: select ? Array.from(select.selectedOptions).map(function (option) { return option.value; }) : [],
                };
            }).filter(function (filter) { return filter.values.length > 0; });
        };
        const matchesActiveFilters = function (option, activeFilters) {
            return activeFilters.every(function (filter) {
                const ids = (option.dataset[filter.attribute] || '').split(',').filter(Boolean);
                return filter.values.some(function (value) { return ids.includes(value); });
            });
        };
        const updateLeaveTypeOptions = function (activeFilters) {
            if (!leaveTypeSelect) return;

            const selectedEmployees = Array.from(employeeSelect.selectedOptions);
            const employeesInScope = selectedEmployees.length > 0
                ? selectedEmployees
                : activeFilters.length > 0
                    ? Array.from(employeeSelect.options).filter(function (option) {
                        return matchesActiveFilters(option, activeFilters);
                    })
                    : [];
            employeesInCurrentScope = employeesInScope;
            updateDateBounds(employeesInScope);

            let availableLeaveTypeIds = null;
            let commonLeaveTypeIds = null;
            employeesInScope.forEach(function (employee) {
                const employeeLeaveTypeIds = new Set(
                    (employee.dataset.leaveTypeIds || '').split(',').filter(Boolean)
                );
                if (availableLeaveTypeIds === null) {
                    availableLeaveTypeIds = new Set(employeeLeaveTypeIds);
                    commonLeaveTypeIds = new Set(employeeLeaveTypeIds);
                } else {
                    employeeLeaveTypeIds.forEach(function (leaveTypeId) {
                        availableLeaveTypeIds.add(leaveTypeId);
                    });
                }
                Array.from(commonLeaveTypeIds).forEach(function (leaveTypeId) {
                    if (!employeeLeaveTypeIds.has(leaveTypeId)) {
                        commonLeaveTypeIds.delete(leaveTypeId);
                    }
                });
            });

            Array.from(leaveTypeSelect.options).forEach(function (option) {
                if (!option.value) return;
                const isAvailable = availableLeaveTypeIds === null || availableLeaveTypeIds.has(option.value);
                option.hidden = !isAvailable;
                option.disabled = !isAvailable;
                if (leaveTypeSelect.tomselect && leaveTypeSelect.tomselect.options[option.value]) {
                    leaveTypeSelect.tomselect.updateOption(option.value, {
                        ...leaveTypeSelect.tomselect.options[option.value],
                        disabled: !isAvailable,
                    });
                }
            });
            if (leaveTypeSelect.tomselect) leaveTypeSelect.tomselect.refreshOptions(false);

            if (leaveTypeSelect.value && commonLeaveTypeIds !== null && !commonLeaveTypeIds.has(leaveTypeSelect.value)) {
                if (leaveTypeSelect.tomselect) {
                    leaveTypeSelect.tomselect.setValue('', true);
                } else {
                    leaveTypeSelect.value = '';
                }
                gradeWiseLeaveToastBody.textContent =
                    'The selected leave type is not available for all selected employees. Add it to each employee\'s grade-wise leave group to continue.';
                gradeWiseLeaveToast.show();
            }
            updateDateBounds(employeesInScope);
            updateLeaveCountPreview();
        };
        if (leaveTypeSelect) {
            leaveTypeSelect.addEventListener('change', function () {
                updateLeaveTypeOptions(getActiveFilters());
            });
        }
        const checkGradeWiseLeaveAssignments = function () {
            const selectedEmployees = Array.from(employeeSelect.selectedOptions);
            const activeFilters = getActiveFilters();
            const employeesInScope = selectedEmployees.length > 0
                ? selectedEmployees
                : activeFilters.length > 0
                    ? Array.from(employeeSelect.options).filter(function (option) {
                        return matchesActiveFilters(option, activeFilters);
                    })
                    : [];
            const employeesWithoutLeaveGroup = employeesInScope.filter(function (employee) {
                return !employee.dataset.gradeWiseLeaveId;
            });

            if (employeesWithoutLeaveGroup.length === 0) {
                gradeWiseLeaveToast.hide();
                return;
            }

            const employeeNames = employeesWithoutLeaveGroup.slice(0, 5).map(function (employee) {
                return employee.textContent.trim();
            });
            const remainingCount = employeesWithoutLeaveGroup.length - employeeNames.length;
            const remainingText = remainingCount > 0 ? ' and ' + remainingCount + ' more' : '';
            gradeWiseLeaveToastBody.textContent =
                'Grade-wise leave is not assigned to: ' + employeeNames.join(', ') + remainingText + '.';
            gradeWiseLeaveToast.show();
        };

        const applyEmployeeFilters = function () {
            const activeFilters = getActiveFilters();

            Array.from(employeeSelect.options).forEach(function (option) {
                const matches = matchesActiveFilters(option, activeFilters);
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
            checkGradeWiseLeaveAssignments();
            updateLeaveTypeOptions(activeFilters);
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
                    (employee.dataset[filter.attribute] || '').split(',').filter(Boolean).forEach(function (value) {
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

        employeeSelect.addEventListener('change', syncFiltersWithEmployees);
        syncFiltersWithEmployees();
    });
    </script>
@endpush
