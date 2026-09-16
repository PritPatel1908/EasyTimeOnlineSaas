@php
    $isEdit = $canteenFacility !== null;
    $rule = $canteenFacility?->rule;
@endphp

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Basic Information Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Basic Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_name' : 'add_name' }}">Name <span class="text-danger">*</span></label>
                <input type="text" id="{{ $isEdit ? 'edit_name' : 'add_name' }}" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $canteenFacility?->name) }}" placeholder="Enter canteen facility name" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_code' : 'add_code' }}">Code <span class="text-danger">*</span></label>
                <input type="text" id="{{ $isEdit ? 'edit_code' : 'add_code' }}" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $canteenFacility?->code) }}" placeholder="e.g. CF1, CAFE" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_total_cfa' : 'add_total_cfa' }}">Total CFA <span class="text-danger">*</span></label>
                <input type="number" id="{{ $isEdit ? 'edit_total_cfa' : 'add_total_cfa' }}" name="total_cfa" min="0" step="0.01" class="form-control @error('total_cfa') is-invalid @enderror" value="{{ old('total_cfa', $canteenFacility?->total_cfa) }}" placeholder="0.00" required>
                @error('total_cfa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_location' : 'add_location' }}">Location <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_location' : 'add_location' }}" name="location_id" class="form-control select2 @error('location_id') is-invalid @enderror" required>
                    <option value="">Select Location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected(old('location_id', $canteenFacility?->location_id) == $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
                @error('location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_status' : 'add_status' }}">Status <span class="text-danger">*</span></label>
                <select id="{{ $isEdit ? 'edit_status' : 'add_status' }}" name="status" class="form-control select @error('status') is-invalid @enderror" required>
                    <option value="1" @selected(old('status', $canteenFacility?->status ?? 1) == 1)>Active</option>
                    <option value="0" @selected(old('status', $canteenFacility?->status ?? 1) == 0)>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Canteen Facility Rule Section --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Canteen Facility Rule</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="{{ $isEdit ? 'edit_total_absent_days' : 'add_total_absent_days' }}">Total Absent Days <span class="text-danger">*</span></label>
                <input type="number" id="{{ $isEdit ? 'edit_total_absent_days' : 'add_total_absent_days' }}" name="total_absent_days" min="0" class="form-control @error('total_absent_days') is-invalid @enderror" value="{{ old('total_absent_days', $rule?->total_absent_days) }}" placeholder="e.g. 2" required>
                @error('total_absent_days')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="{{ $isEdit ? 'edit_percentage_wise' : 'add_percentage_wise' }}" name="company_contribution_in_percentage_wise" value="1" @checked(old('company_contribution_in_percentage_wise', $rule?->company_contribution_in_percentage_wise ?? false))>
                    <label class="form-check-label" for="{{ $isEdit ? 'edit_percentage_wise' : 'add_percentage_wise' }}">Company Contribution in Percentage Wise</label>
                </div>
            </div>

            <div class="col-md-6 mb-3" id="{{ $isEdit ? 'edit_fixed_contribution_group' : 'add_fixed_contribution_group' }}">
                <label class="form-label" for="{{ $isEdit ? 'edit_fixed_contribution' : 'add_fixed_contribution' }}">Company Allowance Contribution in Fixed</label>
                <input type="number" id="{{ $isEdit ? 'edit_fixed_contribution' : 'add_fixed_contribution' }}" name="company_allowance_contribution_in_fixed" min="0" step="0.01" class="form-control @error('company_allowance_contribution_in_fixed') is-invalid @enderror" value="{{ old('company_allowance_contribution_in_fixed', $rule?->company_allowance_contribution_in_fixed) }}" placeholder="0.00">
                @error('company_allowance_contribution_in_fixed')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3" id="{{ $isEdit ? 'edit_percentage_contribution_group' : 'add_percentage_contribution_group' }}">
                <label class="form-label" for="{{ $isEdit ? 'edit_percentage_contribution' : 'add_percentage_contribution' }}">Company Allowance Contribution in Percentage</label>
                <input type="number" id="{{ $isEdit ? 'edit_percentage_contribution' : 'add_percentage_contribution' }}" name="company_allowance_contribution_in_percentage" min="0" max="100" step="0.01" class="form-control @error('company_allowance_contribution_in_percentage') is-invalid @enderror" value="{{ old('company_allowance_contribution_in_percentage', $rule?->company_allowance_contribution_in_percentage) }}" placeholder="e.g. 50.00">
                @error('company_allowance_contribution_in_percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleId = '{{ $isEdit ? "edit_percentage_wise" : "add_percentage_wise" }}';
    const fixedGroupId = '{{ $isEdit ? "edit_fixed_contribution_group" : "add_fixed_contribution_group" }}';
    const percentageGroupId = '{{ $isEdit ? "edit_percentage_contribution_group" : "add_percentage_contribution_group" }}';
    const fixedInputId = '{{ $isEdit ? "edit_fixed_contribution" : "add_fixed_contribution" }}';
    const percentageInputId = '{{ $isEdit ? "edit_percentage_contribution" : "add_percentage_contribution" }}';

    const toggle = document.getElementById(toggleId);
    const fixedGroup = document.getElementById(fixedGroupId);
    const percentageGroup = document.getElementById(percentageGroupId);
    const fixedInput = document.getElementById(fixedInputId);
    const percentageInput = document.getElementById(percentageInputId);

    function syncContributionFields() {
        const isPercentage = toggle.checked;

        fixedGroup.classList.toggle('d-none', isPercentage);
        percentageGroup.classList.toggle('d-none', !isPercentage);

        fixedInput.required = !isPercentage;
        percentageInput.required = isPercentage;
    }

    toggle.addEventListener('change', syncContributionFields);
    syncContributionFields();
});
</script>
@endpush
