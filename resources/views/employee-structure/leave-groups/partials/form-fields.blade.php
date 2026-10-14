@php
    $isEdit = $leaveGroup !== null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">Leave Group Name <span class="text-danger">*</span></label>
                <input type="text" id="{{ $isEdit ? 'edit_name' : 'add_name' }}" name="name" value="{{ old('name', $leaveGroup?->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                <x-validation-error field="name" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_financial_year_id' : 'add_financial_year_id' }}">Financial Year</label>
                <select id="{{ $isEdit ? 'edit_financial_year_id' : 'add_financial_year_id' }}" name="financial_year_id" class="form-control select @error('financial_year_id') is-invalid @enderror">
                    <option value="">Select Financial Year</option>
                    @foreach ($financialYears as $financialYear)
                        <option value="{{ $financialYear->id }}" @selected((string) old('financial_year_id', $leaveGroup?->financial_year_id) === (string) $financialYear->id)>{{ $financialYear->name }}</option>
                    @endforeach
                </select>
                <x-validation-error field="financial_year_id" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_is_monthly' : 'add_is_monthly' }}">Leave Frequency <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_is_monthly' : 'add_is_monthly' }}" name="is_monthly" class="form-control select @error('is_monthly') is-invalid @enderror" required>
                    <option value="1" @selected((string) old('is_monthly', $leaveGroup?->is_monthly ?? 1) === '1')>Monthly</option>
                    <option value="0" @selected((string) old('is_monthly', $leaveGroup?->is_monthly ?? 1) === '0')>Yearly</option>
                </select>
                <x-validation-error field="is_monthly" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                    <option value="1" @selected((string) old('status', $leaveGroup?->status ?? 1) === '1')>Active</option>
                    <option value="0" @selected((string) old('status', $leaveGroup?->status ?? 1) === '0')>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
        </div>
    </div>
</div>

@if ($isEdit)
    @php
        $monthlyRuleFields = [
            ['name' => 'leave_type_id', 'label' => 'Leave Type', 'type' => 'leave_type', 'required' => true],
            ['name' => 'is_paid', 'label' => 'Paid?', 'type' => 'switch'],
            ['name' => 'keeps_balance', 'label' => 'Keeps Balance?', 'type' => 'switch'],
            ['name' => 'allow_negative_balance', 'label' => 'Allow Negative Balance?', 'type' => 'switch'],
            ['name' => 'negative_balance', 'label' => 'Negative Limit', 'type' => 'decimal', 'required' => true],
            ['name' => 'allow_backdate_leave', 'label' => 'Allow Backdated Leave?', 'type' => 'switch'],
            ['name' => 'backdate_day_limit', 'label' => 'Backdated Day Limit', 'type' => 'number'],
            ['name' => 'allow_advance_leave', 'label' => 'Allow Advance Leave?', 'type' => 'switch'],
            ['name' => 'advance_day_limit', 'label' => 'Advance Day Limit', 'type' => 'number'],
            ['name' => 'count_weekly_off_as_present', 'label' => 'Overwrite WeekOff?', 'type' => 'switch'],
            ['name' => 'count_holiday_as_present', 'label' => 'Overwrite Holiday?', 'type' => 'switch'],
            ['name' => 'allow_c_f', 'label' => 'Carried Forward?', 'type' => 'switch'],
            ['name' => 'allow_leave_encashment', 'label' => 'Encashment?', 'type' => 'switch'],
            ['name' => 'allow_leave_lapse', 'label' => 'Allow Leave Lapse?', 'type' => 'switch'],
            ['name' => 'max_accumulation', 'label' => 'Maximum Accumulation', 'type' => 'tiny_number', 'hint' => '0 for unrestricted'],
            ['name' => 'maximum_request_in_a_month', 'label' => 'Maximum Request in a Month', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'maximum_request_in_a_year', 'label' => 'Maximum Request in a Year', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'eligible_after_days', 'label' => 'Eligible Leave After Days', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'credit_after_days', 'label' => 'Credit Leave After Days', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'min_avail', 'label' => 'Min Avail', 'type' => 'decimal', 'hint' => '0 for unrestricted'],
            ['name' => 'max_avail', 'label' => 'Max Avail', 'type' => 'decimal', 'hint' => '365 for unrestricted'],
            ['name' => 'leave_types_not_to_be_clubbed', 'label' => 'Leave not to be clubbed', 'type' => 'leave_type_multiple'],
            ['name' => 'leave_credit_type', 'label' => 'Leave Credit Type', 'type' => 'leave_credit_type', 'default' => 'monthly'],
        ];
        $yearlyRuleFields = [
            ['name' => 'leave_type_id', 'label' => 'Leave Type', 'type' => 'leave_type', 'required' => true],
            ['name' => 'is_paid', 'label' => 'Paid?', 'type' => 'switch'],
            ['name' => 'keeps_balance', 'label' => 'Keeps Balance?', 'type' => 'switch'],
            ['name' => 'eligible', 'label' => 'Eligible', 'type' => 'number'],
            ['name' => 'allow_c_f', 'label' => 'Carried Forward?', 'type' => 'switch'],
            ['name' => 'opening', 'label' => 'Opening', 'type' => 'tiny_number'],
            ['name' => 'based_on', 'label' => 'Based On', 'type' => 'tiny_number'],
            ['name' => 'allow_sandwich', 'label' => 'Allow Sandwich Leave', 'type' => 'boolean'],
            ['name' => 'sandwich_leave_id', 'label' => 'Sandwich Leave Type', 'type' => 'nullable_leave_type'],
            ['name' => 'min_utilization', 'label' => 'Minimum Utilization', 'type' => 'number'],
            ['name' => 'allow_negative_balance', 'label' => 'Allow Negative Balance?', 'type' => 'switch'],
            ['name' => 'negative_balance', 'label' => 'Negative Limit', 'type' => 'decimal', 'required' => true],
            ['name' => 'allow_leave_encashment', 'label' => 'Encashment?', 'type' => 'switch'],
            ['name' => 'max_accumulation', 'label' => 'Maximum Accumulation', 'type' => 'tiny_number', 'hint' => '0 for unrestricted'],
            ['name' => 'maximum_request_in_a_month', 'label' => 'Maximum Request in a Month', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'maximum_request_in_a_year', 'label' => 'Maximum Request in a Year', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'eligible_after_days', 'label' => 'Eligible Leave After Days', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'credit_after_days', 'label' => 'Credit Leave After Days', 'type' => 'number', 'hint' => '0 for unrestricted'],
            ['name' => 'min_avail', 'label' => 'Min Avail', 'type' => 'decimal', 'hint' => '0 for unrestricted'],
            ['name' => 'max_avail', 'label' => 'Max Avail', 'type' => 'decimal', 'hint' => '365 for unrestricted'],
            ['name' => 'leave_types_not_to_be_clubbed', 'label' => 'Leave not to be clubbed', 'type' => 'leave_type_multiple'],
            ['name' => 'leave_credit_type', 'label' => 'Leave Credit Type', 'type' => 'leave_credit_type', 'default' => 'yearly'],
            ['name' => 'allow_leave_lapse', 'label' => 'Allow Leave Lapse', 'type' => 'boolean'],
        ];
    @endphp

    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Monthly Leave Rules</h5></div>
        <div class="card-body">
            @forelse ($leaveGroup->grade_wise_monthly_leave_details as $index => $detail)
                <div class="border rounded p-3 mb-3">
                    <h6 class="mb-3">{{ $detail->leave_type?->description ?? $detail->leave_type?->code ?? 'Monthly Rule' }}</h6>
                    <input type="hidden" name="monthly_rules[{{ $index }}][id]" value="{{ $detail->id }}">
                    <div class="row">
                        @foreach ($monthlyRuleFields as $field)
                            @php
                                $fieldName = $field['name'];
                                $errorKey = 'monthly_rules.'.$index.'.'.$fieldName;
                                $inputName = 'monthly_rules['.$index.']['.$fieldName.']';
                                $inputId = 'monthly_'.$index.'_'.$fieldName;
                                $value = old($errorKey, $detail->{$fieldName} ?? ($field['default'] ?? null));
                                if (in_array($field['type'], ['number', 'tiny_number', 'decimal'], true) && ($value === null || $value === '')) {
                                    $value = 0;
                                }
                            @endphp
                            <div class="{{ in_array($fieldName, ['leave_types_not_to_be_clubbed', 'leave_credit_type'], true) ? 'col-lg-6' : (in_array($fieldName, ['leave_type_id', 'is_paid', 'keeps_balance', 'max_accumulation', 'maximum_request_in_a_month', 'maximum_request_in_a_year', 'count_weekly_off_as_present', 'count_holiday_as_present', 'allow_c_f'], true) ? 'col-lg-4' : (in_array($fieldName, ['allow_leave_encashment', 'allow_leave_lapse'], true) ? 'col-lg-6' : (in_array($fieldName, ['allow_negative_balance', 'negative_balance', 'allow_backdate_leave', 'backdate_day_limit', 'allow_advance_leave', 'advance_day_limit'], true) ? 'col-lg-2' : 'col-lg-3 col-md-4 col-sm-6'))) }} mb-3">
                                <label class="form-label" for="{{ $inputId }}">{{ $field['label'] }}</label>
                                @if ($field['type'] === 'switch')
                                    <div class="form-check form-check-lg form-switch ps-0">
                                        <input type="hidden" name="{{ $inputName }}" value="0">
                                        <input
                                            id="{{ $inputId }}"
                                            name="{{ $inputName }}"
                                            class="form-check-input ms-0 @error($errorKey) is-invalid @enderror"
                                            type="checkbox"
                                            role="switch"
                                            value="1"
                                            @checked((string) $value === '1')
                                        >
                                    </div>
                                @elseif (in_array($field['type'], ['boolean', 'nullable_boolean'], true))
                                    <select id="{{ $inputId }}" name="{{ $inputName }}" class="form-control select @error($errorKey) is-invalid @enderror" @required($field['type'] === 'boolean')>
                                        @if ($field['type'] === 'nullable_boolean')
                                            <option value="" @selected($value === null || $value === '')>Not set</option>
                                        @endif
                                        <option value="1" @selected((string) $value === '1')>Yes</option>
                                        <option value="0" @selected((string) $value === '0')>No</option>
                                    </select>
                                @elseif (in_array($field['type'], ['leave_type', 'nullable_leave_type', 'leave_type_multiple'], true))
                                    <select id="{{ $inputId }}" name="{{ $field['type'] === 'leave_type_multiple' ? $inputName.'[]' : $inputName }}" class="form-control select @error($errorKey) is-invalid @enderror" @if ($field['type'] === 'leave_type_multiple') multiple @endif @required($field['type'] === 'leave_type')>
                                        @if ($field['type'] === 'nullable_leave_type')
                                            <option value="" @selected($value === null || $value === '')>Not set</option>
                                        @endif
                                        @foreach ($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}" @selected($field['type'] === 'leave_type_multiple' ? in_array((string) $leaveType->id, array_map('strval', (array) $value), true) : (string) $value === (string) $leaveType->id)>{{ $leaveType->code }} - {{ $leaveType->description }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($field['type'] === 'leave_credit_type')
                                    <select id="{{ $inputId }}" name="{{ $inputName }}" class="form-control select @error($errorKey) is-invalid @enderror" required>
                                        @foreach (['manual' => 'Manual', 'monthly' => 'Monthly', 'yearly' => 'Yearly', 'based_on_present' => 'Based On Present', 'based_on_working_hours' => 'Based On Working Hrs'] as $creditType => $creditTypeLabel)
                                            <option value="{{ $creditType }}" @selected($value === $creditType)>{{ $creditTypeLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        id="{{ $inputId }}"
                                        type="number"
                                        name="{{ $inputName }}"
                                        value="{{ $value }}"
                                        class="form-control @error($errorKey) is-invalid @enderror"
                                        @if ($field['type'] === 'decimal') step="0.01" @else step="1" @endif
                                        @if ($field['type'] !== 'decimal') min="0" @endif
                                        @if ($field['type'] === 'tiny_number') max="127" @endif
                                        @required($field['required'] ?? false)
                                    >
                                    @if (isset($field['hint']))
                                        <small class="form-text text-muted">{{ $field['hint'] }}</small>
                                    @elseif (in_array($fieldName, ['negative_balance', 'backdate_day_limit', 'advance_day_limit'], true))
                                        <small class="form-text text-muted">0 for No-limit</small>
                                    @endif
                                @endif
                                <x-validation-error :field="$errorKey" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No monthly leave rules are linked to this group.</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Yearly Leave Rules</h5></div>
        <div class="card-body">
            @forelse ($leaveGroup->grade_wise_yearly_leave_details as $index => $detail)
                <div class="border rounded p-3 mb-3">
                    <h6 class="mb-3">{{ $detail->leave_type?->description ?? $detail->leave_type?->code ?? 'Yearly Rule' }}</h6>
                    <input type="hidden" name="yearly_rules[{{ $index }}][id]" value="{{ $detail->id }}">
                    <div class="row">
                        @foreach ($yearlyRuleFields as $field)
                            @php
                                $fieldName = $field['name'];
                                $errorKey = 'yearly_rules.'.$index.'.'.$fieldName;
                                $inputName = 'yearly_rules['.$index.']['.$fieldName.']';
                                $inputId = 'yearly_'.$index.'_'.$fieldName;
                                $value = old($errorKey, $detail->{$fieldName} ?? ($field['default'] ?? null));
                                if (in_array($field['type'], ['number', 'tiny_number', 'decimal'], true) && ($value === null || $value === '')) {
                                    $value = 0;
                                }
                            @endphp
                            <div class="{{ in_array($fieldName, ['leave_types_not_to_be_clubbed', 'leave_credit_type'], true) ? 'col-lg-6' : (in_array($fieldName, ['leave_type_id', 'is_paid', 'keeps_balance', 'max_accumulation', 'maximum_request_in_a_month', 'maximum_request_in_a_year'], true) ? 'col-lg-4' : 'col-lg-3 col-md-4 col-sm-6') }} mb-3">
                                <label class="form-label" for="{{ $inputId }}">{{ $field['label'] }}</label>
                                @if ($field['type'] === 'switch')
                                    <div class="form-check form-check-lg form-switch ps-0">
                                        <input type="hidden" name="{{ $inputName }}" value="0">
                                        <input
                                            id="{{ $inputId }}"
                                            name="{{ $inputName }}"
                                            class="form-check-input ms-0 @error($errorKey) is-invalid @enderror"
                                            type="checkbox"
                                            role="switch"
                                            value="1"
                                            @checked((string) $value === '1')
                                        >
                                    </div>
                                @elseif (in_array($field['type'], ['boolean', 'nullable_boolean'], true))
                                    <select id="{{ $inputId }}" name="{{ $inputName }}" class="form-control select @error($errorKey) is-invalid @enderror" @required($field['type'] === 'boolean')>
                                        @if ($field['type'] === 'nullable_boolean')
                                            <option value="" @selected($value === null || $value === '')>Not set</option>
                                        @endif
                                        <option value="1" @selected((string) $value === '1')>Yes</option>
                                        <option value="0" @selected((string) $value === '0')>No</option>
                                    </select>
                                @elseif (in_array($field['type'], ['leave_type', 'nullable_leave_type'], true))
                                    <select id="{{ $inputId }}" name="{{ $inputName }}" class="form-control select @error($errorKey) is-invalid @enderror" @required($field['type'] === 'leave_type')>
                                        @if ($field['type'] === 'nullable_leave_type')
                                            <option value="" @selected($value === null || $value === '')>Not set</option>
                                        @endif
                                        @foreach ($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}" @selected((string) $value === (string) $leaveType->id)>{{ $leaveType->code }} - {{ $leaveType->description }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        id="{{ $inputId }}"
                                        type="number"
                                        name="{{ $inputName }}"
                                        value="{{ $value }}"
                                        class="form-control @error($errorKey) is-invalid @enderror"
                                        @if ($field['type'] === 'decimal') step="0.01" @else step="1" @endif
                                        @if ($field['type'] !== 'decimal') min="0" @endif
                                        @if ($field['type'] === 'tiny_number') max="127" @endif
                                        @required($field['required'] ?? false)
                                    >
                                    @if (isset($field['hint']))
                                        <small class="form-text text-muted">{{ $field['hint'] }}</small>
                                    @elseif ($fieldName === 'negative_balance')
                                        <small class="form-text text-muted">0 for No-limit</small>
                                    @endif
                                @endif
                                <x-validation-error :field="$errorKey" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No yearly leave rules are linked to this group.</p>
            @endforelse
        </div>
    </div>
@endif

@push('scripts')
<script>
    function leaveGroupValidationName(name) {
        return name.replace(/\]/g, '').replace(/\[/g, '.');
    }

    document.querySelectorAll('.leave-group-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var invalidFields = Array.from(form.elements).filter(function (field) {
                return field.willValidate && !field.validity.valid;
            });
            if (invalidFields.length === 0) return;
            event.preventDefault();
            invalidFields.forEach(function (field) {
                var error = Array.from(form.querySelectorAll('[data-validation-error-for]')).find(function (node) {
                    return node.dataset.validationErrorFor === leaveGroupValidationName(field.name);
                });
                if (error) {
                    error.textContent = field.validity.valueMissing ? 'This field is required.' : field.validationMessage;
                    error.hidden = false;
                }
                field.classList.add('is-invalid');
                field.setAttribute('aria-invalid', 'true');
            });
            invalidFields[0].focus();
        });

        function clearLeaveGroupFieldError(event) {
            var field = event.target;
            if (!field.validity || !field.validity.valid) return;
            var error = Array.from(form.querySelectorAll('[data-validation-error-for]')).find(function (node) {
                return node.dataset.validationErrorFor === leaveGroupValidationName(field.name);
            });
            if (error) {
                error.textContent = '';
                error.hidden = true;
            }
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        }

        form.addEventListener('input', clearLeaveGroupFieldError);
        form.addEventListener('change', clearLeaveGroupFieldError);
    });
</script>
@endpush
