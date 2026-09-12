@extends('layouts.admin')

@section('title', ($domain ? 'Edit Domain' : 'Add Domain').' | EasyTime Online SaaS')

@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12 mt-3"><div class="sub-header py-3 px-3"><h4 class="mb-0">{{ $domain ? 'Edit Domain' : 'Add Domain' }}</h4></div></div></div>
    <div class="row"><div class="col-12 mt-3"><div class="card"><div class="card-body">
        <form method="POST" action="{{ $domain ? route('admin.domains.update', $domain->id) : route('admin.domains.store') }}">
            @csrf
            @if ($domain) @method('PUT') @endif
            <x-form-input name="domain" label="Domain" value="{{ old('domain', $domain->domain ?? '') }}" placeholder="tenant.example.com" required />
            <div class="form-group"><label for="tenant_id">Tenant</label><select id="tenant_id" name="tenant_id" class="form-control" required><option value="">Select tenant</option>@foreach ($tenants as $tenantId => $tenantLabel)<option value="{{ $tenantId }}" @selected(old('tenant_id', $domain->tenant_id ?? '') === $tenantId)>{{ $tenantLabel }}</option>@endforeach</select>@error('tenant_id')<small class="text-danger">{{ $message }}</small>@enderror</div>
            <div class="form-group"><label for="status">Status</label><select id="status" name="status" class="form-control" required>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(old('status', $domain->status ?? 'Active') === $status)>{{ $status }}</option>@endforeach</select>@error('status')<small class="text-danger">{{ $message }}</small>@enderror</div>
            <div class="mt-4"><a href="{{ route('admin.domains.index') }}" class="btn btn-light">Cancel</a> <button type="submit" class="btn btn-primary">{{ $domain ? 'Update Domain' : 'Create Domain' }}</button></div>
        </form>
    </div></div></div></div>
</div>
@endsection
