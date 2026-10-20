@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Absent Rule</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/absent-rules') }}">Absent Rules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $absentRule->code }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('AbsentRule', 'write'))
                    <a href="{{ url('time-management/absent-rules/'.$absentRule->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/absent-rules') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-cog me-2 text-primary"></i>Rule Details</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Rule Code</small><span>{{ $absentRule->code }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Status</small>
                        <span class="badge {{ (int) $absentRule->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                            <i class="ti ti-point-filled me-1"></i>{{ (int) $absentRule->status === 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-md-12 mb-3"><small class="text-muted d-block">Description</small><span>{{ $absentRule->description ?: '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Allow Single Punch</small><span>{{ $absentRule->allow_single_punch ? 'Yes' : 'No' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Work Hours Less Than (Minutes)</small><span>{{ $absentRule->work_hr_less_than_minutes }}</span></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-exclamation me-2 text-primary"></i>Late Coming Rule</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Late Coming Minutes</small><span>{{ $absentRule->late_coming_minutes ?? '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Number of Late Occurrences</small><span>{{ $absentRule->no_of_late ?? '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Consecutive Late Coming</small><span>{{ $absentRule->consecutive_late_coming ?? '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Month-end Late</small><span>{{ $absentRule->ignore_month_end_late ? 'Yes' : 'No' }}</span></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock-check me-2 text-primary"></i>Early Going Rule</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Early Going Minutes</small><span>{{ $absentRule->early_going_minutes ?? '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Number of Early Departures</small><span>{{ $absentRule->no_of_early ?? '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Consecutive Early Going</small><span>{{ $absentRule->consecutive_early_going ?? '—' }}</span></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Ignore Month-end Early</small><span>{{ $absentRule->ignore_month_end_early ? 'Yes' : 'No' }}</span></div>
                </div>
            </div>
        </div>
        @include('partials.audit-users', ['record' => $absentRule])
    </div>
    @include('partials.footer')
</div>
@endsection
