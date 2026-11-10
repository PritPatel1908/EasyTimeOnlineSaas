@extends('layout.mainlayout')
@section('content')
@php($approvalStatus = $application->overallApprovalStatus())
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">View COFF Application</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/coff-applications') }}">COFF Applications</a></li>
                    <li class="breadcrumb-item active">#{{ $application->id }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($application->status !== 'approved' && \App\Support\TenantPermissions::userCan('CoffApplication', 'write'))
                    <a href="{{ url('time-management/coff-applications/'.$application->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/coff-applications') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Application Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Employee</small>{{ $application->user?->name }}{{ $application->user?->code ? ' ('.$application->user->code.')' : '' }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">COFF Against Date</small>{{ $application->coff_against_date?->format('Y-m-d') }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">COFF Date Range</small>{{ $application->coff_from_date?->format('Y-m-d') }} – {{ $application->coff_to_date?->format('Y-m-d') }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">COFF Period</small>{{ $application->halfDayDescription() }}</div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Approval Status</small><span class="badge bg-{{ match ($approvalStatus) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', 'cancelled' => 'dark', default => 'light text-dark'} }}">{{ ucfirst($approvalStatus) }}</span></div>
            </div></div>
        </div>
        <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Approval History</h5></div><div class="card-body">
            @forelse ($application->approval_statuses as $status)
                <div class="mb-3">
                    <h6>{{ $status->approval_flow?->approval_flow_code ?? 'COFF Approval Flow' }} — {{ ucfirst($status->approval_status) }}</h6>
                    @forelse ($status->approval_status_details as $detail)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>Level {{ $detail->level }}{{ $detail->user?->name ? ' — '.$detail->user->name : '' }}</span>
                            <span>{{ ucfirst($detail->approval_status) }}{{ $detail->timestamp ? ' on '.$detail->timestamp->format('Y-m-d H:i:s') : '' }}</span>
                        </div>
                        @if ($detail->rejection_reason)
                            <div class="small text-danger text-end">Rejection reason: {{ $detail->rejection_reason }}</div>
                        @endif
                    @empty
                        <span class="text-muted">No approval level history is available.</span>
                    @endforelse
                </div>
            @empty
                <span class="text-muted">No approval history is available.</span>
            @endforelse
        </div></div>
        @include('partials.audit-users', ['record' => $application])
    </div>
    @include('partials.footer')
</div>
@endsection
