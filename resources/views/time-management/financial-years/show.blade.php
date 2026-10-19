@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="d-md-flex align-items-center justify-content-between page-breadcrumb mb-3">
        <div><h2 class="mb-1">View Financial Year</h2><nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
            <li class="breadcrumb-item">Time Management</li>
            <li class="breadcrumb-item"><a href="{{ url('time-management/financial-years') }}">Financial Years</a></li>
            <li class="breadcrumb-item active">{{ $financialYear->name ?: $financialYear->code }}</li>
        </ol></nav></div>
        <div class="d-flex align-items-center gap-2">
            @if (\App\Support\TenantPermissions::userCan('FinancialYear', 'write'))
                <a href="{{ url('time-management/financial-years/'.$financialYear->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
            @endif
            <a href="{{ url('time-management/financial-years') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Financial Year Information</h5></div><div class="card-body"><div class="row">
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Code</small>{{ $financialYear->code }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Name</small>{{ $financialYear->name ?: '—' }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Start Date</small>{{ $financialYear->start_date?->format('d M Y') }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">End Date</small>{{ $financialYear->end_date?->format('d M Y') }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Locations</small>{{ $financialYear->location_records->pluck('name')->join(', ') ?: '—' }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small>{{ $financialYear->company_records->pluck('name')->join(', ') ?: '—' }}</div>
        <div class="col-md-4 mb-3"><small class="text-muted d-block">Started</small>{{ $financialYear->is_started ? 'Yes' : 'No' }}</div>
        <div class="col-md-4 mb-3"><small class="text-muted d-block">Closed</small>{{ $financialYear->is_closed ? 'Yes' : 'No' }}</div>
        <div class="col-md-4 mb-3"><small class="text-muted d-block">Status</small><span class="badge {{ (int) $financialYear->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ (int) $financialYear->status === 1 ? 'Active' : 'Inactive' }}</span></div>
    </div></div></div>
    @include('partials.audit-users', ['record' => $financialYear])
</div>@include('partials.footer')</div>
@endsection
