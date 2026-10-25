@if ($errors->any())<div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>@endif
@php
    $isEdit = $machine !== null;
    $selectedDirection = (string) old('access_direction', $machine?->access_direction);
    $selectedDirection = match ($selectedDirection) {
        '0' => 'both',
        '1' => 'in',
        '2' => 'out',
        default => $selectedDirection,
    };
@endphp
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">Machine Name <span class="text-danger">*</span></label>
            <input id="{{ $isEdit ? 'edit_name' : 'add_name' }}" name="name" type="text" maxlength="255" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $machine?->name) }}" placeholder="Enter machine name" required>
            <x-validation-error field="name" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_serial' : 'add_serial' }}">Serial Number</label>
            <input id="{{ $isEdit ? 'edit_serial' : 'add_serial' }}" name="machine_serial_number" type="text" maxlength="255" class="form-control @error('machine_serial_number') is-invalid @enderror" value="{{ old('machine_serial_number', $machine?->machine_serial_number) }}" placeholder="Enter machine serial number">
            <x-validation-error field="machine_serial_number" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_ip' : 'add_ip' }}">IP Address</label>
            <input id="{{ $isEdit ? 'edit_ip' : 'add_ip' }}" name="machine_ip" type="text" maxlength="255" class="form-control @error('machine_ip') is-invalid @enderror" value="{{ old('machine_ip', $machine?->machine_ip) }}" placeholder="Enter machine IP address">
            <x-validation-error field="machine_ip" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_direction' : 'add_direction' }}">Access Direction</label>
            <select id="{{ $isEdit ? 'edit_direction' : 'add_direction' }}" name="access_direction" class="form-control select @error('access_direction') is-invalid @enderror">
                <option value="">Select direction</option>
                @foreach (['in' => 'In', 'out' => 'Out', 'both' => 'Both'] as $value => $label)
                    <option value="{{ $value }}" @selected($selectedDirection === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-validation-error field="access_direction" />
        </div>
    </div></div>
</div>
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Organization</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_company' : 'add_company' }}">Company</label>
            <select id="{{ $isEdit ? 'edit_company' : 'add_company' }}" name="company_id" class="form-control select @error('company_id') is-invalid @enderror">
                <option value="">Select company</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) old('company_id', $machine?->company_id) === (string) $company->id)>{{ $company->name }} ({{ $company->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="company_id" />
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_location' : 'add_location' }}">Location</label>
            <select id="{{ $isEdit ? 'edit_location' : 'add_location' }}" name="location_id" class="form-control select @error('location_id') is-invalid @enderror">
                <option value="">Select location</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) old('location_id', $machine?->location_id) === (string) $location->id)>{{ $location->name }} ({{ $location->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="location_id" />
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_area' : 'add_area' }}">Area</label>
            <select id="{{ $isEdit ? 'edit_area' : 'add_area' }}" name="area_id" class="form-control select @error('area_id') is-invalid @enderror">
                <option value="">Select area</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected((string) old('area_id', $machine?->area_id) === (string) $area->id)>{{ $area->name }} ({{ $area->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="area_id" />
        </div>
    </div></div>
</div>
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">DMS Configuration</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_dms_device_id' : 'add_dms_device_id' }}">DMS Device ID</label>
            <input id="{{ $isEdit ? 'edit_dms_device_id' : 'add_dms_device_id' }}" name="dms_device_id" type="number" min="0" step="1" class="form-control @error('dms_device_id') is-invalid @enderror" value="{{ old('dms_device_id', $machine?->dms_device_id) }}">
            <x-validation-error field="dms_device_id" />
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_dms_area_id' : 'add_dms_area_id' }}">DMS Area ID</label>
            <input id="{{ $isEdit ? 'edit_dms_area_id' : 'add_dms_area_id' }}" name="dms_area_id" type="number" min="0" step="1" class="form-control @error('dms_area_id') is-invalid @enderror" value="{{ old('dms_area_id', $machine?->dms_area_id) }}">
            <x-validation-error field="dms_area_id" />
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
            <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                <option value="1" @selected(old('status', $machine?->status ?? 1) == 1)>Active</option>
                <option value="0" @selected(old('status', $machine?->status ?? 1) == 0)>Inactive</option>
            </select>
            <x-validation-error field="status" />
        </div>
    </div></div>
</div>
