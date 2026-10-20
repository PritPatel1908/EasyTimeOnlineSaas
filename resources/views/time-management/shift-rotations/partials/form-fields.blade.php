@php
    $shiftRotation = $shiftRotation ?? null;
    $rotationValues = old('rotation_values', $shiftRotation?->rotation_values?->map(fn ($value) => [
        'shift_id' => $value->shift_id,
        'days' => $value->days,
    ])->all() ?? [['shift_id' => '', 'days' => '']]);
    if ($rotationValues === []) {
        $rotationValues = [['shift_id' => '', 'days' => '']];
    }
@endphp

@if ($errors->any())<div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="shift_rotation_code">Rotation Code <span class="text-danger">*</span></label>
            <input id="shift_rotation_code" name="code" type="text" maxlength="255" required class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $shiftRotation?->code) }}">
            <x-validation-error field="code" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="shift_rotation_start_date">Start Date <span class="text-danger">*</span></label>
            <input id="shift_rotation_start_date" name="start_date" type="date" required class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $shiftRotation?->start_date?->format('Y-m-d')) }}">
            <x-validation-error field="start_date" />
        </div>
        <div class="col-md-6 mb-3 end-date-field">
            <label class="form-label" for="shift_rotation_end_date">End Date <span class="text-danger">*</span></label>
            <input id="shift_rotation_end_date" name="end_date" type="date" min="{{ old('start_date', $shiftRotation?->start_date?->format('Y-m-d')) }}" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $shiftRotation?->end_date?->format('Y-m-d')) }}">
            <x-validation-error field="end_date" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label d-block">Forever</label>
            <input type="hidden" name="is_forever" value="0">
            <div class="form-check form-check-lg form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="shift_rotation_is_forever" name="is_forever" value="1" @checked((bool) old('is_forever', $shiftRotation?->is_forever ?? false))>
                <label class="form-check-label" for="shift_rotation_is_forever">No end date</label>
            </div>
            <x-validation-error field="is_forever" />
        </div>
    </div></div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div><h5 class="card-title mb-0">Rotation Steps</h5><small class="text-muted">Each step assigns a shift for the specified number of days.</small></div>
        <button type="button" class="btn btn-sm btn-primary" id="add_rotation_value"><i class="ti ti-plus me-1"></i>Add Shift</button>
    </div>
    <div class="card-body">
        <x-validation-error field="rotation_values" />
        <div id="rotation_values_container">
            @foreach ($rotationValues as $index => $rotationValue)
                <div class="rotation-value-row border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Shift <span class="rotation-value-number">{{ $loop->iteration }}</span></h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-rotation-value"><i class="ti ti-trash me-1"></i>Remove</button>
                    </div>
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="rotation_value_{{ $index }}_shift">Shift <span class="text-danger">*</span></label>
                            <select id="rotation_value_{{ $index }}_shift" name="rotation_values[{{ $index }}][shift_id]" class="form-control select2 @error('rotation_values.'.$index.'.shift_id') is-invalid @enderror" required>
                                <option value="">Select Shift</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected((string) ($rotationValue['shift_id'] ?? '') === (string) $shift->id)>{{ $shift->name }} ({{ $shift->code }})</option>
                                @endforeach
                            </select>
                            <x-validation-error :field="'rotation_values.'.$index.'.shift_id'" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="rotation_value_{{ $index }}_days">Days <span class="text-danger">*</span></label>
                            <input id="rotation_value_{{ $index }}_days" name="rotation_values[{{ $index }}][days]" type="number" min="1" step="1" required value="{{ $rotationValue['days'] ?? '' }}" class="form-control @error('rotation_values.'.$index.'.days') is-invalid @enderror">
                            <x-validation-error :field="'rotation_values.'.$index.'.days'" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body"><div class="row">
        @foreach ([
            'skip_days_on_month_end' => 'Skip remaining days at month end',
            'skip_days_on_year_end' => 'Skip remaining days at year end',
            'shift_status' => 'Shift Status Flag',
        ] as $field => $label)
            <div class="col-md-4 mb-3">
                <label class="form-label d-block">{{ $label }}</label>
                <input type="hidden" name="{{ $field }}" value="0">
                <div class="form-check form-check-lg form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="{{ $field }}" name="{{ $field }}" value="1" @checked((bool) old($field, $shiftRotation?->{$field} ?? false))>
                    <label class="form-check-label" for="{{ $field }}">Enable</label>
                </div>
                <x-validation-error :field="$field" />
            </div>
        @endforeach
        <div class="col-md-4 mb-3">
            <label class="form-label" for="shift_rotation_status">Status <span class="text-danger">*</span></label>
            <select id="shift_rotation_status" name="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                <option value="1" @selected(old('status', $shiftRotation?->status ?? 1) == 1)>Active</option>
                <option value="0" @selected(old('status', $shiftRotation?->status ?? 1) == 0)>Inactive</option>
            </select>
            <x-validation-error field="status" />
        </div>
    </div></div>
</div>

<template id="rotation_value_template">
    <div class="rotation-value-row border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Shift <span class="rotation-value-number"></span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-rotation-value"><i class="ti ti-trash me-1"></i>Remove</button>
        </div>
        <div class="row align-items-end">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="rotation_value___INDEX___shift">Shift <span class="text-danger">*</span></label>
                <select id="rotation_value___INDEX___shift" name="rotation_values[__INDEX__][shift_id]" class="form-control select2" required>
                    <option value="">Select Shift</option>
                    @foreach ($shifts as $shift)<option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>@endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="rotation_value___INDEX___days">Days <span class="text-danger">*</span></label>
                <input id="rotation_value___INDEX___days" name="rotation_values[__INDEX__][days]" type="number" min="1" step="1" required class="form-control">
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('rotation_values_container');
        var template = document.getElementById('rotation_value_template');
        var addButton = document.getElementById('add_rotation_value');
        var foreverToggle = document.getElementById('shift_rotation_is_forever');
        var endDateField = document.querySelector('.end-date-field');
        var endDateInput = document.getElementById('shift_rotation_end_date');
        var startDateInput = document.getElementById('shift_rotation_start_date');
        if (startDateInput && endDateInput) {
            var updateEndDateMinimum = function () {
                endDateInput.min = startDateInput.value;
            };
            startDateInput.addEventListener('change', updateEndDateMinimum);
            updateEndDateMinimum();
        }
        if (foreverToggle && endDateField && endDateInput) {
            var updateEndDateVisibility = function () {
                endDateField.classList.toggle('d-none', foreverToggle.checked);
                endDateInput.required = !foreverToggle.checked;
                endDateInput.disabled = foreverToggle.checked;
                if (foreverToggle.checked) {
                    endDateInput.value = '';
                }
            };
            foreverToggle.addEventListener('change', updateEndDateVisibility);
            updateEndDateVisibility();
        }
        if (!container || !template || !addButton) {
            return;
        }
        var refreshRotationValueNumbers = function () {
            container.querySelectorAll('.rotation-value-row').forEach(function (row, index) {
                row.querySelector('.rotation-value-number').textContent = index + 1;
            });
        };
        addButton.addEventListener('click', function () {
            var index = Date.now().toString() + Math.floor(Math.random() * 1000).toString();
            var row = document.createElement('div');
            row.innerHTML = template.innerHTML.replaceAll('__INDEX__', index);
            var rotationValueRow = row.firstElementChild;
            container.appendChild(rotationValueRow);
            refreshRotationValueNumbers();
            var shiftSelect = rotationValueRow.querySelector('.select2');
            if (shiftSelect && typeof TomSelect !== 'undefined' && !shiftSelect.tomselect) {
                new TomSelect(shiftSelect);
            }
        });
        container.addEventListener('click', function (event) {
            var removeButton = event.target.closest('.remove-rotation-value');
            if (removeButton) {
                removeButton.closest('.rotation-value-row').remove();
                refreshRotationValueNumbers();
            }
        });
        refreshRotationValueNumbers();
    });
</script>
@endpush
