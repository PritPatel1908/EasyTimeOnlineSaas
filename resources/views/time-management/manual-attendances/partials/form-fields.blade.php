@php
    $attendance = $attendance ?? null;
    $dateTimeValue = static function ($value) {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d\TH:i');
        }

        return $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : '';
    };
    $selectedUsers = array_map('strval', (array) old('users', $attendance?->Users->pluck('id')->all() ?? []));
    $selectedShiftType = old('shift_type', $attendance?->shift_type);
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Attendance Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_users">Employees <span class="text-danger">*</span></label>
                <select id="manual_attendance_users" name="users[]" class="form-control select2 @error('users') is-invalid @enderror" multiple required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(in_array((string) $user->id, $selectedUsers, true))>
                            {{ $user->name }}{{ $user->code ? ' ('.$user->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="users" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="manual_attendance_in_time">In Time <span class="text-danger">*</span></label>
                <input id="manual_attendance_in_time" name="in_time" type="datetime-local" step="1" required
                    class="form-control @error('in_time') is-invalid @enderror"
                    value="{{ old('in_time', $dateTimeValue($attendance?->in_time)) }}">
                <x-validation-error field="in_time" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="manual_attendance_out_time">Out Time <span class="text-danger">*</span></label>
                <input id="manual_attendance_out_time" name="out_time" type="datetime-local" step="1" required
                    class="form-control @error('out_time') is-invalid @enderror"
                    value="{{ old('out_time', $dateTimeValue($attendance?->out_time)) }}">
                <x-validation-error field="out_time" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="manual_attendance_shift_type">Shift Type <span class="text-danger">*</span></label>
                <select id="manual_attendance_shift_type" name="shift_type" class="form-control select2 @error('shift_type') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach (['auto' => 'Auto', 'fixed' => 'Fixed', 'rotational' => 'Rotational'] as $value => $label)
                        <option value="{{ $value }}" @selected($selectedShiftType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-validation-error field="shift_type" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="manual_attendance_shift_id">Shift</label>
                <select id="manual_attendance_shift_id" name="shift_id" class="form-control select2 @error('shift_id') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected((string) old('shift_id', $attendance?->shift_id) === (string) $shift->id)>
                            {{ $shift->name }}{{ $shift->code ? ' ('.$shift->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="shift_id" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="manual_attendance_shift_rotation_id">Shift Rotation</label>
                <select id="manual_attendance_shift_rotation_id" name="shift_rotation_id" class="form-control select2 @error('shift_rotation_id') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach ($shiftRotations as $rotation)
                        <option value="{{ $rotation->id }}" @selected((string) old('shift_rotation_id', $attendance?->shift_rotation_id) === (string) $rotation->id)>
                            {{ $rotation->code }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="shift_rotation_id" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_company_id">Company</label>
                <select id="manual_attendance_company_id" name="company_id" class="form-control select2 @error('company_id') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected((string) old('company_id', $attendance?->company_id) === (string) $company->id)>
                            {{ $company->name }}{{ $company->code ? ' ('.$company->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="company_id" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="manual_attendance_location_id">Location</label>
                <select id="manual_attendance_location_id" name="location_id" class="form-control select2 @error('location_id') is-invalid @enderror">
                    <option value="">Select</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) old('location_id', $attendance?->location_id) === (string) $location->id)>
                            {{ $location->name }}{{ $location->code ? ' ('.$location->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="location_id" />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="manual_attendance_reason">Reason</label>
                <textarea id="manual_attendance_reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $attendance?->reason) }}</textarea>
                <x-validation-error field="reason" />
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var type = document.getElementById('manual_attendance_shift_type');
        var shift = document.getElementById('manual_attendance_shift_id');
        var rotation = document.getElementById('manual_attendance_shift_rotation_id');

        function syncShiftFields() {
            var rotational = type.value === 'rotational';
            var shiftRequired = type.value === 'auto' || type.value === 'fixed';
            shift.required = shiftRequired;
            rotation.required = rotational;
            shift.disabled = rotational;
            rotation.disabled = !rotational;

            if (shift.tomselect) {
                rotational ? shift.tomselect.clear(true) : shift.tomselect.enable();
                rotational ? shift.tomselect.disable() : shift.tomselect.enable();
            }
            if (rotation.tomselect) {
                rotational ? rotation.tomselect.enable() : rotation.tomselect.clear(true);
                rotational ? rotation.tomselect.enable() : rotation.tomselect.disable();
            }
            if (window.jQuery) {
                window.jQuery(shift).prop('disabled', rotational).trigger('change.select2');
                window.jQuery(rotation).prop('disabled', !rotational).trigger('change.select2');
            }
        }

        type.addEventListener('change', syncShiftFields);
        syncShiftFields();
    });
</script>
@endpush
