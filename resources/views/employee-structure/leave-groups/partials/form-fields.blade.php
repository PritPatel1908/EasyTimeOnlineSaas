@php
    $isEdit = $leaveGroup !== null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3" role="alert">
        <p class="mb-1">Please correct the following fields:</p>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
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

@php
    $conditionalLimitToggles = [
        'negative_balance' => 'allow_negative_balance',
        'backdate_day_limit' => 'allow_backdate_leave',
        'advance_day_limit' => 'allow_advance_leave',
    ];
    $monthlyRuleFields = [
        ['name' => 'leave_type_id', 'label' => 'Leave Type', 'type' => 'leave_type', 'required' => true],
        ['name' => 'is_paid', 'label' => 'Paid?', 'type' => 'switch'],
        ['name' => 'keeps_balance', 'label' => 'Keeps Balance?', 'type' => 'switch'],
        ['name' => 'allow_negative_balance', 'label' => 'Allow Negative Balance?', 'type' => 'switch'],
        ['name' => 'negative_balance', 'label' => 'Negative Limit', 'type' => 'decimal', 'required' => true, 'hint' => '0 for unrestricted'],
        ['name' => 'allow_backdate_leave', 'label' => 'Allow Backdated Leave?', 'type' => 'switch'],
        ['name' => 'backdate_day_limit', 'label' => 'Backdated Day Limit', 'type' => 'number', 'hint' => '0 for unrestricted'],
        ['name' => 'allow_advance_leave', 'label' => 'Allow Advance Leave?', 'type' => 'switch'],
        ['name' => 'advance_day_limit', 'label' => 'Advance Day Limit', 'type' => 'number', 'hint' => '0 for unrestricted'],
        ['name' => 'count_weekly_off_as_present', 'label' => 'Count Week Off As Present?', 'type' => 'switch'],
        ['name' => 'count_holiday_as_present', 'label' => 'Count Holiday As Present?', 'type' => 'switch'],
        ['name' => 'allow_c_f', 'label' => 'Carried Forward?', 'type' => 'switch'],
        ['name' => 'allow_leave_encashment', 'label' => 'Encashment?', 'type' => 'switch'],
        ['name' => 'allow_leave_lapse', 'label' => 'Allow Leave Lapse?', 'type' => 'switch'],
        ['name' => 'allow_sandwich', 'label' => 'Allow Sandwich?', 'type' => 'switch'],
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
    $balanceIndependentFields = [
        'leave_type_id',
        'is_paid',
        'allow_backdate_leave',
        'backdate_day_limit',
        'allow_advance_leave',
        'advance_day_limit',
        'allow_sandwich',
        'maximum_request_in_a_month',
        'maximum_request_in_a_year',
        'leave_types_not_to_be_clubbed',
        'eligible_after_days',
        'credit_after_days',
        'min_avail',
        'max_avail',
    ];
@endphp

    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Leave Rules</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-leave-rule>
                <i class="ti ti-plus me-1"></i>Add Leave Rule
            </button>
        </div>
        <div class="card-body">
            @php
                $monthlyRulesForView = old('monthly_rules');
                if ($monthlyRulesForView === null && $isEdit) {
                    $monthlyRulesForView = $leaveGroup->grade_wise_monthly_leave_details->all();
                }
                $monthlyRulesForView ??= [];
                $hasMonthlyRules = count($monthlyRulesForView) > 0;
                $nextRuleIndex = $monthlyRulesForView === []
                    ? 0
                    : max(array_map('intval', array_keys($monthlyRulesForView))) + 1;
                $monthlyRulesForView[$nextRuleIndex] = ['_prototype' => true];
            @endphp
            <p class="text-muted mb-3" data-no-leave-rules @if ($hasMonthlyRules) hidden @endif>No monthly leave rules are linked to this group.</p>
            <div data-leave-rules-container>
            @foreach ($monthlyRulesForView as $index => $detail)
                @php
                    $isRulePrototype = data_get($detail, '_prototype', false);
                    $ruleLeaveType = $leaveTypes->firstWhere('id', data_get($detail, 'leave_type_id'));
                    $keepsBalance = (string) old('monthly_rules.'.$index.'.keeps_balance', data_get($detail, 'keeps_balance', 0)) === '1';
                @endphp
                <div class="border rounded p-3 mb-3" data-leave-rule data-monthly-rule data-rule-index="{{ $index }}" @if ($isRulePrototype) data-rule-prototype hidden @endif>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0" data-rule-title>{{ $ruleLeaveType?->description ?? $ruleLeaveType?->code ?? ($isRulePrototype ? 'New Leave Rule' : 'Monthly Rule') }}</h6>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-leave-rule>
                            <i class="ti ti-trash me-1"></i>Delete
                        </button>
                    </div>
                    @if (data_get($detail, 'id'))
                        <input type="hidden" name="monthly_rules[{{ $index }}][id]" value="{{ data_get($detail, 'id') }}">
                    @endif
                    <div class="row">
                        @foreach ($monthlyRuleFields as $field)
                            @php
                                $fieldName = $field['name'];
                                $errorKey = 'monthly_rules.'.$index.'.'.$fieldName;
                                $inputName = 'monthly_rules['.$index.']['.$fieldName.']';
                                $inputId = 'monthly_'.$index.'_'.$fieldName;
                                $value = old($errorKey, data_get($detail, $fieldName, $field['default'] ?? null));
                                $limitToggleName = $conditionalLimitToggles[$fieldName] ?? null;
                                $limitFieldName = array_search($fieldName, $conditionalLimitToggles, true);
                                $isLimitEnabled = $limitToggleName === null || (string) old('monthly_rules.'.$index.'.'.$limitToggleName, data_get($detail, $limitToggleName, 0)) === '1';
                                $isBalanceDependent = $fieldName !== 'keeps_balance' && !in_array($fieldName, $balanceIndependentFields, true);
                                if (in_array($field['type'], ['number', 'tiny_number', 'decimal'], true) && ($value === null || $value === '')) {
                                    $value = 0;
                                }
                            @endphp
                            <div class="{{ in_array($fieldName, ['leave_types_not_to_be_clubbed', 'leave_credit_type'], true) ? 'col-lg-6' : (in_array($fieldName, ['leave_type_id', 'is_paid', 'keeps_balance', 'max_accumulation', 'maximum_request_in_a_month', 'maximum_request_in_a_year', 'count_weekly_off_as_present', 'count_holiday_as_present', 'allow_c_f', 'allow_leave_encashment', 'allow_leave_lapse', 'allow_sandwich'], true) ? 'col-lg-4' : (in_array($fieldName, ['allow_negative_balance', 'negative_balance', 'allow_backdate_leave', 'backdate_day_limit', 'allow_advance_leave', 'advance_day_limit'], true) ? 'col-lg-2' : 'col-lg-3 col-md-4 col-sm-6')) }} mb-3 {{ $isBalanceDependent && !$keepsBalance ? 'd-none' : '' }}" @if ($isBalanceDependent) data-balance-dependent @endif @if ($limitToggleName) data-conditional-limit="{{ $limitToggleName }}" style="{{ !$isLimitEnabled ? 'visibility: hidden;' : '' }}" @endif>
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
                                            @if ($fieldName === 'keeps_balance') data-keeps-balance @endif
                                            @if ($limitFieldName !== false) data-limit-toggle="{{ $fieldName }}" @endif
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
                                    <select id="{{ $inputId }}" name="{{ $inputName }}" class="form-control select @error($errorKey) is-invalid @enderror" data-monthly-credit-type required>
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
                                        @if ($limitToggleName) style="transition: none;" @endif
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
                        @php
                            $monthlyCreditType = old('monthly_rules.'.$index.'.leave_credit_type', $detail->leave_credit_type ?? 'monthly');
                        @endphp
                        <div class="col-12 {{ $monthlyCreditType === 'monthly' && $keepsBalance ? '' : 'd-none' }}" data-monthly-credit-fields data-balance-dependent>
                            <div class="row">
                                @foreach ([
                                    'jan' => 'January',
                                    'feb' => 'February',
                                    'mar' => 'March',
                                    'apr' => 'April',
                                    'may' => 'May',
                                    'jun' => 'June',
                                    'jul' => 'July',
                                    'aug' => 'August',
                                    'sep' => 'September',
                                    'oct' => 'October',
                                    'nov' => 'November',
                                    'dec' => 'December',
                                ] as $month => $monthLabel)
                                    @php
                                        $monthErrorKey = 'monthly_rules.'.$index.'.'.$month;
                                        $monthInputName = 'monthly_rules['.$index.']['.$month.']';
                                        $monthInputId = 'monthly_'.$index.'_'.$month;
                                        $monthValue = old($monthErrorKey, data_get($detail, $month));
                                        if ($monthValue === null || $monthValue === '') {
                                            $monthValue = 0;
                                        }
                                    @endphp
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                        <label class="form-label" for="{{ $monthInputId }}">{{ $monthLabel }}</label>
                                        <input
                                            id="{{ $monthInputId }}"
                                            type="number"
                                            name="{{ $monthInputName }}"
                                            value="{{ $monthValue }}"
                                            class="form-control @error($monthErrorKey) is-invalid @enderror"
                                            step="0.01"
                                            min="0"
                                        >
                                        <x-validation-error :field="$monthErrorKey" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 {{ $monthlyCreditType === 'yearly' && $keepsBalance ? '' : 'd-none' }}" data-annual-quota-field data-balance-dependent>
                            @php
                                $annualQuotaErrorKey = 'monthly_rules.'.$index.'.annual_quota';
                                $annualQuotaInputName = 'monthly_rules['.$index.'][annual_quota]';
                                $annualQuotaInputId = 'monthly_'.$index.'_annual_quota';
                                $annualQuotaValue = old($annualQuotaErrorKey, data_get($detail, 'annual_quota'));
                                if ($annualQuotaValue === null || $annualQuotaValue === '') {
                                    $annualQuotaValue = 0;
                                }
                            @endphp
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label" for="{{ $annualQuotaInputId }}">Annual Quota</label>
                                    <input
                                        id="{{ $annualQuotaInputId }}"
                                        type="number"
                                        name="{{ $annualQuotaInputName }}"
                                        value="{{ $annualQuotaValue }}"
                                        class="form-control @error($annualQuotaErrorKey) is-invalid @enderror"
                                        step="0.01"
                                        min="0"
                                    >
                                    <x-validation-error :field="$annualQuotaErrorKey" />
                                </div>
                            </div>
                        </div>
                        <div class="col-12 {{ $monthlyCreditType === 'based_on_present' && $keepsBalance ? '' : 'd-none' }}" data-based-on-present-fields data-balance-dependent>
                            @php
                                $presentCountPerErrorKey = 'monthly_rules.'.$index.'.present_count_per';
                                $presentCountPerInputName = 'monthly_rules['.$index.'][present_count_per]';
                                $presentCountPerInputId = 'monthly_'.$index.'_present_count_per';
                                $presentCountPerValue = old($presentCountPerErrorKey, data_get($detail, 'present_count_per', 'monthly'));
                                $totalPresentErrorKey = 'monthly_rules.'.$index.'.if_total_present_count';
                                $totalPresentInputName = 'monthly_rules['.$index.'][if_total_present_count]';
                                $totalPresentInputId = 'monthly_'.$index.'_if_total_present_count';
                                $totalPresentValue = old($totalPresentErrorKey, data_get($detail, 'if_total_present_count', 0));
                                $creditDaysErrorKey = 'monthly_rules.'.$index.'.then_credit_balance_days';
                                $creditDaysInputName = 'monthly_rules['.$index.'][then_credit_balance_days]';
                                $creditDaysInputId = 'monthly_'.$index.'_then_credit_balance_days';
                                $creditDaysValue = old($creditDaysErrorKey, data_get($detail, 'then_credit_balance_days', 0));
                                $includeLeavesErrorKey = 'monthly_rules.'.$index.'.include_leaves';
                                $includeLeavesInputName = 'monthly_rules['.$index.'][include_leaves]';
                                $includeLeavesInputId = 'monthly_'.$index.'_include_leaves';
                                $includeLeavesValue = old($includeLeavesErrorKey, data_get($detail, 'include_leaves', []));
                            @endphp
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label" for="{{ $presentCountPerInputId }}">Present Count Per</label>
                                    <select id="{{ $presentCountPerInputId }}" name="{{ $presentCountPerInputName }}" class="form-control select @error($presentCountPerErrorKey) is-invalid @enderror" required>
                                        <option value="monthly" @selected($presentCountPerValue === 'monthly')>Monthly</option>
                                        <option value="yearly" @selected($presentCountPerValue === 'yearly')>Yearly</option>
                                    </select>
                                    <x-validation-error :field="$presentCountPerErrorKey" />
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label" for="{{ $totalPresentInputId }}">If Total Present Count</label>
                                    <input id="{{ $totalPresentInputId }}" type="number" name="{{ $totalPresentInputName }}" value="{{ $totalPresentValue }}" class="form-control @error($totalPresentErrorKey) is-invalid @enderror" min="0" step="1">
                                    <x-validation-error :field="$totalPresentErrorKey" />
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label" for="{{ $creditDaysInputId }}">Then Credit Balance Days</label>
                                    <input id="{{ $creditDaysInputId }}" type="number" name="{{ $creditDaysInputName }}" value="{{ $creditDaysValue }}" class="form-control @error($creditDaysErrorKey) is-invalid @enderror" min="0" step="0.01">
                                    <x-validation-error :field="$creditDaysErrorKey" />
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label" for="{{ $includeLeavesInputId }}">Include Leaves</label>
                                    <select id="{{ $includeLeavesInputId }}" name="{{ $includeLeavesInputName }}[]" class="form-control select @error($includeLeavesErrorKey) is-invalid @enderror" multiple>
                                        @foreach ($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}" @selected(in_array((string) $leaveType->id, array_map('strval', (array) $includeLeavesValue), true))>{{ $leaveType->code }} - {{ $leaveType->description }}</option>
                                        @endforeach
                                    </select>
                                    <x-validation-error :field="$includeLeavesErrorKey" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
    </div>

@push('scripts')
<script>
    function leaveGroupValidationName(name) {
        return name.replace(/\]/g, '').replace(/\[/g, '.');
    }

    function updateLeaveGroupFieldHighlight(field, invalid) {
        field.classList.toggle('is-invalid', invalid);
        if (invalid) {
            field.setAttribute('aria-invalid', 'true');
        } else {
            field.removeAttribute('aria-invalid');
        }

        var control = field.tomselect && field.tomselect.control;
        if (!control) return;
        control.classList.toggle('is-invalid', invalid);
        if (invalid) {
            control.setAttribute('aria-invalid', 'true');
            control.style.borderColor = 'var(--bs-form-invalid-border-color, #dc3545)';
            control.style.boxShadow = '0 0 0 .25rem rgba(220, 53, 69, .25)';
        } else {
            control.removeAttribute('aria-invalid');
            control.style.removeProperty('border-color');
            control.style.removeProperty('box-shadow');
        }
    }

    document.querySelectorAll('.leave-group-form').forEach(function (form) {
        var rulesContainer = form.querySelector('[data-leave-rules-container]');
        var rulePrototype = rulesContainer && rulesContainer.querySelector('[data-rule-prototype]');
        var noRulesMessage = form.querySelector('[data-no-leave-rules]');
        var nextRuleIndex = rulesContainer
            ? Array.from(rulesContainer.querySelectorAll('[data-monthly-rule]')).reduce(function (nextIndex, rule) {
                return Math.max(nextIndex, Number(rule.dataset.ruleIndex) + 1);
            }, 0)
            : 0;

        if (rulePrototype) {
            rulePrototype.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.disabled = true;
            });
        }

        function syncRuleIndexes(rule, index) {
            rule.dataset.ruleIndex = index;
            rule.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replace(/monthly_rules\[[^\]]+\]/, 'monthly_rules[' + index + ']');
            });
            rule.querySelectorAll('[id]').forEach(function (field) {
                field.id = field.id.replace(/monthly_\d+_/, 'monthly_' + index + '_');
            });
            rule.querySelectorAll('label[for]').forEach(function (label) {
                label.htmlFor = label.htmlFor.replace(/monthly_\d+_/, 'monthly_' + index + '_');
            });
            rule.querySelectorAll('[data-validation-error-for]').forEach(function (error) {
                error.dataset.validationErrorFor = error.dataset.validationErrorFor.replace(/monthly_rules\.\d+\./, 'monthly_rules.' + index + '.');
            });
        }

        function updateNoRulesMessage() {
            if (!noRulesMessage || !rulesContainer) return;
            noRulesMessage.hidden = rulesContainer.querySelectorAll('[data-monthly-rule]:not([data-rule-prototype])').length > 0;
        }

        if (rulesContainer) {
            rulesContainer.addEventListener('click', function (event) {
                var removeButton = event.target.closest('[data-remove-leave-rule]');
                if (!removeButton) return;
                var rule = removeButton.closest('[data-monthly-rule]');
                if (!rule) return;

                rule.remove();
                updateNoRulesMessage();
            });
        }

        var addRuleButton = form.querySelector('[data-add-leave-rule]');
        if (addRuleButton && rulesContainer) {
            addRuleButton.addEventListener('click', function () {
                var sourceRule = rulePrototype || rulesContainer.querySelector('[data-monthly-rule]:not([data-rule-prototype])');
                if (!sourceRule) return;
                sourceRule.querySelectorAll('select').forEach(function (field) {
                    if (field.tomselect) field.tomselect.destroy();
                });
                var rule = sourceRule.cloneNode(true);
                rule.hidden = false;
                rule.removeAttribute('data-rule-prototype');
                syncRuleIndexes(rule, nextRuleIndex++);
                rule.querySelectorAll('input, select, textarea').forEach(function (field) {
                    field.disabled = false;
                    field.classList.remove('is-invalid');
                    field.removeAttribute('aria-invalid');
                });
                rule.querySelectorAll('[data-validation-error-for]').forEach(function (error) {
                    error.textContent = '';
                    error.hidden = true;
                });
                var leaveType = rule.querySelector('select[name$="[leave_type_id]"]');
                if (leaveType) leaveType.value = '';
                var ruleTitle = rule.querySelector('[data-rule-title]');
                if (ruleTitle) ruleTitle.textContent = 'New Leave Rule';
                rulesContainer.appendChild(rule);
                if (typeof TomSelect !== 'undefined') {
                    rule.querySelectorAll('select.select').forEach(function (field) {
                        if (!field.tomselect) new TomSelect(field, { width: '100%', controlInput: null });
                    });
                }

                updateNoRulesMessage();
                initializeRule(rule);
                var leaveTypeSelect = rule.querySelector('select[name$="[leave_type_id]"]');
                if (leaveTypeSelect) leaveTypeSelect.focus();
            });
        }

        function syncMonthlyCreditFields(select) {
            var rule = select.closest('[data-leave-rule]');
            if (!rule) return;
            var keepsBalance = rule.querySelector('[data-keeps-balance]');
            var hasBalance = keepsBalance && keepsBalance.checked;
            var monthlyFields = rule.querySelector('[data-monthly-credit-fields]');
            var annualQuotaField = rule.querySelector('[data-annual-quota-field]');
            var basedOnPresentFields = rule.querySelector('[data-based-on-present-fields]');
            if (monthlyFields) monthlyFields.classList.toggle('d-none', !hasBalance || select.value !== 'monthly');
            if (annualQuotaField) annualQuotaField.classList.toggle('d-none', !hasBalance || select.value !== 'yearly');
            if (basedOnPresentFields) basedOnPresentFields.classList.toggle('d-none', !hasBalance || select.value !== 'based_on_present');
        }

        function syncBalanceDependentFields(checkbox) {
            var rule = checkbox.closest('[data-leave-rule]');
            if (!rule) return;
            rule.querySelectorAll('[data-balance-dependent]').forEach(function (field) {
                field.classList.toggle('d-none', !checkbox.checked);
            });
            var creditType = rule.querySelector('[data-monthly-credit-type]');
            if (creditType) syncMonthlyCreditFields(creditType);
        }

        function syncConditionalLimits(rule) {
            rule.querySelectorAll('[data-conditional-limit]').forEach(function (field) {
                var toggleName = field.dataset.conditionalLimit;
                var toggle = rule.querySelector('[data-limit-toggle="' + toggleName + '"]');
                if (toggle) field.style.visibility = toggle.checked ? '' : 'hidden';
            });
        }

        var initializedRules = new WeakSet();
        function initializeRule(rule) {
            if (initializedRules.has(rule)) return;
            initializedRules.add(rule);
            syncConditionalLimits(rule);
            rule.querySelectorAll('[data-limit-toggle]').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    syncConditionalLimits(rule);
                });
            });

            rule.querySelectorAll('[data-monthly-credit-type]').forEach(function (select) {
                syncMonthlyCreditFields(select);
                select.addEventListener('change', function () {
                    syncMonthlyCreditFields(select);
                });
            });

            rule.querySelectorAll('[data-keeps-balance]').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    syncBalanceDependentFields(checkbox);
                });
            });
        }

        form.querySelectorAll('[data-leave-rule]:not([data-rule-prototype])').forEach(initializeRule);

        window.addEventListener('load', function () {
            var invalidFields = Array.from(form.querySelectorAll('.is-invalid'));
            invalidFields.forEach(function (field) {
                if (field.matches('input, select, textarea')) {
                    updateLeaveGroupFieldHighlight(field, true);
                }
            });
            if (invalidFields.length > 0) {
                var firstInvalidField = invalidFields[0];
                var visibleField = firstInvalidField.tomselect
                    ? firstInvalidField.tomselect.wrapper
                    : firstInvalidField;
                visibleField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

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
                updateLeaveGroupFieldHighlight(field, true);
            });
            var firstInvalidField = invalidFields[0];
            if (firstInvalidField.tomselect) {
                firstInvalidField.tomselect.focus();
            } else {
                firstInvalidField.focus();
            }
            var visibleField = firstInvalidField.tomselect
                ? firstInvalidField.tomselect.wrapper
                : firstInvalidField;
            visibleField.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
            updateLeaveGroupFieldHighlight(field, false);
        }

        form.addEventListener('input', clearLeaveGroupFieldError);
        form.addEventListener('change', clearLeaveGroupFieldError);
    });
</script>
@endpush
