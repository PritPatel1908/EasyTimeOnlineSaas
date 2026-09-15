@php
    $selectedDepartments = old('department_id', $subDepartment?->department_id ?? []);
    $selectedLocations = old('location_id', $subDepartment?->location_id ?? []);
    $selectedDepartments = is_array($selectedDepartments) ? $selectedDepartments : [$selectedDepartments];
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div><div class="card-body"><div class="row">
    <div class="col-md-6 mb-3"><label class="form-label" for="sub_department_name">Sub Department Name <span class="text-danger">*</span></label><input id="sub_department_name" name="name" class="form-control" value="{{ old('name', $subDepartment?->name) }}" required></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="sub_department_code">Sub Department Code <span class="text-danger">*</span></label><input id="sub_department_code" name="code" class="form-control" value="{{ old('code', $subDepartment?->code) }}" required></div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Contact Information</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="sub_department_email">Email</label><input id="sub_department_email" type="email" name="email" class="form-control" value="{{ old('email', $subDepartment?->email) }}"></div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Organization</h5></div><div class="card-body"><div class="row">
    <div class="col-md-6 mb-3"><label class="form-label" for="sub_department_departments">Departments</label><select id="sub_department_departments" name="department_id[]" class="form-control select2 js-example-placeholder-multiple js-states" multiple>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected(in_array($department->id, $selectedDepartments))>{{ $department->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="sub_department_locations">Locations</label><select id="sub_department_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select></div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="sub_department_status">Status <span class="text-danger">*</span></label><select id="sub_department_status" name="status" class="form-control" required><option value="1" @selected(old('status', $subDepartment?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $subDepartment?->status ?? 1) == 0)>Inactive</option></select></div>
</div></div></div>
