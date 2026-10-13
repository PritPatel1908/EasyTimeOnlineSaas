@php
    $isEdit = $leaveType !== null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body"><div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_code' : 'add_code' }}">Leave Type Code <span class="text-danger">*</span></label>
            <input type="text" id="{{ $isEdit ? 'edit_code' : 'add_code' }}" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $leaveType?->code) }}" placeholder="e.g. CL, SL, PL" required>
            <x-validation-error field="code" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_location_id' : 'add_location_id' }}">Location</label>
            <select id="{{ $isEdit ? 'edit_location_id' : 'add_location_id' }}" name="location_id" class="form-control select @error('location_id') is-invalid @enderror">
                <option value="">Select Location</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) old('location_id', $leaveType?->location_id) === (string) $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
            <x-validation-error field="location_id" />
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label" for="{{ $isEdit ? 'edit_description' : 'add_description' }}">Description</label>
            <textarea id="{{ $isEdit ? 'edit_description' : 'add_description' }}" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Enter leave type description">{{ old('description', $leaveType?->description) }}</textarea>
            <x-validation-error field="description" />
        </div>
    </div></div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.leave-type-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var invalidFields = Array.from(form.elements).filter(function (field) {
                return field.willValidate && !field.validity.valid;
            });
            if (invalidFields.length === 0) return;
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

        function clearLeaveTypeFieldError(event) {
            var field = event.target;
            if (!field.validity || !field.validity.valid) return;
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

        form.addEventListener('input', clearLeaveTypeFieldError);
        form.addEventListener('change', clearLeaveTypeFieldError);
    });
</script>
@endpush

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body"><div class="row"><div class="col-md-12 mb-3">
        <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
        <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
            <option value="1" @selected(old('status', $leaveType?->status ?? 1) == 1)>Active</option>
            <option value="0" @selected(old('status', $leaveType?->status ?? 1) == 0)>Inactive</option>
        </select>
        <x-validation-error field="status" />
    </div></div></div>
</div>
