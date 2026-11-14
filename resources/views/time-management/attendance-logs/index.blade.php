@extends('layout.mainlayout')

@section('content')
@php
    $canReprocess = \App\Support\TenantPermissions::userCan('AttendanceLog', 'write');
    $canDelete = \App\Support\TenantPermissions::userCan('AttendanceLog', 'delete');
    $selectedEmployeeIds = old('employee_ids', []);
    $selectedEmployeeIds = is_array($selectedEmployeeIds) ? array_map('strval', $selectedEmployeeIds) : [];
@endphp

<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">Attendance Logs</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">Time Management</li>
                        <li class="breadcrumb-item active">Attendance Logs</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($canDelete)
                    <a href="{{ request()->fullUrlWithQuery(['deleted' => $showDeleted ? 0 : 1]) }}" class="btn btn-light">
                        <i class="ti ti-trash me-2"></i>{{ $showDeleted ? 'Active Punches' : 'Deleted Punches' }}
                    </a>
                    @if ($showDeleted)
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#restore_attendance_logs_modal">
                            <i class="ti ti-refresh me-2"></i>Restore
                        </button>
                    @else
                        <button type="button" class="btn btn-light text-danger" data-bs-toggle="modal" data-bs-target="#delete_attendance_logs_modal">
                            <i class="ti ti-trash me-2"></i>Delete
                        </button>
                    @endif
                @endif
                @if ($canReprocess && ! $showDeleted)
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#reprocess_attendance_logs_modal">
                        <i class="ti ti-refresh me-2"></i>Reprocess
                    </button>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Attendance Log List</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>
                                <th>Log ID</th>
                                <th>Employee</th>
                                <th>Punch Date &amp; Time</th>
                                <th>Direction</th>
                                <th>Source</th>
                                <th>Processing Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('time-management.attendance-logs.partials.rows')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('partials.footer')
</div>

@if ($canDelete && $showDeleted)
    <div class="modal fade" id="restore_attendance_logs_modal" tabindex="-1" aria-labelledby="restore_attendance_logs_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restore_attendance_logs_title">Restore Attendance Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ url('time-management/attendance-logs/restore-range') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="restore_attendance_logs_start_datetime" class="form-label">From Date &amp; Time</label>
                                <input id="restore_attendance_logs_start_datetime" name="start_datetime" type="datetime-local" class="form-control" value="{{ old('start_datetime') }}" required>
                                @error('start_datetime')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="restore_attendance_logs_end_datetime" class="form-label">To Date &amp; Time</label>
                                <input id="restore_attendance_logs_end_datetime" name="end_datetime" type="datetime-local" class="form-control" value="{{ old('end_datetime') }}" required>
                                @error('end_datetime')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label for="restore_attendance_logs_employee_ids" class="form-label">Employees</label>
                                <select id="restore_attendance_logs_employee_ids" name="employee_ids[]" class="form-control select2" multiple>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(in_array((string) $employee->id, $selectedEmployeeIds, true))>
                                            {{ $employee->name }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">Leave empty to restore matching deleted punches for all employees.</small>
                            </div>
                        </div>
                        <div class="alert alert-info mb-0" role="alert">Matching deleted punches will be restored as active and recalculated.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="ti ti-refresh me-1"></i>Restore Punches</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if ($canDelete && ! $showDeleted)
    <div class="modal fade" id="delete_attendance_logs_modal" tabindex="-1" aria-labelledby="delete_attendance_logs_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_attendance_logs_title">Delete Attendance Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ url('time-management/attendance-logs/delete-range') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="delete_attendance_logs_start_datetime" class="form-label">From Date &amp; Time</label>
                                <input id="delete_attendance_logs_start_datetime" name="start_datetime" type="datetime-local" class="form-control" value="{{ old('start_datetime') }}" required>
                                @error('start_datetime')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="delete_attendance_logs_end_datetime" class="form-label">To Date &amp; Time</label>
                                <input id="delete_attendance_logs_end_datetime" name="end_datetime" type="datetime-local" class="form-control" value="{{ old('end_datetime') }}" required>
                                @error('end_datetime')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label for="delete_attendance_logs_employee_ids" class="form-label">Employees</label>
                                <select id="delete_attendance_logs_employee_ids" name="employee_ids[]" class="form-control select2" multiple>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(in_array((string) $employee->id, $selectedEmployeeIds, true))>
                                            {{ $employee->name }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">Leave empty to delete matching punches for all employees.</small>
                            </div>
                        </div>
                        <div class="alert alert-warning mb-0" role="alert">Only unlocked punches in the selected range will be deleted.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Delete Punches</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if ($canReprocess && ! $showDeleted)
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
                                <label for="reprocess_attendance_logs_start_datetime" class="form-label">From Date &amp; Time</label>
                                <input id="reprocess_attendance_logs_start_datetime" name="start_datetime" type="datetime-local" class="form-control" value="{{ old('start_datetime') }}" required>
                                @error('start_datetime')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reprocess_attendance_logs_end_datetime" class="form-label">To Date &amp; Time</label>
                                <input id="reprocess_attendance_logs_end_datetime" name="end_datetime" type="datetime-local" class="form-control" value="{{ old('end_datetime') }}" required>
                                @error('end_datetime')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label for="reprocess_attendance_logs_employee_ids" class="form-label">Employees</label>
                                <select id="reprocess_attendance_logs_employee_ids" name="employee_ids[]" class="form-control select2" multiple>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(in_array((string) $employee->id, $selectedEmployeeIds, true))>
                                            {{ $employee->name }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">Leave empty to reprocess punches for all employees.</small>
                            </div>
                        </div>
                        <div class="alert alert-warning mb-0" role="alert">Matching attendance will be recalculated from punches in the selected range.</div>
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
