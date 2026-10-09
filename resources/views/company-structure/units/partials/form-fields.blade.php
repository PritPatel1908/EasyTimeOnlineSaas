@php
    $selectedLocations = old('location_id', $unit?->location_id ?? []);
    $selectedLocations = is_array($selectedLocations) ? $selectedLocations : [$selectedLocations];
@endphp
@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div><div class="card-body"><div class="row">
    <div class="col-md-6 mb-3"><label class="form-label" for="unit_name">Unit Name <span class="text-danger">*</span></label><input id="unit_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit?->name) }}" required><x-validation-error field="name" /></div>
    <div class="col-md-6 mb-3"><label class="form-label" for="unit_code">Unit Code <span class="text-danger">*</span></label><input id="unit_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $unit?->code) }}" required><x-validation-error field="code" /></div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Organization</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="unit_locations">Locations</label><select id="unit_locations" name="location_id[]" class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror" multiple>@foreach ($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations))>{{ $location->name }}</option>@endforeach</select><x-validation-error field="location_id" /></div>
</div></div></div>
<div class="card mb-4"><div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3"><label class="form-label" for="unit_status">Status <span class="text-danger">*</span></label><select id="unit_status" name="status" class="form-control @error('status') is-invalid @enderror" required><option value="1" @selected(old('status', $unit?->status ?? 1) == 1)>Active</option><option value="0" @selected(old('status', $unit?->status ?? 1) == 0)>Inactive</option></select><x-validation-error field="status" /></div>
</div></div></div>

@push('scripts')
<script>
    document.querySelectorAll('.unit-form').forEach(function (form) {
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

        function clearUnitFieldError(event) {
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

        form.addEventListener('input', clearUnitFieldError);
        form.addEventListener('change', clearUnitFieldError);
    });
</script>
@endpush