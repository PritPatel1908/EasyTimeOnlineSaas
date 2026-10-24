@php
    $selectedCompanyIds = old('company_id', $area?->company_id ?? []);
    $selectedCompanyIds = is_array($selectedCompanyIds) ? array_map('strval', $selectedCompanyIds) : [(string) $selectedCompanyIds];
    $selectedLocationIds = old('location_id', $area?->location_id ?? []);
    $selectedLocationIds = is_array($selectedLocationIds) ? array_map('strval', $selectedLocationIds) : [(string) $selectedLocationIds];
@endphp
@if ($errors->any())<div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>@endif
@php $isEdit = $area !== null; @endphp
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_code' : 'add_code' }}">Area Code <span class="text-danger">*</span></label>
            <input id="{{ $isEdit ? 'edit_code' : 'add_code' }}" name="code" type="text" maxlength="255" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $area?->code) }}" placeholder="Enter area code" required>
            <x-validation-error field="code" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">Area Name <span class="text-danger">*</span></label>
            <input id="{{ $isEdit ? 'edit_name' : 'add_name' }}" name="name" type="text" maxlength="255" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $area?->name) }}" placeholder="Enter area name" required>
            <x-validation-error field="name" />
        </div>
    </div></div>
</div>
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Organization</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_company_id' : 'add_company_id' }}">Companies</label>
            <select id="{{ $isEdit ? 'edit_company_id' : 'add_company_id' }}" name="company_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('company_id') is-invalid @enderror" data-placeholder="Select" multiple>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanyIds, true))>{{ $company->name }} ({{ $company->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="company_id" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_location_id' : 'add_location_id' }}">Locations</label>
            <select id="{{ $isEdit ? 'edit_location_id' : 'add_location_id' }}" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" data-placeholder="Select" multiple>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(in_array((string) $location->id, $selectedLocationIds, true))>{{ $location->name }} ({{ $location->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="location_id" />
        </div>
    </div></div>
</div>
<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_time_limit' : 'add_time_limit' }}">Time Limit (minutes) <span class="text-danger">*</span></label>
            <input id="{{ $isEdit ? 'edit_time_limit' : 'add_time_limit' }}" name="time_limit_in_min" type="number" min="0" max="4294967295" step="1" class="form-control @error('time_limit_in_min') is-invalid @enderror" value="{{ old('time_limit_in_min', $area?->time_limit_in_min ?? 0) }}" required>
            <x-validation-error field="time_limit_in_min" />
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_dms_area_id' : 'add_dms_area_id' }}">DMS Area ID</label>
            <input id="{{ $isEdit ? 'edit_dms_area_id' : 'add_dms_area_id' }}" name="dms_area_id" type="number" min="0" max="9223372036854775807" step="1" class="form-control @error('dms_area_id') is-invalid @enderror" value="{{ old('dms_area_id', $area?->dms_area_id) }}">
            <x-validation-error field="dms_area_id" />
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
            <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                <option value="1" @selected(old('status', $area?->status ?? 1) == 1)>Active</option>
                <option value="0" @selected(old('status', $area?->status ?? 1) == 0)>Inactive</option>
            </select>
            <x-validation-error field="status" />
        </div>
    </div></div>
</div>
