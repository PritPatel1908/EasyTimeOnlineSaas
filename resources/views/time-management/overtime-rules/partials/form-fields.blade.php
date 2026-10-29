@php
    $overtimeRule = $overtimeRule ?? null;
    $timeInputValue = static function ($value): string {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('H:i');
        }

        return $value ? substr((string) $value, 0, 5) : '';
    };
    $durationInputValue = static function ($minutes): string {
        $totalMinutes = (int) round((float) $minutes);

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    };
    $savedSlabs = $overtimeRule?->overtime_slabs?->map(static fn ($slab): array => [
        'value_from' => $timeInputValue($slab->value_from),
        'value_to' => $timeInputValue($slab->value_to),
        'head_value' => $timeInputValue($slab->head_value),
        'decimal_value' => $slab->decimal_value,
    ])->all() ?? [[]];
    $slabs = old('slabs', $savedSlabs);
    if ($slabs === []) {
        $slabs = [[]];
    }
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Rule Details</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="overtime_rule_code">Rule Code <span class="text-danger">*</span></label>
                <input id="overtime_rule_code" name="code" type="text" maxlength="255" required
                    class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $overtimeRule?->code) }}">
                <x-validation-error field="code" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="overtime_rule_status">Status <span class="text-danger">*</span></label>
                <select id="overtime_rule_status" name="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                    <option value="1" @selected(old('status', $overtimeRule?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $overtimeRule?->status ?? 1) == 0)>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="overtime_rule_description">Description</label>
                <textarea id="overtime_rule_description" name="description" rows="3"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $overtimeRule?->description) }}</textarea>
                <x-validation-error field="description" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Coming</h5></div>
    <div class="card-body">
        <div class="row">
            @foreach ([
                'ignore_early_come_minutes' => 'Ignore Early Coming',
                'ignore_late_come_minutes' => 'Ignore Late Coming',
            ] as $field => $label)
                <div class="col-lg-6 col-md-6 mb-3">
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="text"
                        placeholder="HH:mm" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]"
                        class="form-control @error($field) is-invalid @enderror"
                        value="{{ $timeInputValue(old($field, $overtimeRule?->{$field})) }}">
                    <x-validation-error :field="$field" />
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Going</h5></div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="form-check form-check-lg form-switch">
                    <input id="ignore_late_going" name="ignore_late_going" type="checkbox" role="switch"
                        class="form-check-input @error('ignore_late_going') is-invalid @enderror" value="1"
                        @checked(old('ignore_late_going', $overtimeRule?->ignore_late_going ?? false))>
                    <label class="form-check-label fw-semibold" for="ignore_late_going">Apply Ignore Late Going Deduction?</label>
                    <x-validation-error field="ignore_late_going" />
                </div>
            </div>
            <div id="ignore_overtime_field" class="col-lg-6 col-md-6 mb-3 @if(old('ignore_late_going', $overtimeRule?->ignore_late_going ?? false)) d-none @endif">
                <label class="form-label" for="ignore_overtime">Ignore Overtime (minutes)</label>
                <input id="ignore_overtime" name="ignore_overtime" type="hidden"
                    value="{{ old('ignore_overtime', $overtimeRule?->ignore_overtime ?? '0.00') }}">
                <input id="ignore_overtime_display" type="text" placeholder="HH:mm" maxlength="8"
                    pattern="\d+:[0-5]\d" @unless(old('ignore_late_going', $overtimeRule?->ignore_late_going ?? false)) required @endunless
                    class="form-control @error('ignore_overtime') is-invalid @enderror"
                    value="{{ $durationInputValue(old('ignore_overtime', $overtimeRule?->ignore_overtime ?? '0.00')) }}">
                <x-validation-error field="ignore_overtime" />
            </div>
            <div id="ignore_late_going_overtime_field" class="col-lg-6 col-md-6 mb-3 @unless(old('ignore_late_going', $overtimeRule?->ignore_late_going ?? false)) d-none @endunless">
                <label class="form-label" for="ignore_late_going_overtime">Ignore Late Going Overtime (minutes)</label>
                <input id="ignore_late_going_overtime" name="ignore_late_going_overtime" type="hidden"
                    value="{{ old('ignore_late_going_overtime', $overtimeRule?->ignore_late_going_overtime ?? '0.00') }}">
                <input id="ignore_late_going_overtime_display" type="text" placeholder="HH:mm" maxlength="8"
                    pattern="\d+:[0-5]\d" @if(old('ignore_late_going', $overtimeRule?->ignore_late_going ?? false)) required @endif
                    class="form-control @error('ignore_late_going_overtime') is-invalid @enderror"
                    value="{{ $durationInputValue(old('ignore_late_going_overtime', $overtimeRule?->ignore_late_going_overtime ?? '0.00')) }}">
                <x-validation-error field="ignore_late_going_overtime" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Overtime Slabs</h5>
        <button type="button" class="btn btn-sm btn-primary" id="add_overtime_slab"><i class="ti ti-plus me-1"></i>Add Slab</button>
    </div>
    <div class="card-body">
        <p class="text-muted">Enter time ranges and optional head/decimal values for each overtime slab.</p>
        <div id="overtime_slab_rows">
            @foreach ($slabs as $index => $slab)
                <div class="overtime-slab-row border rounded p-3 mb-3" data-index="{{ $index }}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Slab <span class="overtime-slab-number">{{ $loop->iteration }}</span></h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-overtime-slab"><i class="ti ti-trash me-1"></i>Remove</button>
                    </div>
                    <div class="row align-items-end">
                        @foreach ([
                            'value_from' => ['From', 'time'],
                            'value_to' => ['To', 'time'],
                            'head_value' => ['Head Value', 'time'],
                            'decimal_value' => ['Decimal Value', 'number'],
                        ] as $field => [$label, $type])
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label class="form-label" for="slabs_{{ $index }}_{{ $field }}">{{ $label }}</label>
                                <input id="slabs_{{ $index }}_{{ $field }}"
                                    name="slabs[{{ $index }}][{{ $field }}]" type="{{ $type === 'time' ? 'text' : $type }}"
                                    @if ($type === 'time') placeholder="HH:mm" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]" @else min="0" max="999999.99" step="0.01" @endif
                                    class="form-control @error('slabs.'.$index.'.'.$field) is-invalid @enderror"
                                    value="{{ $type === 'time' ? $timeInputValue(old('slabs.'.$index.'.'.$field, $slab[$field] ?? '')) : old('slabs.'.$index.'.'.$field, $slab[$field] ?? '') }}">
                                <x-validation-error :field="'slabs.'.$index.'.'.$field" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <template id="overtime_slab_template">
            <div class="overtime-slab-row border rounded p-3 mb-3" data-index="__INDEX__">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Slab <span class="overtime-slab-number"></span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-overtime-slab"><i class="ti ti-trash me-1"></i>Remove</button>
                </div>
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label" for="slabs___INDEX___value_from">From</label>
                        <input id="slabs___INDEX___value_from" name="slabs[__INDEX__][value_from]" type="text" placeholder="HH:mm" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]" class="form-control">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label" for="slabs___INDEX___value_to">To</label>
                        <input id="slabs___INDEX___value_to" name="slabs[__INDEX__][value_to]" type="text" placeholder="HH:mm" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]" class="form-control">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label" for="slabs___INDEX___head_value">Head Value</label>
                        <input id="slabs___INDEX___head_value" name="slabs[__INDEX__][head_value]" type="text" placeholder="HH:mm" maxlength="5" pattern="(?:[01][0-9]|2[0-3]):[0-5][0-9]" class="form-control">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label" for="slabs___INDEX___decimal_value">Decimal Value</label>
                        <input id="slabs___INDEX___decimal_value" name="slabs[__INDEX__][decimal_value]" type="number" min="0" max="999999.99" step="0.01" class="form-control">
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ignoreLateGoing = document.getElementById('ignore_late_going');
        var ignoreOvertimeField = document.getElementById('ignore_overtime_field');
        var ignoreLateGoingOvertimeField = document.getElementById('ignore_late_going_overtime_field');
        var ignoreOvertimeDisplay = document.getElementById('ignore_overtime_display');
        var ignoreLateGoingOvertimeDisplay = document.getElementById('ignore_late_going_overtime_display');

        function updateGoingFields() {
            ignoreOvertimeField.classList.toggle('d-none', ignoreLateGoing.checked);
            ignoreLateGoingOvertimeField.classList.toggle('d-none', !ignoreLateGoing.checked);
            ignoreOvertimeDisplay.required = !ignoreLateGoing.checked;
            ignoreLateGoingOvertimeDisplay.required = ignoreLateGoing.checked;
        }

        updateGoingFields();
        ignoreLateGoing.addEventListener('change', updateGoingFields);

        document.addEventListener('keydown', function (event) {
            var input = event.target;
            if (!(input instanceof HTMLInputElement) || input.placeholder !== 'HH:mm' || event.key !== 'Backspace') {
                return;
            }

            var cursor = input.selectionStart;
            if (input.selectionStart !== input.selectionEnd || cursor !== 3 || input.value.charAt(2) !== ':') {
                return;
            }

            event.preventDefault();
            input.value = input.value.slice(0, 1) + input.value.slice(3);
            input.setSelectionRange(1, 1);
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        document.addEventListener('input', function (event) {
            var input = event.target;
            if (!(input instanceof HTMLInputElement) || input.placeholder !== 'HH:mm') {
                return;
            }

            var digits = input.value.replace(/\D/g, '').slice(0, 4);
            if (digits.length > 2) {
                input.value = digits.slice(0, 2) + ':' + digits.slice(2);
            } else if (digits.length === 2) {
                input.value = digits + ':';
            } else {
                input.value = digits;
            }
        });

        [
            ['ignore_overtime_display', 'ignore_overtime'],
            ['ignore_late_going_overtime_display', 'ignore_late_going_overtime'],
        ].forEach(function ([displayId, valueId]) {
            var display = document.getElementById(displayId);
            var value = document.getElementById(valueId);

            display.addEventListener('input', function () {
                var match = display.value.match(/^(\d+):([0-5]\d)$/);
                if (match) {
                    value.value = (Number(match[1]) * 60 + Number(match[2])).toFixed(2);
                }
            });
        });

        var rows = document.getElementById('overtime_slab_rows');
        var template = document.getElementById('overtime_slab_template');
        var nextIndex = 0;
        rows.querySelectorAll('.overtime-slab-row').forEach(function (row) {
            nextIndex = Math.max(nextIndex, Number(row.dataset.index) + 1);
        });

        function updateSlabNumbers() {
            rows.querySelectorAll('.overtime-slab-row').forEach(function (row, index) {
                row.querySelector('.overtime-slab-number').textContent = String(index + 1);
            });
        }

        updateSlabNumbers();
        document.getElementById('add_overtime_slab').addEventListener('click', function () {
            rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
            updateSlabNumbers();
        });

        rows.addEventListener('click', function (event) {
            var removeButton = event.target.closest('.remove-overtime-slab');
            if (removeButton) {
                removeButton.closest('.overtime-slab-row').remove();
                updateSlabNumbers();
            }
        });
    });
</script>
@endpush
