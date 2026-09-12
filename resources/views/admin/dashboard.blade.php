@extends('layouts.admin')

@section('title', 'Dashboard | EasyTime Online SaaS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 align-self-center">
            <div class="sub-header mt-3 py-3 px-3 align-self-center d-sm-flex w-100 rounded">
                <div class="w-sm-100 mr-auto"><h4 class="mb-0">SaaS Overview</h4><b>Manage your tenants, domains, and users</b></div>
                <ol class="breadcrumb bg-transparent align-self-center m-0 p-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li><li class="breadcrumb-item active">Dashboard</li></ol>
            </div>
        </div>
    </div>

    <div class="row" id="tenant-growth">
        <x-stat-card icon="traffic.png" title="Total Tenants" period="All organizations" :value="$tenantCount" trend="Registered tenants" />
        <x-stat-card icon="cart.png" title="New This Month" period="Current month" :value="$newTenantCount" trend="New tenants" />
        <x-stat-card icon="money.png" title="Tenant Domains" period="All domains" :value="$domainCount" trend="Configured domains" />
        <x-stat-card icon="wallet.png" title="Platform Users" period="All users" :value="$userCount" trend="Registered users" />
    </div>

    <div class="row" id="tenants">
        <div class="col-12 col-xl-8 mt-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"><h4 class="card-title">Recent Tenants</h4><span class="text-muted">Latest 5</span></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap">
                            <thead><tr><th class="border-top-0">Tenant</th><th class="border-top-0">Domains</th><th class="border-top-0">Created</th><th class="border-top-0">Status</th></tr></thead>
                            <tbody>
                                @forelse ($tenants as $tenant)
                                    <tr><th scope="row">{{ $tenant->name }}<br><small class="text-muted">{{ $tenant->id }}</small></th><td>{{ $tenant->domains_count }}</td><td>{{ $tenant->created_at?->format('d M Y') ?? 'Not available' }}</td><td><span class="badge badge-success">Active</span></td></tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No tenants have been registered yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4 mt-3" id="tenant-domains">
            <div class="card h-100"><div class="card-header"><h4 class="card-title">Tenant Domains</h4></div><div class="card-body"><div class="text-center py-3"><i class="icon-globe display-3 color-primary"></i><h2 class="mt-3 mb-1">{{ $domainCount }}</h2><p class="text-muted mb-0">Domains connected to tenant workspaces</p></div></div></div>
        </div>
    </div>

    <div class="row" id="users">
        <div class="col-12 col-lg-6 mt-3" id="activity"><div class="card"><div class="card-header"><h4 class="card-title">Platform Activity</h4></div><div class="card-body"><ul class="activities mt-4 mb-2"><li class="activity py-2 px-2 border-left"><span class="bg-primary"></span><span>Tenant management</span><p class="mt-3 mb-0"><b>{{ $tenantCount }}</b> tenant workspaces are available in the central admin.</p></li><li class="activity py-2 px-2 border-left"><span class="bg-success"></span><span>Domain management</span><p class="mt-3 mb-0"><b>{{ $domainCount }}</b> tenant domains are configured.</p></li><li class="activity py-2 px-2 border-left"><span class="bg-warning"></span><span>User management</span><p class="mt-3 mb-0"><b>{{ $userCount }}</b> platform users are registered.</p></li></ul></div></div></div>
        <div class="col-12 col-lg-6 mt-3" id="settings"><div class="card"><div class="card-header"><h4 class="card-title">Central Administration</h4></div><div class="card-body"><p class="text-muted">Use the navigation to manage tenant workspaces, domains, and platform users. Subscription and payroll sections will appear once their project modules are added.</p><a href="{{ route('admin.dashboard') }}#tenants" class="btn btn-primary">Review tenants</a></div></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('admin-dist/vendors/raphael/raphael.min.js') }}"></script>
<script src="{{ asset('admin-dist/vendors/morris/morris.min.js') }}"></script>
<script src="{{ asset('admin-dist/vendors/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('admin-dist/vendors/starrr/starrr.js') }}"></script>
@endpush

