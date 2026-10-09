@php
    $isEdit = $department !== null;
    $selectedCompanies = old('company_id', $department?->company_id ?? []);
    $selectedLocations = old('location_id', $department?->location_id ?? []);
    $selectedCompanies = is_array($selectedCompanies) ? $selectedCompanies : [$selectedCompanies];
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

{{-- Basic Information Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Basic Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="department_name">Department Name <span class="text-danger">*</span></label><input id="department_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department?->name) }}" required><x-validation-error field="name" /></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="department_code">Department Code <span class="text-danger">*</span></label><input id="department_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department?->code) }}" required><x-validation-error field="code" /></div>
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
            <div class="col-md-12 mb-3"><label class="form-label" for="department_email">Email</label><input id="department_email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $department?->email) }}"><x-validation-error field="email" /></div>
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
            <div class="col-md-6 mb-3"><label class="form-label" for="department_companies">Companies</label><select id="department_companies" name="company_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('company_id') is-invalid @enderror" placeholder="Select" multiple>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(in_array($company->id, $selectedCompanies))>{{ $company->name }}</option>@endforeach</select><x-validation-error field="company_id" /></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="department_locations">Locations</label><select id="department_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" placeholder="Select" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select><x-validation-error field="location_id" /></div>
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
            <div class="col-md-12 mb-3"><label class="form-label" for="department_status">Status <span class="text-danger">*</span></label><select id="department_status" name="status" class="form-control @error('status') is-invalid @enderror" required><option value="1" @selected(old('status', $department?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $department?->status ?? 1) == 0)>Inactive</option></select><x-validation-error field="status" /></div>
        </div>

        @push('scripts')
        <script>
            document.querySelectorAll('.department-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var invalidFields = Array.from(form.elements).filter(function (field) {
                        return field.willValidate && !field.validity.valid;
                    });

                    if (invalidFields.length === 0) {
                        return;
                    }

                    event.preventDefault();

                    invalidFields.forEach(function (field) {
                        var error = Array.from(form.querySelectorAll('[data-validation-error-for]')).find(function (node) {
                            return node.dataset.validationErrorFor === field.name;
                        });

                        if (error) {
                            error.textContent = field.validity.valueMissing ? 'This field is required.' : field.validationMessage;
                            error.hidden = false;
                        }

                        field.classList.add('is-invalid');
                        field.setAttribute('aria-invalid', 'true');
                    });

                    invalidFields[0].focus();
                });

                function clearDepartmentFieldError(event) {
                    var field = event.target;

                    if (!field.validity || !field.validity.valid) {
                        return;
                    }

                    var error = Array.from(form.querySelectorAll('[data-validation-error-for]')).find(function (node) {
                        return node.dataset.validationErrorFor === field.name;
                    });

                    if (error) {
                        error.textContent = '';
                        error.hidden = true;
                    }

                    field.classList.remove('is-invalid');
                    field.removeAttribute('aria-invalid');
                }

                form.addEventListener('input', clearDepartmentFieldError);
                form.addEventListener('change', clearDepartmentFieldError);
            });
        </script>
        @endpush
    </div>
</div>
