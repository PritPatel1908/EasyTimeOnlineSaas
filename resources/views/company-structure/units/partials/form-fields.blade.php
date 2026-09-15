@php
    $selectedLocations = old('location_id', $unit?->location_id ?? []);
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div><div class="card-body"><div class="row">
    <div class="col-md-6 mb-3"><label class="form-label" for="unit_name">Unit Name <span class="text-danger">*</span></label><input id="unit_name" name="name" class="form-control" value="{{ old('name', $unit?->name) }}" required></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="unit_code">Unit Code <span class="text-danger">*</span></label><input id="unit_code" name="code" class="form-control" value="{{ old('code', $unit?->code) }}" required></div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Organization</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="unit_locations">Locations</label><select id="unit_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select>@error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="unit_status">Status <span class="text-danger">*</span></label><select id="unit_status" name="status" class="form-control" required><option value="1" @selected(old('status', $unit?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $unit?->status ?? 1) == 0)>Inactive</option></select></div>
</div></div></div>