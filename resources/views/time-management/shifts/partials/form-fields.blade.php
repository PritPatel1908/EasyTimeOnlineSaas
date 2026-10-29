@php
    $shift = $shift ?? null;
    $idList = static function ($value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_map('strval', (array) $value);
    };
    $fieldTime = static function ($value) {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('H:i:s');
        }

        return $value ? substr((string) $value, 0, 8) : '';
    };
    $selectedLocationIds = $idList(old('location_id', $shift?->location_id ?? []));
    $selectedCompanyIds = $idList(old('company_id', $shift?->company_id ?? []));
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="shift_name">Shift Name <span class="text-danger">*</span></label>
                <input id="shift_name" name="name" type="text" maxlength="255" required
                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $shift?->name) }}">
                <x-validation-error field="name" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="shift_code">Shift Code <span class="text-danger">*</span></label>
                <input id="shift_code" name="code" type="text" maxlength="50" required
                    class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $shift?->code) }}">
                <x-validation-error field="code" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Organization</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="shift_company_id">Companies</label>
                <select id="shift_company_id" name="company_id[]" class="form-control select2" multiple>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanyIds, true))>
                            {{ $company->name }}{{ (int) $company->status === 1 ? '' : ' (Inactive)' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="company_id" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="shift_location_id">Locations</label>
                <select id="shift_location_id" name="location_id[]" class="form-control select2" multiple>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocationIds, true))>
                            {{ $location->name }}{{ (int) $location->status === 1 ? '' : ' (Inactive)' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="location_id" />
            </div>
        </div>
    </div>
</div>

<div id="shift-timing-section" class="card mb-4" @class(['d-none' => (bool) old('is_flaxible', $shift?->is_flaxible ?? false)])>
    <div class="card-header"><h5 class="card-title mb-0">Shift Timing</h5></div>
    <div class="card-body">
        <div class="row">
            @foreach ([
                'in_time' => 'Shift Start Time',
                'out_time' => 'Shift End Time',
                'first_half_end_time' => 'First Half End Time',
                'second_half_start_time' => 'Second Half Start Time',
                'brack_start_time' => 'Break Start Time',
                'brack_end_time' => 'Break End Time',
                'auto_from' => 'Automatic Attendance From',
                'auto_to' => 'Automatic Attendance To',
            ] as $field => $label)
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="shift_{{ $field }}">{{ $label }}</label>
                    <input id="shift_{{ $field }}" name="{{ $field }}" type="time" step="1"
                        class="form-control @error($field) is-invalid @enderror"
                        value="{{ old($field, $fieldTime($shift?->{$field})) }}">
                    <x-validation-error :field="$field" />
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-sm-6 col-lg mb-3">
                <label class="form-label d-block">Flexible Shift</label>
                <input type="hidden" name="is_flaxible" value="0">
                <div class="form-check form-check-lg form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_flaxible" name="is_flaxible" value="1"
                        @checked((bool) old('is_flaxible', $shift?->is_flaxible ?? false))>
                    <label class="form-check-label" for="is_flaxible">Enable flexible timing</label>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg mb-3">
                <label class="form-label d-block">Night Shift</label>
                <input type="hidden" name="is_night_shift" value="0">
                <div class="form-check form-check-lg form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_night_shift" name="is_night_shift" value="1"
                        @checked((bool) old('is_night_shift', $shift?->is_night_shift ?? false))>
                    <label class="form-check-label" for="is_night_shift">This shift crosses midnight</label>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg mb-3">
                <label class="form-label d-block">Set Cutoff Duration</label>
                <input type="hidden" name="set_cutoff" value="0">
                <div class="form-check form-check-lg form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="set_cutoff" name="set_cutoff" value="1"
                        @checked((bool) old('set_cutoff', $shift?->set_cutoff ?? false))>
                    <label class="form-check-label" for="set_cutoff">Enable cutoff</label>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg mb-3 cutoff-time-field" @class(['d-none' => !(bool) old('set_cutoff', $shift?->set_cutoff ?? false)])>
                <label class="form-label" for="cutoff_time">Cutoff Duration</label>
                <input id="cutoff_time" name="cutoff_time" type="text" inputmode="numeric"
                    placeholder="HH:MM or HH:MM:SS" maxlength="9" autocomplete="off"
                    class="form-control @error('cutoff_time') is-invalid @enderror"
                    value="{{ old('cutoff_time', $fieldTime($shift?->cutoff_time)) }}">
                <small class="form-text text-muted">Duration added after the scheduled shift end; 24:00 means 24 hours after shift end.</small>
                <x-validation-error field="cutoff_time" />
            </div>

            <div class="col-12 col-sm-6 col-lg mb-3">
                <label class="form-label" for="shift_status">Status <span class="text-danger">*</span></label>
                <select id="shift_status" name="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                    <option value="1" @selected(old('status', $shift?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $shift?->status ?? 1) == 0)>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const cutoffToggle = document.getElementById('set_cutoff');
                const cutoffTimeField = document.querySelector('.cutoff-time-field');
                const flexibleToggle = document.getElementById('is_flaxible');
                const shiftTimingSection = document.getElementById('shift-timing-section');

                if (cutoffToggle && cutoffTimeField) {
                    const updateCutoffTimeVisibility = function () {
                        cutoffTimeField.classList.toggle('d-none', !cutoffToggle.checked);
                    };

                    cutoffToggle.addEventListener('change', updateCutoffTimeVisibility);
                    updateCutoffTimeVisibility();
                }

                if (flexibleToggle && shiftTimingSection) {
                    const updateShiftTimingVisibility = function () {
                        shiftTimingSection.classList.toggle('d-none', flexibleToggle.checked);
                    };

                    flexibleToggle.addEventListener('change', updateShiftTimingVisibility);
                    updateShiftTimingVisibility();
                }
            });
        </script>
    @endpush
</div>
