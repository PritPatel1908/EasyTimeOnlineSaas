@php
    $isEdit = $team !== null;
    $selectedCompanies = old('company_id', $team?->company_id ?? []);
    $selectedLocations = old('location_id', $team?->location_id ?? []);
    $selectedCompanies = is_array($selectedCompanies) ? $selectedCompanies : [$selectedCompanies];
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="team_name">Team Name <span class="text-danger">*</span></label><input type="text" id="team_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $team?->name) }}" placeholder="Enter team name" required><x-validation-error field="name" /></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="team_code">Team Code <span class="text-danger">*</span></label><input type="text" id="team_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $team?->code) }}" placeholder="e.g. TM1, TEAM01" required><x-validation-error field="code" /></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Contact Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3"><label class="form-label" for="team_email">Email</label><input id="team_email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $team?->email) }}" placeholder="team@example.com"><x-validation-error field="email" /></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Organization</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="team_companies">Companies</label><select id="team_companies" name="company_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('company_id') is-invalid @enderror" data-placeholder="Select" multiple>@foreach ($companies as $company)<option value="{{ $company->id }}" @selected(in_array($company->id, $selectedCompanies))>{{ $company->name }}</option>@endforeach</select><x-validation-error field="company_id" /></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="team_locations">Locations</label><select id="team_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" data-placeholder="Select" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select><x-validation-error field="location_id" /></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3"><label class="form-label" for="team_status">Status <span class="text-danger">*</span></label><select id="team_status" name="status" class="form-control select @error('status') is-invalid @enderror" required><option value="1" @selected(old('status', $team?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $team?->status ?? 1) == 0)>Inactive</option></select><x-validation-error field="status" /></div>
        </div>

        @push('scripts')
        <script>
            document.querySelectorAll('.team-form').forEach(function (form) {
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

                function clearTeamFieldError(event) {
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

                form.addEventListener('input', clearTeamFieldError);
                form.addEventListener('change', clearTeamFieldError);
            });
        </script>
        @endpush
    </div>
</div>
