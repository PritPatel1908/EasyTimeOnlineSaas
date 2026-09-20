@php
    $isEdit = $team !== null;
    $selectedCompanies = old('company_id', $team?->company_id ?? []);
    $selectedLocations = old('location_id', $team?->location_id ?? []);
    $selectedCompanies = is_array($selectedCompanies) ? $selectedCompanies : [$selectedCompanies];
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="team_name">Team Name <span class="text-danger">*</span></label><input type="text" id="team_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $team?->name) }}" placeholder="Enter team name" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label" for="team_code">Team Code <span class="text-danger">*</span></label><input type="text" id="team_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $team?->code) }}" placeholder="e.g. TM1, TEAM01" required>@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Contact Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3"><label class="form-label" for="team_email">Email</label><input id="team_email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $team?->email) }}" placeholder="team@example.com">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Organization</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="team_companies">Companies</label><select id="team_companies" name="company_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('company_id') is-invalid @enderror" data-placeholder="Select" multiple>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(in_array($company->id, $selectedCompanies))>{{ $company->name }}</option>@endforeach</select>@error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label" for="team_locations">Locations</label><select id="team_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" data-placeholder="Select" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select>@error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3"><label class="form-label" for="team_status">Status <span class="text-danger">*</span></label><select id="team_status" name="status" class="form-control select @error('status') is-invalid @enderror" required><option value="1" @selected(old('status', $team?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $team?->status ?? 1) == 0)>Inactive</option></select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div>
</div>
