@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">View Team</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Company Structure</li>
                    <li class="breadcrumb-item"><a href="{{ url('company-structure/teams') }}">Teams</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $team->name }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('Team', 'write'))
                    <a href="{{ url('company-structure/teams/'.$team->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('company-structure/teams') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-users me-2 text-primary"></i>Basic Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Team Name</small><span>{{ $team->name }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Team Code</small><span>{{ $team->code }}</span></div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Contact Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Email</small><span>{{ $team->email ?: '-' }}</span></div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Organization</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small><span>{{ $team->companies->pluck('name')->join(', ') ?: '-' }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Locations</small><span>{{ $team->locations->pluck('name')->join(', ') ?: '-' }}</span></div>
            </div></div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Configuration</h5></div>
            <div class="card-body"><small class="text-muted d-block mb-1">Status</small>
                <span class="badge {{ $team->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>{{ $team->status === 1 ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
