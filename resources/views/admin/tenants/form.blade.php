@extends('layouts.admin')

@section('title', ($tenant ? 'Edit Tenant' : 'Create Tenant').' | EasyTime Online SaaS')

@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12 mt-3"><div class="sub-header py-3 px-3"><h4 class="mb-0">{{ $tenant ? 'Edit Tenant' : 'Create Tenant' }}</h4></div></div></div>
    <div class="row"><div class="col-12 mt-3"><div class="card"><div class="card-body">
        <form method="POST" action="{{ $tenant ? route('admin.tenants.update', $tenant->id) : route('admin.tenants.store') }}">
            @csrf
            @if ($tenant)
                @method('PUT')
            @endif
            <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
                <legend class="float-none w-auto px-2 mb-3 h6 text-primary">Tenant details</legend>
                <div class="form-group">
                    <label for="company_id">Company</label>
                    <select id="company_id" name="company_id" class="form-control" required>
                        <option value="">Select company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected((string) old('company_id', $tenant?->company_id) === (string) $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('company_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                @if ($tenant)
                    <x-form-input name="slug" label="Subdomain slug" value="{{ old('slug', $tenant->id) }}" required disabled />
                @else
                    <x-form-input name="slug" label="Subdomain slug" value="{{ old('slug') }}" placeholder="acme" required />
                    <small class="form-text text-muted mb-3">Use lowercase letters, numbers, and single hyphens.</small>
                @endif
            </fieldset>

            <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
                <legend class="float-none w-auto px-2 mb-3 h6 text-primary">Database connection</legend>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="db_connection">Database provider</label>
                            <select id="db_connection" name="db_connection" class="form-control" required>
                                @foreach (['mysql' => 'MySQL', 'pgsql' => 'PostgreSQL', 'sqlsrv' => 'SQL Server', 'sqlite' => 'SQLite'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('db_connection', data_get($tenant?->data, 'tenancy_db_connection', 'mysql')) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('db_connection')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="col-md-5"><x-form-input name="db_host" label="Database host" value="{{ old('db_host', data_get($tenant?->data, 'tenancy_db_host', '127.0.0.1')) }}" required /></div>
                    <div class="col-md-3"><x-form-input name="db_port" label="Database port" value="{{ old('db_port', data_get($tenant?->data, 'tenancy_db_port', '3306')) }}" required /></div>
                </div>
            </fieldset>

            <fieldset class="border rounded px-3 pt-2 pb-1 mb-4">
                <legend class="float-none w-auto px-2 mb-3 h6 text-primary">Database credentials</legend>
                <x-form-input name="db_database" label="Database name" value="{{ old('db_database', data_get($tenant?->data, 'tenancy_db_name')) }}" required />
                <div class="row">
                    <div class="col-md-6"><x-form-input name="db_username" label="Database username" value="{{ old('db_username', data_get($tenant?->data, 'tenancy_db_username')) }}" required /></div>
                    <div class="col-md-6">
                        <x-form-input name="db_password" type="password" label="Database password" value="" :required="!$tenant" />
                        @if ($tenant)<small class="form-text text-muted mt-n2 mb-3">Leave blank to keep the existing password.</small>@endif
                    </div>
                </div>
            </fieldset>

            <div class="mt-4"><a href="{{ route('admin.tenants.index') }}" class="btn btn-light">Cancel</a> <button type="submit" class="btn btn-primary">{{ $tenant ? 'Save Changes' : 'Create Tenant' }}</button></div>
        </form>
    </div></div></div></div>
</div>
@endsection
