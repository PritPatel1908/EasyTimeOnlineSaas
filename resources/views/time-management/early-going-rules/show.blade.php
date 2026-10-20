@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Early Going Rule</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/early-going-rules') }}">Early Going Rules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $earlyGoingRule->code }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('EarlyGoingRule', 'write'))
                    <a href="{{ url('time-management/early-going-rules/'.$earlyGoingRule->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/early-going-rules') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-cog me-2 text-primary"></i>Rule Details</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Rule Code</small><span>{{ $earlyGoingRule->code }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Early Going (Minutes)</small><span>{{ $earlyGoingRule->ignore_early_going_minutes }}</span></div>
                <div class="col-md-12 mb-3"><small class="text-muted d-block">Description</small><span>{{ $earlyGoingRule->description ?: '—' }}</span></div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge {{ (int) $earlyGoingRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                        <i class="ti ti-point-filled me-1"></i>{{ (int) $earlyGoingRule->status === 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div></div>
        </div>
        @include('partials.audit-users', ['record' => $earlyGoingRule])
    </div>
    @include('partials.footer')
</div>
@endsection
