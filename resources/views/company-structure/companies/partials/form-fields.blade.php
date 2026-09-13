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
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="col-md-6 mb-3">
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
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Locations --}}
    <div class="col-md-6 mb-3">
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
        @error('location_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6 mb-3">
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
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
