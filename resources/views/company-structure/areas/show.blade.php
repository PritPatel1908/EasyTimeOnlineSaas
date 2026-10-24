@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2"><h2 class="mb-1">View Area</h2><nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Company Structure</li>
            <li class="breadcrumb-item"><a href="{{ url('company-structure/areas') }}">Areas</a></li><li class="breadcrumb-item active">{{ $area->name }}</li>
        </ol></nav></div>
        <div class="d-flex align-items-center gap-2">
            @if (\App\Support\TenantPermissions::userCan('Area', 'write'))<a href="{{ url('company-structure/areas/'.$area->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>@endif
            <a href="{{ url('company-structure/areas') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="ti ti-map-pin me-2 text-primary"></i>Basic Information</h5></div>
        <div class="card-body"><div class="row">
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Area Name</small><span>{{ $area->name }}</span></div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Area Code</small><span>{{ $area->code }}</span></div>
        </div></div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Organization</h5></div>
        <div class="card-body"><div class="row">
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small><span>{{ $area->companies->pluck('name')->join(', ') ?: '-' }}</span></div>
            <div class="col-md-12 mb-3"><small class="text-muted d-block">Locations</small><span>{{ $area->locations->pluck('name')->join(', ') ?: '-' }}</span></div>
        </div></div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Configuration</h5></div>
        <div class="card-body"><div class="row">
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Time Limit</small><span>{{ $area->time_limit_in_min ?? 0 }} minutes</span></div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block">DMS Area ID</small><span>{{ $area->dms_area_id ?? '-' }}</span></div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block mb-1">Status</small>
                <span class="badge {{ $area->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>{{ $area->status === 1 ? 'Active' : 'Inactive' }}</span>
            </div>
        </div></div>
    </div>
    @include('partials.audit-users', ['record' => $area])
</div>@include('partials.footer')</div>
@endsection
