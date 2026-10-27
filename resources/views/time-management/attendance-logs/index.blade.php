@extends('layout.mainlayout')
@section('content')
@php
    $canReprocess = \App\Support\TenantPermissions::userCan('AttendanceLog', 'write');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Attendance Logs</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Attendance Logs</li>
                </ol></nav>
            </div>
            @if ($canReprocess)
                <div class="mb-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reprocess_attendance_logs_modal">
                        <i class="ti ti-refresh me-2"></i>Reprocess
                    </button>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Punch Log List</h5>
            </div>
            <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th>Log ID</th><th>Employee</th><th>Punch Date &amp; Time</th><th>Direction</th><th>Source</th><th>Processing Status</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($attendanceLogs as $attendanceLog)
                            <tr>
                                <td>#{{ $attendanceLog->id }}</td>
                                <td>
                                    {{ $attendanceLog->user?->name ?? 'Unknown employee' }}
                                    <small class="d-block text-muted">{{ $attendanceLog->user_code ?: '—' }}</small>
                                </td>
                                <td>{{ $attendanceLog->datetime?->format('Y-m-d H:i:s') ?? '—' }}</td>
                                <td>{{ $attendanceLog->is_out ? 'Out' : 'In' }}</td>
                                <td>{{ $attendanceLog->is_manual ? 'Manual' : 'Device' }}</td>
                                <td>
                                    @if ($attendanceLog->is_locked)
                                        <span class="badge bg-secondary">Locked</span>
                                    @elseif ($attendanceLog->is_ignored)
                                        <span class="badge bg-warning">Ignored</span>
                                    @elseif ($attendanceLog->has_error)
                                        <span class="badge bg-danger">Error</span>
                                    @elseif ($attendanceLog->is_staged)
                                        <span class="badge bg-warning">Staged</span>
                                    @elseif ($attendanceLog->is_calculated)
                                        <span class="badge bg-success">Processed</span>
                                    @else
                                        <span class="badge bg-light text-dark">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ url('time-management/attendance-logs/'.$attendanceLog->id) }}" class="btn btn-sm btn-light">
                                        <i class="ti ti-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No attendance logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
            @if ($attendanceLogs->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $attendanceLogs->firstItem() }}–{{ $attendanceLogs->lastItem() }} of {{ $attendanceLogs->total() }} attendance logs</p>
                    {{ $attendanceLogs->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>

@if ($canReprocess)
    @php
        $selectedEmployeeIds = old('employee_ids', []);
        $selectedEmployeeIds = is_array($selectedEmployeeIds) ? array_map('strval', $selectedEmployeeIds) : [];
    @endphp
    <div class="modal fade" id="reprocess_attendance_logs_modal" tabindex="-1" aria-labelledby="reprocess_attendance_logs_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reprocess_attendance_logs_title">Reprocess Attendance Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ url('time-management/attendance-logs/reprocess-range') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="attendance_logs_start_datetime" class="form-label">Start Date &amp; Time</label>
                                <input id="attendance_logs_start_datetime" name="start_datetime" type="datetime-local" class="form-control" value="{{ old('start_datetime') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="attendance_logs_end_datetime" class="form-label">End Date &amp; Time</label>
                                <input id="attendance_logs_end_datetime" name="end_datetime" type="datetime-local" class="form-control" value="{{ old('end_datetime') }}" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="attendance_logs_employee_ids" class="form-label">Employees</label>
                                <select id="attendance_logs_employee_ids" name="employee_ids[]" class="form-control select2" multiple>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(in_array((string) $employee->id, $selectedEmployeeIds, true))>
                                            {{ $employee->name }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave this empty to reprocess punches for all employees in the selected date and time range.</small>
                            </div>
                        </div>
                        <div class="alert alert-warning mb-0" role="alert">
                            Matching unlocked attendance and In/Out Muster records will be removed and recalculated from the selected punches.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-refresh me-1"></i>Start Reprocessing</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
