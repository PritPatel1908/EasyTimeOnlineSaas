@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">View Attendance Log</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Time Management</li>
                        <li class="breadcrumb-item"><a href="{{ url('time-management/attendance-logs') }}">Attendance Logs</a></li>
                        <li class="breadcrumb-item active">Log #{{ $attendanceLog->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($attendanceLog->trashed() && \App\Support\TenantPermissions::userCan('AttendanceLog', 'delete'))
                    <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/restore') }}">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="ti ti-refresh me-1"></i>Restore Punch</button>
                    </form>
                @elseif (\App\Support\TenantPermissions::userCan('AttendanceLog', 'delete') && ! $attendanceLog->is_locked)
                    <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/delete') }}" onsubmit="return confirm('Delete this punch?')">
                        @csrf
                        <button type="submit" class="btn btn-light text-danger"><i class="ti ti-trash me-1"></i>Delete Punch</button>
                    </form>
                @endif
                @if (! $attendanceLog->trashed() && ! $attendanceLog->is_locked && ! $attendanceLog->is_ignored && $attendanceLog->status === 1 && \App\Support\TenantPermissions::userCan('AttendanceLog', 'write'))
                    <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/reprocess') }}" onsubmit="return confirm('Reprocess this punch and recalculate attendance?')">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="ti ti-refresh me-1"></i>Reprocess Punch</button>
                    </form>
                @endif
                <a href="{{ url('time-management/attendance-logs') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Punch Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Employee</small>{{ $attendanceLog->user?->name ?? 'Unknown employee' }}{{ $attendanceLog->user_code ? ' ('.$attendanceLog->user_code.')' : '' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Log ID</small>#{{ $attendanceLog->id }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Punch Date &amp; Time</small>{{ $attendanceLog->datetime?->format('Y-m-d H:i:s') ?? '—' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Direction</small>{{ $attendanceLog->is_out ? 'Out' : 'In' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Punch Type</small>{{ $attendanceLog->punch_type ?? '—' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Source</small>{{ $attendanceLog->is_manual ? 'Manual' : 'Device' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Device</small>{{ $attendanceLog->machine?->name ?? $attendanceLog->dms_device_id ?? '—' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Area</small>{{ $attendanceLog->area?->name ?? $attendanceLog->dms_area_id ?? '—' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Calculated</small>{{ $attendanceLog->is_calculated ? 'Yes' : 'No' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Staged</small>{{ $attendanceLog->is_staged ? 'Yes' : 'No' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Ignored</small>{{ $attendanceLog->is_ignored ? 'Yes' : 'No' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Error</small>{{ $attendanceLog->has_error ? 'Yes' : 'No' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Locked</small>{{ $attendanceLog->is_locked ? 'Yes' : 'No' }}</div>
                    <div class="col-md-3 mb-3"><small class="text-muted d-block">Reference</small>{{ $attendanceLog->referance_type ? $attendanceLog->referance_type.' #'.$attendanceLog->referance_id : '—' }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Processing Tags</h5></div>
            <div class="card-body">
                @forelse ($attendanceLog->process_tags as $tag)
                    <span class="badge badge-light me-1">{{ $tag->name }}</span>
                @empty
                    <span class="text-muted">No processing tags.</span>
                @endforelse
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@endsection
