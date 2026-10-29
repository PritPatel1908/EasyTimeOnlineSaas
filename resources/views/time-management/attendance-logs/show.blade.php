@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">View Attendance Log</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item"><a href="{{ url('time-management/attendance-logs') }}">Attendance Logs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log #{{ $attendanceLog->id }}</li>
                </ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($attendanceLog->trashed() && \App\Support\TenantPermissions::userCan('AttendanceLog', 'delete'))
                    <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/restore') }}">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Restore this punch?')">
                            <i class="ti ti-refresh me-1"></i>Restore Punch
                        </button>
                    </form>
                @elseif (\App\Support\TenantPermissions::userCan('AttendanceLog', 'delete') && ! $attendanceLog->is_locked)
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delete_attendance_log_modal_{{ $attendanceLog->id }}">
                        <i class="ti ti-trash me-1"></i>Delete Punch
                    </button>
                @endif
                @if (! $attendanceLog->trashed() && ! $attendanceLog->is_locked && ! $attendanceLog->is_ignored && $attendanceLog->status === 1 && \App\Support\TenantPermissions::userCan('AttendanceLog', 'write'))
                    <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/reprocess') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Reprocess this punch and recalculate attendance?')">
                            <i class="ti ti-refresh me-1"></i>Reprocess Punch
                        </button>
                    </form>
                @endif
                <a href="{{ url('time-management/attendance-logs') }}" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Punch Information</h5></div>
            <div class="card-body"><div class="row">
                <div class="col-md-6 mb-3"><small class="text-muted d-block">Employee</small><span>{{ $attendanceLog->user?->name ?? 'Unknown employee' }}{{ $attendanceLog->user_code ? ' ('.$attendanceLog->user_code.')' : '' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Log ID</small><span>#{{ $attendanceLog->id }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Punch Date &amp; Time</small><span>{{ $attendanceLog->datetime?->format('Y-m-d H:i:s') ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Direction</small><span>{{ $attendanceLog->is_out ? 'Out' : 'In' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Punch Type</small><span>{{ $attendanceLog->punch_type ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Source</small><span>{{ $attendanceLog->is_manual ? 'Manual' : 'Device' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Device</small><span>{{ $attendanceLog->machine?->name ?? $attendanceLog->dms_device_id ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Area</small><span>{{ $attendanceLog->area?->name ?? $attendanceLog->dms_area_id ?? '—' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Calculated</small><span>{{ $attendanceLog->is_calculated ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Staged</small><span>{{ $attendanceLog->is_staged ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Ignored</small><span>{{ $attendanceLog->is_ignored ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Error</small><span>{{ $attendanceLog->has_error ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Locked</small><span>{{ $attendanceLog->is_locked ? 'Yes' : 'No' }}</span></div>
                <div class="col-md-3 mb-3"><small class="text-muted d-block">Reference</small><span>{{ $attendanceLog->referance_type ? $attendanceLog->referance_type.' #'.$attendanceLog->referance_id : '—' }}</span></div>
            </div></div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Processing Tags</h5></div>
            <div class="card-body">
                @forelse ($attendanceLog->process_tags as $tag)
                    <span class="badge bg-light text-dark me-1">{{ $tag->name }}</span>
                @empty
                    <span class="text-muted">No processing tags.</span>
                @endforelse
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>
@if (! $attendanceLog->trashed() && ! $attendanceLog->is_locked && \App\Support\TenantPermissions::userCan('AttendanceLog', 'delete'))
    @include('time-management.attendance-logs.partials.delete-modal', ['attendanceLog' => $attendanceLog])
@endif
@endsection
