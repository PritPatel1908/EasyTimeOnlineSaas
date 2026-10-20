@php
    $absentRule = $absentRule ?? null;
    $numberGroups = [
        'Work Hours' => [
            'work_hr_less_than_minutes' => 'Work hours less than (minutes)',
        ],
        'Late Coming' => [
            'late_coming_minutes' => 'Late coming threshold (minutes)',
            'no_of_late' => 'Number of late occurrences',
            'consecutive_late_coming' => 'Consecutive late occurrences',
        ],
        'Early Going' => [
            'early_going_minutes' => 'Early going threshold (minutes)',
            'no_of_early' => 'Number of early departures',
            'consecutive_early_going' => 'Consecutive early departures',
        ],
    ];
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Rule Details</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="absent_rule_code">Rule Code <span class="text-danger">*</span></label>
                <input id="absent_rule_code" name="code" type="text" maxlength="255" required
                    class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $absentRule?->code) }}">
                <x-validation-error field="code" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="absent_rule_status">Status <span class="text-danger">*</span></label>
                <select id="absent_rule_status" name="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                    <option value="1" @selected(old('status', $absentRule?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $absentRule?->status ?? 1) == 0)>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="absent_rule_description">Description</label>
                <textarea id="absent_rule_description" name="description" rows="3"
                    class="form-control @error('description') is-invalid @enderror">{{ old('description', $absentRule?->description) }}</textarea>
                <x-validation-error field="description" />
            </div>
        </div>
    </div>
</div>

@foreach ($numberGroups as $groupTitle => $fields)
    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">{{ $groupTitle }}</h5></div>
        <div class="card-body">
            <div class="row">
                @foreach ($fields as $field => $label)
                    @php($isInteger = in_array($field, ['work_hr_less_than_minutes', 'late_coming_minutes', 'early_going_minutes'], true))
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="{{ $field }}">{{ $label }}@if ($field === 'work_hr_less_than_minutes') <span class="text-danger">*</span>@endif</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="number" min="0"
                            @if ($isInteger) max="2147483647" step="1" @else step="any" @endif
                            @if ($field === 'work_hr_less_than_minutes') required @endif
                            class="form-control @error($field) is-invalid @enderror"
                            value="{{ old($field, $absentRule?->{$field} ?? ($field === 'work_hr_less_than_minutes' ? 0 : null)) }}">
                        <x-validation-error :field="$field" />
                    </div>
                @endforeach
                @if ($groupTitle === 'Work Hours')
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block" for="allow_single_punch">Allow single punch</label>
                        <div class="form-check form-check-lg form-switch ps-0">
                            <input type="hidden" name="allow_single_punch" value="0">
                            <input id="allow_single_punch" name="allow_single_punch" type="checkbox" value="1"
                                role="switch"
                                class="form-check-input ms-0 @error('allow_single_punch') is-invalid @enderror"
                                @checked(old('allow_single_punch', $absentRule?->allow_single_punch ?? false))>
                        </div>
                        <x-validation-error field="allow_single_punch" />
                    </div>
                @elseif ($groupTitle === 'Late Coming')
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block" for="ignore_month_end_late">Ignore month-end late coming</label>
                        <div class="form-check form-check-lg form-switch ps-0">
                            <input type="hidden" name="ignore_month_end_late" value="0">
                            <input id="ignore_month_end_late" name="ignore_month_end_late" type="checkbox" value="1"
                                role="switch"
                                class="form-check-input ms-0 @error('ignore_month_end_late') is-invalid @enderror"
                                @checked(old('ignore_month_end_late', $absentRule?->ignore_month_end_late ?? false))>
                        </div>
                        <x-validation-error field="ignore_month_end_late" />
                    </div>
                @elseif ($groupTitle === 'Early Going')
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block" for="ignore_month_end_early">Ignore month-end early</label>
                        <div class="form-check form-check-lg form-switch ps-0">
                            <input type="hidden" name="ignore_month_end_early" value="0">
                            <input id="ignore_month_end_early" name="ignore_month_end_early" type="checkbox" value="1"
                                role="switch"
                                class="form-check-input ms-0 @error('ignore_month_end_early') is-invalid @enderror"
                                @checked(old('ignore_month_end_early', $absentRule?->ignore_month_end_early ?? false))>
                        </div>
                        <x-validation-error field="ignore_month_end_early" />
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach
