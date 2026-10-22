@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Manual Attendance</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/manual-attendances') }}">Manual Attendances</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $attendance->in_time?->format('Y-m-d H:i:s') }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('ManualAttendance', 'write'))
                    <a href="{{ url('time-management/manual-attendances/'.$attendance->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/manual-attendances') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Attendance Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Employees</small><span>{{ $attendance->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">In Time</small><span>{{ $attendance->in_time?->format('Y-m-d H:i:s') ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Out Time</small><span>{{ $attendance->out_time?->format('Y-m-d H:i:s') ?? '—' }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Reason</small><span>{{ $attendance->reason ?: '—' }}</span></div>
            </div></div>
        </div>

        @include('partials.audit-users', ['record' => $attendance])
    </div>
    @include('partials.footer')
</div>
@endsection
