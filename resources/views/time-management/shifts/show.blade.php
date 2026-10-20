@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Shift</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/shifts') }}">Shifts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $shift->name }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if (\App\Support\TenantPermissions::userCan('Shift', 'write'))
                    <a href="{{ url('time-management/shifts/'.$shift->id.'/edit') }}" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                @endif
                <a href="{{ url('time-management/shifts') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="ti ti-clock me-2 text-primary"></i>Basic Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Shift Name</small><span>{{ $shift->name }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Shift Code</small><span>{{ $shift->code }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Companies</small><span>{{ $companies->pluck('name')->join(', ') ?: '—' }}</span></div>
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Locations</small><span>{{ $locations->pluck('name')->join(', ') ?: '—' }}</span></div>
            </div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Shift Timing</h5></div>
            <div class="card-body"><div class="row">
                @foreach ([
                    'in_time' => 'Shift Start Time',
                    'out_time' => 'Shift End Time',
                    'first_half_end_time' => 'First Half End Time',
                    'second_half_start_time' => 'Second Half Start Time',
                    'brack_start_time' => 'Break Start Time',
                    'brack_end_time' => 'Break End Time',
                    'auto_from' => 'Automatic Attendance From',
                    'auto_to' => 'Automatic Attendance To',
                ] as $field => $label)
                    <div class="col-md-3 mb-3">
                        <small class="text-muted d-block">{{ $label }}</small>
                        <span>{{ $shift->{$field}?->format('H:i:s') ?? '—' }}</span>
                    </div>
                @endforeach
            </div></div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Configuration</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Night Shift</small><span>{{ $shift->is_night_shift ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Set Cutoff</small><span>{{ $shift->set_cutoff ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-4 mb-3"><small class="text-muted d-block">Cutoff Time</small><span>{{ $shift->cutoff_time?->format('H:i:s') ?? '—' }}</span></div>
                <div class="col-md-4 mb-3">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge {{ (int) $shift->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                        <i class="ti ti-point-filled me-1"></i>{{ (int) $shift->status === 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div></div>
        </div>
        @include('partials.audit-users', ['record' => $shift])
    </div>
    @include('partials.footer')
</div>
@endsection
