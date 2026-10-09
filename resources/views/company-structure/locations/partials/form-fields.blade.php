@php
    $isEdit = $location !== null;
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
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">Location Name <span class="text-danger">*</span></label>
                <input type="text" id="{{ $isEdit ? 'edit_name' : 'add_name' }}" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $location?->name) }}" placeholder="Enter location name" required>
                <x-validation-error field="name" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_code' : 'add_code' }}">Location Code <span class="text-danger">*</span></label>
                <input type="text" id="{{ $isEdit ? 'edit_code' : 'add_code' }}" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $location?->code) }}" placeholder="e.g. HQ, BR1" required>
                <x-validation-error field="code" />
            </div>
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
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_email' : 'add_email' }}">Email</label>
                <input type="email" id="{{ $isEdit ? 'edit_email' : 'add_email' }}" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $location?->email) }}" placeholder="location@example.com">
                <x-validation-error field="email" />
            </div>
        </div>
    </div>
</div>

{{-- Geographic Information Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Geographic Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_latitude' : 'add_latitude' }}">Latitude</label>
                <input type="number" step="any" min="-90" max="90" id="{{ $isEdit ? 'edit_latitude' : 'add_latitude' }}" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $location?->latitude) }}" placeholder="e.g. 23.0225">
                <x-validation-error field="latitude" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_longitude' : 'add_longitude' }}">Longitude</label>
                <input type="number" step="any" min="-180" max="180" id="{{ $isEdit ? 'edit_longitude' : 'add_longitude' }}" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $location?->longitude) }}" placeholder="e.g. 72.5714">
                <x-validation-error field="longitude" />
            </div>
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
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                    <option value="1" @selected(old('status', $location?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $location?->status ?? 1) == 0)>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
        </div>

        @push('scripts')
        <script>
            document.querySelectorAll('.location-form').forEach(function (form) {
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

                function clearLocationFieldError(event) {
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

                form.addEventListener('input', clearLocationFieldError);
                form.addEventListener('change', clearLocationFieldError);
            });
        </script>
        @endpush
    </div>
</div>
