@extends('layout.mainlayout')
@section('content')
@php($approvalStatus = $application->overallApprovalStatus())
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Short Leave Application</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/short-leave-applications') }}">Short Leave Applications</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $application->date?->format('Y-m-d') }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'create') && \App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'read'))
                    <a href="{{ url('time-management/short-leave-applications/'.$application->id.'/clone') }}" class="btn btn-light"><i class="ti ti-copy me-1"></i>Clone</a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'write'))
                    <a href="{{ url('time-management/short-leave-applications/'.$application->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/short-leave-applications') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Application Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Employee</small>{{ $application->user?->name }}{{ $application->user?->code ? ' ('.$application->user->code.')' : '' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Short Leave Type</small>{{ \App\Enums\ShortLeaveType::tryFrom((int) $application->short_leave_type)?->getLabel() ?? '—' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Date</small>{{ $application->date?->format('Y-m-d') ?? '—' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Minutes</small>{{ $application->minutes ?? '—' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">From Time</small>{{ $application->from_time?->format('H:i') ?? '—' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">To Time</small>{{ $application->to_time?->format('H:i') ?? '—' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Approval Status</small><span class="badge bg-{{ match ($approvalStatus) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', default => 'light text-dark'} }}">{{ ucfirst($approvalStatus) }}</span></div>
                <div class="col-12 mb-3"><small class="text-muted d-block">Reason</small>{{ $application->reason ?: '—' }}</div>
            </div></div>
        </div>
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Approval History</h5></div>
            <div class="card-body">
                @forelse ($application->approval_statuses as $status)
                    <div class="mb-3">
                        <h6>{{ $status->approval_flow?->approval_flow_code ?? 'Approval Flow' }} — {{ ucfirst($status->approval_status) }}</h6>
                        <div class="small text-muted mb-2">Employee: {{ $application->user?->name ?? 'Unknown employee' }}</div>
                        @forelse ($status->approval_status_details as $detail)
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Level {{ $detail->level }}{{ $detail->user?->name ? ' — '.$detail->user->name : '' }}</span>
                                <span>{{ ucfirst($detail->approval_status) }}{{ $detail->timestamp ? ' on '.$detail->timestamp->format('Y-m-d H:i:s') : '' }}</span>
                            </div>
                            @if ($detail->rejection_reason)
                                <div class="small text-danger text-end">Rejection reason: {{ $detail->rejection_reason }}</div>
                            @endif
                        @empty
                            <span class="text-muted">No level history is available.</span>
                        @endforelse
                    </div>
                @empty
                    <span class="text-muted">No approval history is available.</span>
                @endforelse
            </div>
        </div>
        @include('partials.audit-users', ['record' => $application])
    </div>
    @include('partials.footer')
</div>
@endsection
