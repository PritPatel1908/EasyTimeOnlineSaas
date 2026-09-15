@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Department</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Company Structure</li>
                    <li class="breadcrumb-item"><a href="{{ url('company-structure/departments') }}">Departments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $department->name }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('Department', 'write'))
                    <a href="{{ url('company-structure/departments/'.$department->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('company-structure/departments') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-sitemap me-2 text-primary"></i>Basic Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Department Name</small><span>{{ $department->name }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Department Code</small><span>{{ $department->code }}</span></div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Contact Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Email</small><span>{{ $department->email ?: '-' }}</span></div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Organization</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small><span>{{ $department->companies->pluck('name')->join(', ') ?: '-' }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Locations</small><span>{{ $department->locations->pluck('name')->join(', ') ?: '-' }}</span></div>
            </div></div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Configuration</h5></div>
            <div class="card-body"><small class="text-muted d-block mb-1">Status</small>
                <span class="badge {{ $department->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>{{ $department->status === 1 ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
