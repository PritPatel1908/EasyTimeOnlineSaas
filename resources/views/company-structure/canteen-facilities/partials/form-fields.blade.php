@php
    $isEdit = $canteenFacility !== null;
    $rules = old('rules');
    if (! is_array($rules)) {
        $rules = $canteenFacility?->rules?->map(fn ($rule) => $rule->toArray())->all() ?? [];
    }
    if ($rules === []) {
        $rules = [['total_absent_days' => '', 'company_contribution_in_percentage_wise' => false, 'company_allowance_contribution_in_fixed' => '', 'company_allowance_contribution_in_percentage' => '']];
    }
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
            <div class="col-md-12 mb-3">
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
            <div class="col-md-12 mb-3">
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Canteen Facility Rules</h5>
        <button type="button" class="btn btn-sm btn-primary" id="add-canteen-rule"><i class="ti ti-plus me-1"></i>Add Rule</button>
    </div>
    <div class="card-body" id="canteen-rules-container">
        @foreach ($rules as $index => $rule)
            <div class="canteen-rule-row border rounded p-3 mb-3" data-rule-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Rule <span class="rule-number">{{ $index + 1 }}</span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-canteen-rule"><i class="ti ti-trash me-1"></i>Remove</button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Absent Days <span class="text-danger">*</span></label>
                        <input type="number" name="rules[{{ $index }}][total_absent_days]" min="0" class="form-control" value="{{ $rule['total_absent_days'] ?? '' }}" placeholder="e.g. 2" required>
                        @error('rules.' . $index . '.total_absent_days')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Company Contribution in Percentage Wise</label>
                        <div class="form-check form-check-lg form-switch">
                            <input class="form-check-input rule-percentage-toggle" type="checkbox" role="switch" name="rules[{{ $index }}][company_contribution_in_percentage_wise]" value="1" @checked(!empty($rule['company_contribution_in_percentage_wise']))>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3 rule-fixed-group">
                        <label class="form-label">Company Allowance Contribution in Fixed</label>
                        <input type="number" name="rules[{{ $index }}][company_allowance_contribution_in_fixed]" min="0" step="0.01" class="form-control" value="{{ $rule['company_allowance_contribution_in_fixed'] ?? '' }}" placeholder="0.00">
                        @error('rules.' . $index . '.company_allowance_contribution_in_fixed')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12 mb-3 rule-percentage-group">
                        <label class="form-label">Company Allowance Contribution in Percentage</label>
                        <input type="number" name="rules[{{ $index }}][company_allowance_contribution_in_percentage]" min="0" max="100" step="0.01" class="form-control" value="{{ $rule['company_allowance_contribution_in_percentage'] ?? '' }}" placeholder="e.g. 50.00">
                        @error('rules.' . $index . '.company_allowance_contribution_in_percentage')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="canteen-rule-template">
    <div class="canteen-rule-row border rounded p-3 mb-3" data-rule-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Rule <span class="rule-number"></span></h6><button type="button" class="btn btn-sm btn-outline-danger remove-canteen-rule"><i class="ti ti-trash me-1"></i>Remove</button></div>
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Total Absent Days <span class="text-danger">*</span></label><input type="number" name="rules[__INDEX__][total_absent_days]" min="0" class="form-control" placeholder="e.g. 2" required></div>
            <div class="col-md-6 mb-3"><label class="form-label d-block">Company Contribution in Percentage Wise</label><div class="form-check form-check-lg form-switch"><input class="form-check-input rule-percentage-toggle" type="checkbox" role="switch" name="rules[__INDEX__][company_contribution_in_percentage_wise]" value="1"></div></div>
            <div class="col-md-12 mb-3 rule-fixed-group"><label class="form-label">Company Allowance Contribution in Fixed</label><input type="number" name="rules[__INDEX__][company_allowance_contribution_in_fixed]" min="0" step="0.01" class="form-control" placeholder="0.00"></div>
            <div class="col-md-12 mb-3 rule-percentage-group"><label class="form-label">Company Allowance Contribution in Percentage</label><input type="number" name="rules[__INDEX__][company_allowance_contribution_in_percentage]" min="0" max="100" step="0.01" class="form-control" placeholder="e.g. 50.00"></div>
        </div>
    </div>
 </template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('canteen-rules-container');
    const template = document.getElementById('canteen-rule-template');
    let nextIndex = {{ count($rules) }};

    function syncContributionFields(row) {
        const isPercentage = row.querySelector('.rule-percentage-toggle').checked;
        const fixedGroup = row.querySelector('.rule-fixed-group');
        const percentageGroup = row.querySelector('.rule-percentage-group');
        fixedGroup.classList.toggle('d-none', isPercentage);
        percentageGroup.classList.toggle('d-none', !isPercentage);
        fixedGroup.querySelector('input').required = !isPercentage;
        percentageGroup.querySelector('input').required = isPercentage;
    }

    function refreshRows() {
        const rows = container.querySelectorAll('.canteen-rule-row');
        rows.forEach(function (row, index) {
            row.querySelector('.rule-number').textContent = index + 1;
            row.querySelector('.remove-canteen-rule').disabled = rows.length === 1;
            syncContributionFields(row);
        });
    }

    container.addEventListener('change', function (event) {
        if (event.target.classList.contains('rule-percentage-toggle')) syncContributionFields(event.target.closest('.canteen-rule-row'));
    });
    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-canteen-rule');
        if (button && container.querySelectorAll('.canteen-rule-row').length > 1) {
            button.closest('.canteen-rule-row').remove();
            refreshRows();
        }
    });
    document.getElementById('add-canteen-rule').addEventListener('click', function () {
        container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        refreshRows();
    });
    refreshRows();
});
</script>
@endpush
