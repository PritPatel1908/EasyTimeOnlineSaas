@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">View Approval Flow</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Employee Structure</li>
                        <li class="breadcrumb-item"><a href="{{ url('employee-structure/approval-flows') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item active">{{ $approvalFlow->approval_flow_code }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(\App\Support\TenantPermissions::userCan('ApprovalFlow', 'write'))
                    <a href="{{ url('employee-structure/approval-flows/'.$approvalFlow->id.'/edit') }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>Edit
                    </a>
                @endif
                <a href="{{ url('employee-structure/approval-flows') }}" class="btn btn-light">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Approval Flow Code</small>
                        {{ $approvalFlow->approval_flow_code }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge {{ $approvalFlow->status === 1 ? 'badge-success' : 'badge-danger' }}">
                            {{ $approvalFlow->status === 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Approval Levels &amp; Approvers</h5></div>
            <div class="card-body">
                <div class="row">
                @forelse($approvalFlow->approval_flow_details->sortBy('level') as $detail)
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block">Level {{ $detail->level }}</small>
                            <div class="mt-2">
                                {{ $detail->users->map(fn($user) => $user->name . ($user->email ? ' — '.$user->email : ''))->join(', ') ?: 'No approvers assigned' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12"><p class="text-muted mb-0">No approval levels or approvers are configured.</p></div>
                @endforelse
                </div>
            </div>
        </div>

        @include('partials.audit-users', ['record' => $approvalFlow])
    </div>
</div>
@include('partials.footer')
@endsection
