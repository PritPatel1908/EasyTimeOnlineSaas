@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2"><h2 class="mb-1">View Machine</h2><nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Company Structure</li>
            <li class="breadcrumb-item"><a href="{{ url('company-structure/machines') }}">Machines</a></li><li class="breadcrumb-item active">{{ $machine->name }}</li>
        </ol></nav></div>
        <div class="d-flex align-items-center gap-2">
            @if (\App\Support\TenantPermissions::userCan('Machine', 'write'))<a href="{{ url('company-structure/machines/'.$machine->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>@endif
            <a href="{{ url('company-structure/machines') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="ti ti-device-desktop me-2 text-primary"></i>Basic Information</h5></div>
        <div class="card-body"><div class="row">
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Machine Name</small><span>{{ $machine->name }}</span></div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Serial Number</small><span>{{ $machine->machine_serial_number ?? '—' }}</span></div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block">IP Address</small><span>{{ $machine->machine_ip ?? '—' }}</span></div>
            <div class="col-md-6 mb-3"><small class="text-muted d-block">Access Direction</small><span>{{ $machine->access_direction ? ucfirst($machine->access_direction) : '—' }}</span></div>
        </div></div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Organization</h5></div>
        <div class="card-body"><div class="row">
            <div class="col-md-4 mb-3"><small class="text-muted d-block">Company</small><span>{{ $machine->company?->name ?? '—' }}</span></div>
            <div class="col-md-4 mb-3"><small class="text-muted d-block">Location</small><span>{{ $machine->location?->name ?? '—' }}</span></div>
            <div class="col-md-4 mb-3"><small class="text-muted d-block">Area</small><span>{{ $machine->area?->name ?? '—' }}</span></div>
        </div></div>
    </div>
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Configuration</h5></div>
        <div class="card-body"><div class="row">
            <div class="col-md-4 mb-3"><small class="text-muted d-block">DMS Device ID</small><span>{{ $machine->dms_device_id ?? '—' }}</span></div>
            <div class="col-md-4 mb-3"><small class="text-muted d-block">DMS Area ID</small><span>{{ $machine->dms_area_id ?? '—' }}</span></div>
            <div class="col-md-4 mb-3"><small class="text-muted d-block">Network Status</small><span>{{ ucfirst($machine->network_status) }}</span></div>
            <div class="col-md-4 mb-3"><small class="text-muted d-block">Sync with DMS</small><span>{{ $machine->sync_dms ? 'Yes' : 'No' }}</span></div>
            <div class="col-md-4 mb-3"><small class="text-muted d-block mb-1">Status</small>
                <span class="badge {{ $machine->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>{{ $machine->status === 1 ? 'Active' : 'Inactive' }}</span>
            </div>
        </div></div>
    </div>
    @include('partials.audit-users', ['record' => $machine])
</div>@include('partials.footer')</div>
@endsection
