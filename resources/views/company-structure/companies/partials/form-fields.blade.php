{{--
    Shared form fields for Add (modal) and Edit (full page) company forms.
    Variables expected:
      $company  – Company model instance or null
      $locations – Collection of active Location models
--}}

@php
    $isEdit = $company !== null;
    $selectedLocationIds = old('location_id', $company?->location_id ?? []);
    $selectedLocationIds = is_array($selectedLocationIds) ? $selectedLocationIds : [$selectedLocationIds];
@endphp

{{-- Validation errors --}}
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
            {{-- Company Name --}}
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">
                    Company Name <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    id="{{ $isEdit ? 'edit_name' : 'add_name' }}"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $company?->name) }}"
                    placeholder="Enter company name"
                    required>
                <x-validation-error field="name" />
            </div>

            {{-- Company Code --}}
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_code' : 'add_code' }}">
                    Company Code <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    id="{{ $isEdit ? 'edit_code' : 'add_code' }}"
                    name="code"
                    class="form-control @error('code') is-invalid @enderror"
                    value="{{ old('code', $company?->code) }}"
                    placeholder="e.g. HO, BR1"
                    required>
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
            {{-- Email --}}
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_email' : 'add_email' }}">
                    Email
                </label>
                <input
                    type="email"
                    id="{{ $isEdit ? 'edit_email' : 'add_email' }}"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $company?->email) }}"
                    placeholder="company@example.com">
                <x-validation-error field="email" />
            </div>
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
            {{-- Locations --}}
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_location_id' : 'add_location_id' }}">
                    Locations
                </label>
                <select
                    id="{{ $isEdit ? 'edit_location_id' : 'add_location_id' }}"
                    name="location_id[]"
                    class="form-control select2 js-example-placeholder-multiple js-states @error('location_id') is-invalid @enderror"
                    placeholder="Select"
                    multiple>
                    @foreach ($locations as $location)
                        <option
                            value="{{ $location->id }}"
                            @selected(in_array($location->id, $selectedLocationIds))>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                <x-validation-error field="location_id" />
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
            {{-- Status --}}
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">
                    Status <span class="text-danger">*</span>
                </label>
                <select
                    id="{{ $isEdit ? 'edit_status' : 'add_status' }}"
                    name="status"
                    class="form-control select @error('status') is-invalid @enderror"
                    required>
                    <option value="1" @selected(old('status', $company?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $company?->status ?? 1) == 0)>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
        </div>

        @push('scripts')
        <script>
            document.querySelectorAll('.company-form').forEach(function (form) {
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

                function clearCompanyFieldError(event) {
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

                form.addEventListener('input', clearCompanyFieldError);
                form.addEventListener('change', clearCompanyFieldError);
            });
        </script>
        @endpush
    </div>
</div>
