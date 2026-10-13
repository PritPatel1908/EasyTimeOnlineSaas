@php
    $isEdit = $leaveGroup !== null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Basic Information</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">Leave Group Name <span class="text-danger">*</span></label>
                <input type="text" id="{{ $isEdit ? 'edit_name' : 'add_name' }}" name="name" value="{{ old('name', $leaveGroup?->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                <x-validation-error field="name" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_financial_year_id' : 'add_financial_year_id' }}">Financial Year</label>
                <select id="{{ $isEdit ? 'edit_financial_year_id' : 'add_financial_year_id' }}" name="financial_year_id" class="form-control select @error('financial_year_id') is-invalid @enderror">
                    <option value="">Select Financial Year</option>
                    @foreach ($financialYears as $financialYear)
                        <option value="{{ $financialYear->id }}" @selected((string) old('financial_year_id', $leaveGroup?->financial_year_id) === (string) $financialYear->id)>{{ $financialYear->name }}</option>
                    @endforeach
                </select>
                <x-validation-error field="financial_year_id" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_is_monthly' : 'add_is_monthly' }}">Leave Frequency <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_is_monthly' : 'add_is_monthly' }}" name="is_monthly" class="form-control select @error('is_monthly') is-invalid @enderror" required>
                    <option value="1" @selected((string) old('is_monthly', $leaveGroup?->is_monthly ?? 1) === '1')>Monthly</option>
                    <option value="0" @selected((string) old('is_monthly', $leaveGroup?->is_monthly ?? 1) === '0')>Yearly</option>
                </select>
                <x-validation-error field="is_monthly" />
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Configuration</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                    <option value="1" @selected((string) old('status', $leaveGroup?->status ?? 1) === '1')>Active</option>
                    <option value="0" @selected((string) old('status', $leaveGroup?->status ?? 1) === '0')>Inactive</option>
                </select>
                <x-validation-error field="status" />
            </div>
        </div>
    </div>
</div>

@if($leaveGroup && ($leaveGroup->monthly_rules_count ?? $leaveGroup->grade_wise_monthly_leave_details()->count()) + ($leaveGroup->yearly_rules_count ?? $leaveGroup->grade_wise_yearly_leave_details()->count()) > 0)
    <div class="alert alert-info">This group already has leave rules. The monthly/yearly rule records are preserved when you edit these group details.</div>
@endif

@push('scripts')
<script>
    document.querySelectorAll('.leave-group-form').forEach(function (form) {
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

        function clearLeaveGroupFieldError(event) {
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

        form.addEventListener('input', clearLeaveGroupFieldError);
        form.addEventListener('change', clearLeaveGroupFieldError);
    });
</script>
@endpush
