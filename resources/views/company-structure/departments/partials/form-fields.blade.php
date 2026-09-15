@php
    $isEdit = $department !== null;
    $selectedCompanies = old('company_id', $department?->company_id ?? []);
    $selectedLocations = old('location_id', $department?->location_id ?? []);
    $selectedCompanies = is_array($selectedCompanies) ? $selectedCompanies : [$selectedCompanies];
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

{{-- Basic Information Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Basic Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="department_name">Department Name <span class="text-danger">*</span></label><input id="department_name" name="name" class="form-control" value="{{ old('name', $department?->name) }}" required></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="department_code">Department Code <span class="text-danger">*</span></label><input id="department_code" name="code" class="form-control" value="{{ old('code', $department?->code) }}" required></div>
        </div>
    </div>
</div>

{{-- Contact Information Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Contact Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3"><label class="form-label" for="department_email">Email</label><input id="department_email" type="email" name="email" class="form-control" value="{{ old('email', $department?->email) }}"></div>
        </div>
    </div>
</div>

{{-- Organization Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Organization</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="department_companies">Companies</label><select id="department_companies" name="company_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('company_id') is-invalid @enderror" placeholder="Select" multiple>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(in_array($company->id, $selectedCompanies))>{{ $company->name }}</option>@endforeach</select>@error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label" for="department_locations">Locations</label><select id="department_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" placeholder="Select" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select>@error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div>
</div>

{{-- Configuration Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Configuration</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3"><label class="form-label" for="department_status">Status <span class="text-danger">*</span></label><select id="department_status" name="status" class="form-control" required><option value="1" @selected(old('status', $department?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $department?->status ?? 1) == 0)>Inactive</option></select></div>
        </div>
    </div>
</div>
