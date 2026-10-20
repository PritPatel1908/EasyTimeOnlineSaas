@php
    $selectedLocationIds = old('location_id', $financialYear?->location_id ?? []);
    $selectedCompanyIds = old('company_id', $financialYear?->company_id ?? []);
    $selectedLocationId = is_array($selectedLocationIds) ? ($selectedLocationIds[0] ?? '') : $selectedLocationIds;
    $selectedCompanyId = is_array($selectedCompanyIds) ? ($selectedCompanyIds[0] ?? '') : $selectedCompanyIds;
@endphp

@if ($errors->any())
    <div class="alert alert-danger">Please correct the highlighted fields.</div>
@endif

<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Financial Year Details</h5></div><div class="card-body">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="financial_year_code">Code <span class="text-danger">*</span></label>
            <input id="financial_year_code" name="code" type="text" maxlength="50" required class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $financialYear?->code) }}" placeholder="e.g. FY26-27">
            <x-validation-error field="code" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="financial_year_name">Name</label>
            <input id="financial_year_name" name="name" type="text" maxlength="255" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $financialYear?->name) }}" placeholder="e.g. Financial Year 2026-27">
            <x-validation-error field="name" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="financial_year_start_date">Start Date <span class="text-danger">*</span></label>
            <input id="financial_year_start_date" name="start_date" type="date" required class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $financialYear?->start_date?->format('Y-m-d')) }}" @disabled($financialYear?->is_started)>
            <x-validation-error field="start_date" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="financial_year_end_date">End Date <span class="text-danger">*</span></label>
            <input id="financial_year_end_date" name="end_date" type="date" required min="{{ old('start_date', $financialYear?->start_date?->format('Y-m-d')) }}" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $financialYear?->end_date?->format('Y-m-d')) }}" @disabled($financialYear?->is_started)>
            <x-validation-error field="end_date" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="financial_year_locations">Locations</label>
            <select id="financial_year_locations" name="location_id" class="form-control select @error('location_id') is-invalid @enderror">
                <option value="">Select Location</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) $location->id === (string) $selectedLocationId)>{{ $location->name }} ({{ $location->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="location_id" />
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="financial_year_companies">Companies</label>
            <select id="financial_year_companies" name="company_id" class="form-control select @error('company_id') is-invalid @enderror">
                <option value="">Select Company</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) $company->id === (string) $selectedCompanyId)>{{ $company->name }} ({{ $company->code }})</option>
                @endforeach
            </select>
            <x-validation-error field="company_id" />
        </div>
    </div>
</div></div>

<div class="card mb-4"><div class="card-header"><h5 class="mb-0">Configuration</h5></div><div class="card-body"><div class="row">
    <div class="col-md-12 mb-3">
        <label class="form-label" for="financial_year_status">Status <span class="text-danger">*</span></label>
        <select id="financial_year_status" name="status" class="form-control select @error('status') is-invalid @enderror" required @disabled($financialYear?->is_started)>
            <option value="1" @selected(old('status', $financialYear?->status ?? 1) == 1)>Active</option>
            <option value="0" @selected(old('status', $financialYear?->status ?? 1) == 0)>Inactive</option>
        </select>
        <x-validation-error field="status" />
    </div>
</div></div></div>
