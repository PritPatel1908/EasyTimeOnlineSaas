@php
    $application = $application ?? null;
    $selectedUserIds = array_map('strval', (array) old('users', $selectedUserIds ?? []));
    $selectedEmployeeId = $selectedUserIds[0] ?? null;
    $selectedEmployeeAllowsHalfDay = $selectedEmployeeId !== null
        && ($halfDayAllowedByUser[$selectedEmployeeId] ?? false);
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Employee Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label" for="coff_application_users">Employee <span class="text-danger">*</span></label>
                <select id="coff_application_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" required>
                    <option value="">Select employee</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            data-allow-halfday-coff="{{ ($halfDayAllowedByUser[$user->id] ?? false) ? '1' : '0' }}"
                            @selected(in_array((string) $user->id, $selectedUserIds, true))>
                            {{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="users" />
                <div class="small text-danger" data-coff-live-feedback-for="users"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">COFF Application Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="coff_against_date" class="form-label">COFF Against Date <span class="text-danger">*</span></label>
                <input id="coff_against_date" name="coff_against_date" type="text" inputmode="none" autocomplete="off"
                    class="form-control @error('coff_against_date') is-invalid @enderror"
                    value="{{ old('coff_against_date', $application?->coff_against_date?->format('Y-m-d')) }}" required disabled>
                <x-validation-error field="coff_against_date" />
                <div class="small text-danger" data-coff-live-feedback-for="coff_against_date"></div>
                <small id="coff_against_date_status" class="form-text text-muted" aria-live="polite">Select employee(s) to see available COFF dates.</small>
            </div>
            <div class="col-md-3 mb-3">
                <label for="coff_from_date" class="form-label">COFF From Date <span class="text-danger">*</span></label>
                <input id="coff_from_date" name="coff_from_date" type="date" class="form-control @error('coff_from_date') is-invalid @enderror"
                    value="{{ old('coff_from_date', $application?->coff_from_date?->format('Y-m-d')) }}" required>
                <x-validation-error field="coff_from_date" />
                <div class="small text-danger" data-coff-live-feedback-for="coff_from_date"></div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="coff_to_date" class="form-label">COFF To Date <span class="text-danger">*</span></label>
                <input id="coff_to_date" name="coff_to_date" type="date" class="form-control @error('coff_to_date') is-invalid @enderror"
                    value="{{ old('coff_to_date', $application?->coff_to_date?->format('Y-m-d')) }}" required>
                <x-validation-error field="coff_to_date" />
                <div class="small text-danger" data-coff-live-feedback-for="coff_to_date"></div>
            </div>
            <div class="col-md-4 mb-3 coff-half-day-toggle">
                <div class="form-check form-switch form-check-lg mb-2">
                    <input type="hidden" name="is_half_day" value="0">
                    <input class="form-check-input" id="is_half_day" name="is_half_day" type="checkbox" value="1"
                        @disabled(! $selectedEmployeeAllowsHalfDay)
                        @checked(old('is_half_day', $application?->is_half_day ?? false))>
                    <label class="form-check-label" for="is_half_day">Single half-day COFF</label>
                </div>
                <x-validation-error field="is_half_day" />
                <div class="small text-danger" data-coff-live-feedback-for="is_half_day"></div>
                <small id="coff_halfday_disabled_notice" class="form-text text-muted" @if ($selectedEmployeeAllowsHalfDay || $selectedEmployeeId === null) hidden @endif>
                    Half-day COFF is disabled for this employee's category.
                </small>
            </div>
            <div class="col-md-4 mb-3 coff-first-half-field">
                <div class="form-check form-switch form-check-lg mb-2">
                    <input type="hidden" name="is_first_half" value="0">
                    <input class="form-check-input" id="is_first_half" name="is_first_half" type="checkbox" value="1"
                        @disabled(! $selectedEmployeeAllowsHalfDay)
                        @checked(old('is_first_half', $application?->is_first_half ?? false))>
                    <label class="form-check-label" for="is_first_half">First half</label>
                </div>
                <small class="form-text text-muted coff-half-hint">CO applies to the first half (COA).</small>
                <x-validation-error field="is_first_half" />
            </div>
            <div class="col-md-4 mb-3 coff-second-half-field">
                <div class="form-check form-switch form-check-lg mb-2">
                    <input type="hidden" name="is_second_half" value="0">
                    <input class="form-check-input" id="is_second_half" name="is_second_half" type="checkbox" value="1"
                        @disabled(! $selectedEmployeeAllowsHalfDay)
                        @checked(old('is_second_half', $application?->is_second_half ?? false))>
                    <label class="form-check-label" for="is_second_half">Second half</label>
                </div>
                <small class="form-text text-muted coff-half-hint">CO applies to the second half (ACO).</small>
                <x-validation-error field="is_second_half" />
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect = document.getElementById('coff_application_users');
        const form = employeeSelect?.closest('form');
        const halfDayToggle = document.getElementById('is_half_day');
        const againstDateInput = document.getElementById('coff_against_date');
        const againstDateStatus = document.getElementById('coff_against_date_status');
        const fromDate = document.getElementById('coff_from_date');
        const toDate = document.getElementById('coff_to_date');
        const firstHalfToggle = document.getElementById('is_first_half');
        const secondHalfToggle = document.getElementById('is_second_half');
        const firstHalfField = document.querySelector('.coff-first-half-field');
        const secondHalfField = document.querySelector('.coff-second-half-field');
        const firstHalfLabel = document.querySelector('label[for="is_first_half"]');
        const secondHalfLabel = document.querySelector('label[for="is_second_half"]');
        const firstHalfHint = firstHalfField?.querySelector('.coff-half-hint');
        const secondHalfHint = secondHalfField?.querySelector('.coff-half-hint');
        const halfDayDisabledNotice = document.getElementById('coff_halfday_disabled_notice');

        const updateHalfDayFields = function () {
            if (!halfDayToggle || !firstHalfField || !secondHalfField) return;

            if (halfDayToggle.checked) {
                secondHalfField.before(firstHalfField);
                firstHalfLabel.textContent = 'First half';
                secondHalfLabel.textContent = 'Second half';
                firstHalfHint.textContent = 'CO applies to the first half (COA).';
                secondHalfHint.textContent = 'CO applies to the second half (ACO).';
                toDate.readOnly = true;
                if (fromDate.value) toDate.value = fromDate.value;
            } else {
                firstHalfField.before(secondHalfField);
                firstHalfLabel.textContent = 'To date: first half';
                secondHalfLabel.textContent = 'From date: second half';
                firstHalfHint.textContent = 'The final date uses COA; select this with the from-date second half for a half-day range.';
                secondHalfHint.textContent = 'The first date uses ACO; select this with the to-date first half for a half-day range.';
                toDate.readOnly = false;
            }
        };

        const updateHalfDayAvailability = function () {
            const selectedEmployee = employeeSelect?.selectedOptions[0];
            const halfDayAllowed = Boolean(employeeSelect?.value)
                && selectedEmployee?.dataset.allowHalfdayCoff === '1';

            [halfDayToggle, firstHalfToggle, secondHalfToggle].forEach(function (toggle) {
                if (toggle) toggle.disabled = !halfDayAllowed;
            });

            if (!halfDayAllowed) {
                if (halfDayToggle) halfDayToggle.checked = false;
                if (firstHalfToggle) firstHalfToggle.checked = false;
                if (secondHalfToggle) secondHalfToggle.checked = false;
            }
            if (halfDayDisabledNotice) halfDayDisabledNotice.hidden = halfDayAllowed || !employeeSelect?.value;
            updateHalfDayFields();
        };

        if (halfDayToggle) {
            halfDayToggle.addEventListener('change', function () {
                if (halfDayToggle.checked && firstHalfToggle.checked && secondHalfToggle.checked) {
                    firstHalfToggle.checked = false;
                    secondHalfToggle.checked = false;
                }
                updateHalfDayFields();
            });
            updateHalfDayFields();
        }
        if (fromDate && toDate) {
            fromDate.addEventListener('change', function () {
                if (halfDayToggle?.checked) toDate.value = fromDate.value;
            });
        }

        if (halfDayToggle && firstHalfToggle && secondHalfToggle) {
            [firstHalfToggle, secondHalfToggle].forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    if (halfDayToggle.checked && toggle.checked) {
                        (toggle === firstHalfToggle ? secondHalfToggle : firstHalfToggle).checked = false;
                    }
                });
            });
        }

        if (!employeeSelect) return;

        let availableCredits = new Map();
        let creditLookupController = null;
        const againstDatePicker = window.flatpickr && againstDateInput
            ? window.flatpickr(againstDateInput, {
                dateFormat: 'Y-m-d',
                allowInput: false,
                disable: [function () { return true; }],
                onChange: function (selectedDates, dateString) {
                    const selectedCredit = availableCredits.get(dateString) || [];
                    if (selectedCredit.length === 0) {
                        againstDateStatus.textContent = '';
                        return;
                    }
                    againstDateStatus.textContent = 'COFF credit balance: ' + selectedCredit.map(function (credit) {
                        return credit.name + ' — ' + Number(credit.days).toFixed(2) + ' day(s)';
                    }).join('; ');
                }
            })
            : null;

        const hasEmployeeSelection = function () {
            return Boolean(employeeSelect.value);
        };
        const liveFeedbackFields = ['coff_against_date', 'coff_from_date', 'coff_to_date', 'is_half_day', 'users'];
        let liveValidationErrors = {};
        let liveValidationTimer = null;
        let liveValidationController = null;
        let liveValidationPending = false;
        let lastValidatedSignature = null;
        let lastValidationAlertSignature = null;

        const validationSignature = function () {
            const data = new FormData(form);
            data.delete('_token');
            data.delete('_method');
            return new URLSearchParams(data).toString();
        };
        const clearLiveFeedback = function () {
            liveFeedbackFields.forEach(function (field) {
                const feedback = form.querySelector('[data-coff-live-feedback-for="' + field + '"]');
                const serverFeedback = form.querySelector('[data-validation-error-for="' + field + '"]');
                const input = field === 'users' ? employeeSelect : document.getElementById(field);
                if (feedback) feedback.textContent = '';
                if (serverFeedback) {
                    serverFeedback.textContent = '';
                    serverFeedback.hidden = true;
                }
                if (input) input.classList.remove('is-invalid');
            });
        };
        const renderLiveValidationErrors = function (errors) {
            liveValidationErrors = errors || {};
            liveFeedbackFields.forEach(function (field) {
                const feedback = form.querySelector('[data-coff-live-feedback-for="' + field + '"]');
                const input = field === 'users' ? employeeSelect : document.getElementById(field);
                const messages = liveValidationErrors[field] || [];
                if (feedback) feedback.textContent = '';
                if (input) input.classList.toggle('is-invalid', messages.length > 0);
            });

            const hasErrors = Object.keys(liveValidationErrors).length > 0;

            if (hasErrors) {
                const signature = validationSignature() + JSON.stringify(liveValidationErrors);
                if (signature !== lastValidationAlertSignature && typeof window.Swal?.fire === 'function') {
                    const messages = Object.values(liveValidationErrors).flat();
                    window.Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'error',
                        title: messages[0] || 'Please correct the COFF application details.',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    lastValidationAlertSignature = signature;
                }
            } else {
                lastValidationAlertSignature = null;
            }
        };
        const validateLive = async function () {
            liveValidationTimer = null;
            if (!form?.dataset.liveValidationUrl || !hasEmployeeSelection()
                || !againstDateInput.value || !fromDate.value || !toDate.value) {
                liveValidationPending = false;
                liveValidationErrors = {};
                clearLiveFeedback();
                return;
            }

            if (liveValidationController) liveValidationController.abort();
            const controller = new AbortController();
            liveValidationController = controller;
            liveValidationPending = true;
            liveValidationErrors = {};
            clearLiveFeedback();

            try {
                const response = await fetch(form.dataset.liveValidationUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value
                    },
                    body: new FormData(form),
                    signal: controller.signal
                });
                const result = await response.json();
                if (!response.ok && response.status !== 422) {
                    throw new Error(result.message || 'Live COFF validation is temporarily unavailable.');
                }
                renderLiveValidationErrors(result.errors || {});
                lastValidatedSignature = validationSignature();
            } catch (error) {
                if (error.name !== 'AbortError') {
                    liveValidationErrors = { live: [error.message || 'Live COFF validation is temporarily unavailable.'] };
                    if (typeof window.Swal?.fire === 'function') {
                        window.Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'error',
                            title: liveValidationErrors.live[0],
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    }
                }
            } finally {
                if (liveValidationController === controller) {
                    liveValidationController = null;
                    liveValidationPending = false;
                }
            }
        };
        const scheduleLiveValidation = function () {
            if (liveValidationTimer) window.clearTimeout(liveValidationTimer);
            if (liveValidationController) {
                liveValidationController.abort();
                liveValidationController = null;
            }
            liveValidationPending = false;
            liveValidationErrors = {};
            lastValidatedSignature = null;
            clearLiveFeedback();
            if (!hasEmployeeSelection() || !againstDateInput.value || !fromDate.value || !toDate.value) return;
            liveValidationPending = true;
            liveValidationTimer = window.setTimeout(validateLive, 350);
        };
        const loadEligibleCoffDates = function () {
            if (!againstDateInput || !againstDateStatus || !againstDatePicker) {
                if (againstDateStatus) againstDateStatus.textContent = 'Unable to load the COFF date picker.';
                return;
            }
            if (creditLookupController) creditLookupController.abort();

            const currentValue = againstDateInput.value;
            availableCredits = new Map();
            againstDatePicker.clear(false);
            againstDateInput.disabled = true;
            againstDatePicker.set('disable', [function () { return true; }]);
            againstDateStatus.textContent = '';

            if (!hasEmployeeSelection()) {
                againstDateStatus.textContent = 'Select employee(s) to see available COFF dates.';
                return;
            }

            const form = employeeSelect.closest('form');
            const payload = new FormData(form);
            const requestController = new AbortController();
            creditLookupController = requestController;
            againstDateStatus.textContent = 'Loading available COFF dates...';

            fetch(@json($application
                ? url('time-management/coff-applications/'.$application->id.'/eligible-dates')
                : url('time-management/coff-applications/eligible-dates')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: payload,
                signal: requestController.signal
            }).then(function (response) {
                if (!response.ok) throw new Error('COFF date lookup failed with status ' + response.status);
                return response.json();
            }).then(function (result) {
                const eligibleDates = Array.isArray(result.dates) ? result.dates : [];
                eligibleDates.forEach(function (item) {
                    availableCredits.set(item.date, item.credits || []);
                });
                againstDatePicker.set('disable', [function (date) {
                    const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000)
                        .toISOString().slice(0, 10);
                    return !availableCredits.has(localDate);
                }]);
                againstDateInput.disabled = eligibleDates.length === 0;
                againstDatePicker.set('clickOpens', eligibleDates.length > 0);

                if (currentValue && availableCredits.has(currentValue)) {
                    againstDatePicker.setDate(currentValue, true);
                } else {
                    againstDatePicker.clear(false);
                }
                if (currentValue && availableCredits.has(currentValue)) {
                    againstDateStatus.textContent = 'COFF credit balance: ' + availableCredits.get(currentValue).map(function (credit) {
                        return credit.name + ' — ' + Number(credit.days).toFixed(2) + ' day(s)';
                    }).join('; ');
                } else {
                    againstDateStatus.textContent = eligibleDates.length
                        ? eligibleDates.length + ' COFF date(s) available. Select a date to view its credit balance.'
                        : 'No eligible COFF dates with available credit were found for the selected employee(s).';
                }
                againstDateStatus.classList.remove('text-danger');
                scheduleLiveValidation();
            }).catch(function (error) {
                if (error.name === 'AbortError') return;
                console.error(error);
                againstDateStatus.textContent = 'Unable to load COFF dates. Please try again.';
                againstDateStatus.classList.add('text-danger');
            });
        };

        employeeSelect.addEventListener('change', function () {
            updateHalfDayAvailability();
            loadEligibleCoffDates();
            scheduleLiveValidation();
        });
        [againstDateInput, fromDate, toDate, halfDayToggle, firstHalfToggle, secondHalfToggle].forEach(function (field) {
            if (field) field.addEventListener('change', scheduleLiveValidation);
        });
        form.addEventListener('submit', function (event) {
            if (liveValidationTimer || liveValidationPending) {
                event.preventDefault();
                if (typeof window.Swal?.fire === 'function') {
                    window.Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'info',
                        title: 'Please wait while COFF category rules are checked.',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
                return;
            }
            if (Object.keys(liveValidationErrors).length > 0
                && lastValidatedSignature === validationSignature()) {
                event.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
        updateHalfDayAvailability();
        loadEligibleCoffDates();
    });
    </script>
@endpush
