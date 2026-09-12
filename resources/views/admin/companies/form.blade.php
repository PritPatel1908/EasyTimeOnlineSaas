@extends('layouts.admin')

@section('title', ($company ? 'Edit Company' : 'Add Company').' | EasyTime Online SaaS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/bootstrap4-toggle/css/bootstrap4-toggle.min.css') }}">
@endpush

@section('content')
<div class="container-fluid"><div class="row"><div class="col-12 mt-3"><div class="sub-header py-3 px-3"><h4 class="mb-0">{{ $company ? 'Edit Company' : 'Add Company' }}</h4></div></div></div><div class="row"><div class="col-12 mt-3"><div class="card"><div class="card-body">
    <form method="POST" action="{{ $company ? route('admin.companies.update', $company) : route('admin.companies.store') }}">
        @csrf
        @if ($company) @method('PUT') @endif
        <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
            <legend class="float-none w-auto px-2 mb-3 h6 text-primary">Company details</legend>
            <x-form-input name="name" label="Company name" value="{{ old('name', $company->name ?? '') }}" required />
            <div class="row"><div class="col-md-6"><x-form-input name="email" label="Email" type="email" value="{{ old('email', $company->email ?? '') }}" /></div><div class="col-md-6"><x-form-input name="phone" label="Phone" value="{{ old('phone', $company->phone ?? '') }}" /></div></div>
            <x-form-input name="address" label="Address" value="{{ old('address', $company->address ?? '') }}" />
            <div class="form-group"><label for="status">Status</label><select id="status" name="status" class="form-control" required>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(old('status', $company->status ?? 'Active') === $status)>{{ $status }}</option>@endforeach</select>@error('status')<small class="text-danger">{{ $message }}</small>@enderror</div>
        </fieldset>
        @php($currentLicense = $company?->licenses?->first())
        @php($previousLicense = $company?->licenses?->get(1))
        <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
            <legend class="float-none w-auto px-2 mb-3 h6 text-primary">Current licence</legend>
            @if ($currentLicense?->license_key)
                <div class="form-group"><label for="license-key">Licence key</label><div class="input-group"><input id="license-key" class="form-control" value="{{ $currentLicense->license_key }}" readonly><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" data-copy-target="#license-key">Copy</button></div></div><small class="form-text text-muted">This encrypted key contains the company name, enabled features, usage limits, and expiry date.</small></div>
            @endif
            <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
                <legend class="float-none w-auto px-2 mb-3 h6 text-secondary">Features</legend>
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="have_leave">Leave</label><div><input type="hidden" name="have_leave" value="0"><input id="have_leave" name="have_leave" type="checkbox" value="1" data-toggle="toggle" data-on="Yes" data-off="No" data-onstyle="primary" @checked((string) old('have_leave', $currentLicense?->have_leave ? '1' : '0') === '1')></div>@error('have_leave')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                    <div class="col-md-6"><div class="form-group"><label for="have_payroll">Payroll</label><div><input type="hidden" name="have_payroll" value="0"><input id="have_payroll" name="have_payroll" type="checkbox" value="1" data-toggle="toggle" data-on="Yes" data-off="No" data-onstyle="primary" @checked((string) old('have_payroll', $currentLicense?->have_payroll ? '1' : '0') === '1')></div>@error('have_payroll')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
                </div>
            </fieldset>
            <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
                <legend class="float-none w-auto px-2 mb-3 h6 text-secondary">Usage limits</legend>
                <div class="row"><div class="col-md-4"><x-form-input name="location_count" label="Location count" type="number" min="0" value="{{ old('location_count', $currentLicense?->location_count ?? 0) }}" /></div><div class="col-md-4"><x-form-input name="company_count" label="Company count" type="number" min="0" value="{{ old('company_count', $currentLicense?->company_count ?? 0) }}" /></div><div class="col-md-4"><x-form-input name="user_count" label="User count" type="number" min="0" value="{{ old('user_count', $currentLicense?->user_count ?? 0) }}" /></div></div>
            </fieldset>
            <fieldset class="border rounded px-3 pt-2 pb-1 mb-2">
                <legend class="float-none w-auto px-2 mb-3 h6 text-secondary">Validity</legend>
                <x-form-input name="expiry_date" label="Expiry date" type="date" value="{{ old('expiry_date', $currentLicense?->expiry_date?->format('Y-m-d') ?? '') }}" />
            </fieldset>
        </fieldset>
        @if ($previousLicense)
            <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
                <legend class="float-none w-auto px-2 mb-3 h6 text-muted">Previous licence</legend>
                <div class="alert alert-light border mb-2">Expires {{ $previousLicense->expiry_date->format('d M Y') }} | Locations: {{ $previousLicense->location_count }} | Companies: {{ $previousLicense->company_count }} | Users: {{ $previousLicense->user_count }} | Leave: {{ $previousLicense->have_leave ? 'Yes' : 'No' }} | Payroll: {{ $previousLicense->have_payroll ? 'Yes' : 'No' }}</div>
            </fieldset>
        @endif
        <div class="mt-4"><a href="{{ route('admin.companies.index') }}" class="btn btn-light">Cancel</a> <button type="submit" class="btn btn-primary">{{ $company ? 'Update Company' : 'Create Company' }}</button></div>
    </form>
</div></div></div></div></div>
@endsection

@push('scripts')
    <script src="{{ asset('admin-dist/vendors/bootstrap4-toggle/js/bootstrap4-toggle.min.js') }}"></script>
@endpush
