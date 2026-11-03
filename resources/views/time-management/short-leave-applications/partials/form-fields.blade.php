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
                <div class="small text-danger" data-short-leave-feedback-for="short_leave_type"></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                <input id="date" name="date" type="date" required
                    class="form-control @error('date') is-invalid @enderror"
                    value="{{ old('date', $application?->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                <x-validation-error field="date" />
                <div class="small text-danger" data-short-leave-feedback-for="date"></div>
            </div>
            <div class="col-md-{{ $selectedType === '3' ? '3' : '6' }} mb-3 short-leave-minutes">
                <label class="form-label" for="minutes">Minutes <span class="text-danger">*</span></label>
                <input id="minutes" name="minutes" type="number" min="1" max="1440"
                    class="form-control @error('minutes') is-invalid @enderror"
                    value="{{ old('minutes', $application?->minutes) }}">
                <x-validation-error field="minutes" />
                <div class="small text-danger" data-short-leave-feedback-for="minutes"></div>
            </div>
            <div class="col-md-3 mb-3 short-leave-times">
                <label class="form-label" for="from_time">From Time <span class="text-danger">*</span></label>
                <input id="from_time" name="from_time" type="time" step="60"
                    class="form-control @error('from_time') is-invalid @enderror"
                    value="{{ old('from_time', $application?->from_time?->format('H:i')) }}">
                <x-validation-error field="from_time" />
                <div class="small text-danger" data-short-leave-feedback-for="from_time"></div>
            </div>
            <div class="col-md-3 mb-3 short-leave-times">
                <label class="form-label" for="to_time">To Time <span class="text-danger">*</span></label>
                <input id="to_time" name="to_time" type="time" step="60"
                    class="form-control @error('to_time') is-invalid @enderror"
                    value="{{ old('to_time', $application?->to_time?->format('H:i')) }}">
                <x-validation-error field="to_time" />
                <div class="small text-danger" data-short-leave-feedback-for="to_time"></div>
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
                <div class="small text-danger" data-short-leave-feedback-for="users"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var type = document.getElementById('short_leave_type');
        if (!type) return;
        var form = type.closest('form');
        var minutes = document.querySelector('.short-leave-minutes');
        var times = document.querySelectorAll('.short-leave-times');
        var minutesInput = document.getElementById('minutes');
        var fromInput = document.getElementById('from_time');
        var toInput = document.getElementById('to_time');
        var dateInput = document.getElementById('date');
        var feedbackFields = ['short_leave_type', 'date', 'minutes', 'from_time', 'to_time', 'users'];
        var liveErrors = {};
        var validationTimer = null;
        var activeRequest = null;
        var isValidating = false;
        var dateCheckPending = false;
        var lastCheckedSignature = null;
        var liveStatus = document.createElement('div');
        liveStatus.className = 'small mb-2';
        liveStatus.setAttribute('role', 'status');
        liveStatus.setAttribute('aria-live', 'polite');
        form.querySelector('.card-body').prepend(liveStatus);

        function updateFields() {
            var isBreak = type.value === '3';
            minutes.hidden = isBreak;
            minutes.classList.toggle('col-md-3', isBreak);
            minutes.classList.toggle('col-md-6', !isBreak);
            times.forEach(function (field) { field.hidden = !isBreak; });
            minutesInput.required = !isBreak;
            fromInput.required = isBreak;
            toInput.required = isBreak;
            var dateIsInvalid = (liveErrors.date || []).length > 0;
            fromInput.disabled = isBreak && (dateIsInvalid || dateCheckPending);
            toInput.disabled = isBreak && (dateIsInvalid || dateCheckPending);
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

        function formSignature() {
            var data = new FormData(form);
            data.delete('_method');
            return new URLSearchParams(data).toString();
        }

        function hasEmployeeScope() {
            if (employeeSelect.selectedOptions.length > 0) return true;
            return [
                '#short_leave_application_companies',
                '#short_leave_application_locations',
                ...filters.map(function (filter) { return filter.selector; })
            ].some(function (selector) {
                var select = document.querySelector(selector);
                return select && select.selectedOptions.length > 0;
            });
        }

        function renderLiveErrors(errors) {
            liveErrors = errors || {};
            feedbackFields.forEach(function (field) {
                var feedback = form.querySelector('[data-short-leave-feedback-for="' + field + '"]');
                var input = field === 'users' ? employeeSelect : document.getElementById(field);
                var messages = liveErrors[field] || (field === 'users' ? liveErrors.user_id : null) || [];
                if (feedback) feedback.textContent = messages.join(' ');
                if (input) input.classList.toggle('is-invalid', messages.length > 0);
            });
            var hasErrors = Object.keys(liveErrors).length > 0;
            liveStatus.className = hasErrors ? 'small mb-2 text-danger' : 'small mb-2 text-success';
            liveStatus.textContent = hasErrors ? 'Please correct the highlighted fields.' : 'Short leave details are valid.';
            updateFields();
        }

        function clearLiveValidation() {
            renderLiveErrors({});
            dateCheckPending = false;
            updateFields();
            liveStatus.textContent = '';
            liveStatus.className = 'small mb-2';
            lastCheckedSignature = null;
        }

        async function validateLive(dateOnly) {
            validationTimer = null;
            if (!form.dataset.liveValidationUrl || !dateInput.value || !hasEmployeeScope()) {
                clearLiveValidation();
                return;
            }

            if (activeRequest) activeRequest.abort();
            var controller = new AbortController();
            activeRequest = controller;
            isValidating = true;
            var signature = formSignature();
            liveErrors = {};
            feedbackFields.forEach(function (field) {
                var feedback = form.querySelector('[data-short-leave-feedback-for="' + field + '"]');
                var input = field === 'users' ? employeeSelect : document.getElementById(field);
                if (feedback) feedback.textContent = '';
                if (input) input.classList.remove('is-invalid');
            });
            liveStatus.className = 'small mb-2 text-muted';
            liveStatus.textContent = 'Checking short leave rules…';

            try {
                var body = new FormData(form);
                body.delete('_method');
                body.set('live_date_check', dateOnly ? '1' : '0');
                var response = await fetch(form.dataset.liveValidationUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value
                    },
                    body: body,
                    signal: controller.signal
                });
                var data;
                try {
                    data = await response.json();
                } catch {
                    throw new Error('Live validation is temporarily unavailable.');
                }
                if (!response.ok && response.status !== 422) {
                    throw new Error(data.message || 'Live validation is temporarily unavailable.');
                }
                renderLiveErrors(data.errors || {});
                lastCheckedSignature = signature;
            } catch (error) {
                if (error.name !== 'AbortError') {
                    liveErrors = { live: [error.message || 'Live validation is temporarily unavailable.'] };
                    liveStatus.className = 'small mb-2 text-danger';
                    liveStatus.textContent = liveErrors.live[0];
                }
            } finally {
                if (activeRequest === controller) {
                    activeRequest = null;
                    isValidating = false;
                    dateCheckPending = false;
                    updateFields();
                }
            }
        }

        function scheduleLiveValidation(dateOnly) {
            if (validationTimer) window.clearTimeout(validationTimer);
            if (activeRequest) {
                activeRequest.abort();
                activeRequest = null;
                isValidating = false;
            }
            liveErrors = {};
            lastCheckedSignature = null;
            dateCheckPending = Boolean(dateOnly);
            feedbackFields.forEach(function (field) {
                var feedback = form.querySelector('[data-short-leave-feedback-for="' + field + '"]');
                var input = field === 'users' ? employeeSelect : document.getElementById(field);
                if (feedback) feedback.textContent = '';
                if (input) input.classList.remove('is-invalid');
            });
            liveStatus.className = 'small mb-2 text-muted';
            liveStatus.textContent = dateOnly ? 'Checking date and monthly limits…' : '';
            updateFields();
            validationTimer = window.setTimeout(function () { validateLive(Boolean(dateOnly)); }, 350);
        }

        form.addEventListener('change', function (event) {
            var target = event.target;
            var isTimeOrMinutes = target === minutesInput || target === fromInput || target === toInput;
            if (isTimeOrMinutes) {
                scheduleLiveValidation(false);
                return;
            }
            if (target === dateInput || target === type || target === employeeSelect
                || target.matches('.short-leave-employee-filter')
                || target.matches('#short_leave_application_companies, #short_leave_application_locations')) {
                scheduleLiveValidation(true);
            }
        });
        [minutesInput, fromInput, toInput].forEach(function (input) {
            input.addEventListener('input', scheduleLiveValidation);
        });
        form.addEventListener('submit', function (event) {
            if (validationTimer || isValidating) {
                event.preventDefault();
                liveStatus.className = 'small mb-2 text-muted';
                liveStatus.textContent = 'Please wait while short leave rules are checked.';
                return;
            }
            if (Object.keys(liveErrors).length > 0 && lastCheckedSignature === formSignature()) {
                event.preventDefault();
                var firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });

        scheduleLiveValidation(true);
    })();
</script>
@endpush
