@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper"><div class="content">
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2"><h2 class="mb-1">View Manual Leave Balance</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item"><a href="{{ url('employee-structure/manual-leave-balances') }}">Manual Leave Balances</a></li><li class="breadcrumb-item active">Balance #{{ $balance->id }}</li></ol></nav></div>
        <div class="d-flex align-items-center gap-2">
            @if (\App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'write'))<a href="{{ url('employee-structure/manual-leave-balances/'.$balance->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>@endif
            <a href="{{ url('employee-structure/manual-leave-balances') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
        </div>
    </div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Balance Information</h5></div><div class="card-body"><div class="row">
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Leave Type</small>{{ $balance->leave_type?->code }} — {{ $balance->leave_type?->description }}</div>
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Balance</small>{{ number_format((float) $balance->balance, 2) }}</div>
        <div class="col-md-3 mb-3"><small class="text-muted d-block">Date &amp; Time</small>{{ $balance->date?->format('d-m-Y h:i A') ?? '—' }}</div>
        <div class="col-12 mb-3"><small class="text-muted d-block">Employees</small>{{ $balance->Users->map(fn($employee) => trim(($employee->code ? $employee->code.' — ' : '').$employee->name))->join(', ') ?: '—' }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Locations</small>{{ $locations->pluck('name')->join(', ') ?: '—' }}</div>
        <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small>{{ $companies->pluck('name')->join(', ') ?: '—' }}</div>
        <div class="col-12"><small class="text-muted d-block">Reason</small>{{ $balance->reason ?: '—' }}</div>
    </div></div></div>
    @include('partials.audit-users', ['record' => $balance])
</div>@include('partials.footer')</div>
@endsection
