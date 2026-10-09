@php
    $selectedCompanies = (array) old('company_id', $designation?->company_id ?? []);
    $selectedLocations = (array) old('location_id', $designation?->location_id ?? []);
    $selectedCategories = (array) old('category_id', $designation?->category_id ?? []);
@endphp
@if($errors->any())<div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>@endif
<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Basic Information</h5></div><div class="card-body"><div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="designation_name">Designation Name <span class="text-danger" aria-hidden="true">*</span></label><input id="designation_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $designation?->name) }}" required><x-validation-error field="name" /></div><div class="col-md-6 mb-3"><label class="form-label" for="designation_code">Designation Code <span class="text-danger" aria-hidden="true">*</span></label><input id="designation_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $designation?->code) }}" required><x-validation-error field="code" /></div></div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Organization</h5></div><div class="card-body"><div class="row"><div class="col-md-4 mb-3"><label class="form-label">Companies</label><select name="company_id[]" class="form-control select2" multiple>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(in_array($company->id, $selectedCompanies))>{{ $company->name }}</option>@endforeach</select></div><div class="col-md-4 mb-3"><label class="form-label">Locations</label><select name="location_id[]" class="form-control select2" multiple>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select></div><div class="col-md-4 mb-3"><label class="form-label">Categories</label><select name="category_id[]" class="form-control select2" multiple>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategories))>{{ $category->name }}</option>@endforeach</select></div></div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Configuration</h5></div><div class="card-body"><label class="form-label" for="designation_status">Status <span class="text-danger" aria-hidden="true">*</span></label><select id="designation_status" name="status" class="form-control @error('status') is-invalid @enderror" required><option value="1" @selected(old('status', $designation?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $designation?->status ?? 1) == 0)>Inactive</option></select><x-validation-error field="status" /></div></div>

@push('scripts')
<script>
    document.querySelectorAll('.designation-form').forEach(function (form) {
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

        function clearDesignationFieldError(event) {
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

        form.addEventListener('input', clearDesignationFieldError);
        form.addEventListener('change', clearDesignationFieldError);
    });
</script>
@endpush
