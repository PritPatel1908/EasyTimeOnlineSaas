@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Leave Application</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/leave-applications') }}">Leave Applications</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $application->id }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('LeaveApplication', 'write'))
                    <a href="{{ url('time-management/leave-applications/'.$application->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/leave-applications') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Application Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-12 mb-3"><small class="text-muted d-block">Employees</small><span>{{ $application->users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Leave Type</small><span>{{ $application->leave_type?->code }}{{ $application->leave_type?->description ? ' — '.$application->leave_type->description : '' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Leave Reason</small><span>{{ $application->leave_reason?->leave_reason ?: '—' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Leave Count</small><span>{{ $application->leave_count ?? '—' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">From Date</small><span>{{ $application->from_date?->format('Y-m-d') ?? '—' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">To Date</small><span>{{ $application->to_date?->format('Y-m-d') ?? '—' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Application Date</small><span>{{ $application->application_date?->format('Y-m-d H:i') ?? '—' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Single Half Day</small><span>{{ $application->is_only_half ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">First Half</small><span>{{ $application->is_first_half ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Second Half</small><span>{{ $application->is_second_half ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Status</small><span class="badge bg-{{ (int) $application->status === 1 ? 'success' : 'secondary' }}">{{ (int) $application->status === 1 ? 'Active' : 'Inactive' }}</span></div>
                <div class="col-md-12 mb-3"><small class="text-muted d-block">Reason Explanation</small><span>{{ $application->reason_explanation ?: '—' }}</span></div>
            </div></div>
        </div>
        @include('partials.audit-users', ['record' => $application])
    </div>
    @include('partials.footer')
</div>
@endsection
