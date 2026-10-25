@extends('layout.mainlayout')
@section('content')
@php($status = $application->overallApprovalStatus())
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Week-off Swap Application</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/week-off-swap-applications') }}">Week-off Swap Applications</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $application->week_date?->format('Y-m-d') }} → {{ $application->swap_date?->format('Y-m-d') }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('WeekOffSwapApplication', 'write'))
                    <a href="{{ url('time-management/week-off-swap-applications/'.$application->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/week-off-swap-applications') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Application Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Employees</small><span>{{ $application->Users->map(fn ($user) => $user->name.($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Week Date</small><span>{{ $application->week_date?->format('Y-m-d') ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Swap Date</small><span>{{ $application->swap_date?->format('Y-m-d') ?? '—' }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Reason</small><span>{{ $application->reason ?: '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Approval Status</small><span class="badge bg-{{ match ($status) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', default => 'light text-dark'} }}">{{ ucfirst($status) }}</span></div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Approval History</h5></div>
            <div class="card-body">
                @forelse ($application->approval_statuses as $approvalStatus)
                    <div class="mb-3">
                        <h6>{{ $approvalStatus->approval_flow?->approval_flow_code ?? 'Approval Flow' }} — {{ ucfirst($approvalStatus->approval_status) }}</h6>
                        @php($employees = $employeesByApprovalFlow->get($approvalStatus->approval_flow_id, collect()))
                        <div class="small text-muted mb-2">
                            Employees: {{ $employees->map(fn ($user) => $user->name.($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: 'No matching employee' }}
                        </div>
                        @forelse ($approvalStatus->approval_status_details as $detail)
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
