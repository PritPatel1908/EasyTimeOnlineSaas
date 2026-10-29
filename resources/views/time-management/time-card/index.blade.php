@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Time Card</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Time Card</li>
                </ol></nav>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Attendance</h5>
            </div>
            <div class="card-body">
                <form id="time_card_filter_form" method="GET" action="{{ url('time-management/time-card') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label for="time_card_year" class="form-label">Year</label>
                            <select id="time_card_year" name="year" class="form-select">
                                @foreach ($years as $availableYear)
                                    <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="time_card_employee" class="form-label">Employee</label>
                            <select id="time_card_employee" name="employee_id" class="form-select">
                                <option value="">All Employees</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected($employeeId === (string) $employee->id)>
                                        {{ $employee->name }}{{ $employee->code ? ' ('.$employee->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="time_card_start_date" class="form-label">From Date</label>
                            <input id="time_card_start_date" name="start_date" type="date" class="form-control"
                                min="{{ $year }}-01-01" max="{{ $year }}-12-31" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="time_card_end_date" class="form-label">To Date</label>
                            <input id="time_card_end_date" name="end_date" type="date" class="form-control"
                                min="{{ $year }}-01-01" max="{{ $year }}-12-31" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2 mb-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i>Apply</button>
                            <a href="{{ url('time-management/time-card') }}" class="btn btn-light">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                <h5 class="mb-0">Attendance Records — {{ $year }}</h5>
                <span class="text-muted fs-13">{{ $attendanceRecords->total() }} records</span>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>
                                <th>Attendance ID</th>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Work Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendanceRecords as $attendance)
                                <tr>
                                    <td>#{{ $attendance->id }}</td>
                                    <td>
                                        {{ $attendance->user?->name ?? 'Unknown employee' }}
                                        <small class="d-block text-muted">{{ $attendance->user?->code ?: '—' }}</small>
                                    </td>
                                    <td>{{ $attendance->date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $attendance->shift_code ?? $attendance->shift?->name ?? '—' }}</td>
                                    <td>{{ $attendance->in_time?->format('H:i:s') ?? '—' }}</td>
                                    <td>{{ $attendance->out_time?->format('H:i:s') ?? '—' }}</td>
                                    <td>{{ intdiv($attendance->work_time, 60) }}h {{ $attendance->work_time % 60 }}m</td>
                                    <td>
                                        @if ($attendance->has_error)
                                            <span class="badge bg-danger">Error</span>
                                        @elseif ($attendance->is_absent)
                                            <span class="badge bg-danger">Absent</span>
                                        @elseif ($attendance->is_half_day)
                                            <span class="badge bg-warning">Half Day</span>
                                        @elseif ($attendance->is_holiday)
                                            <span class="badge bg-info">Holiday</span>
                                        @elseif ($attendance->is_weekoff)
                                            <span class="badge bg-secondary">Week Off</span>
                                        @elseif ($attendance->is_late)
                                            <span class="badge bg-warning">Late</span>
                                        @else
                                            <span class="badge bg-success">Present</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">No attendance records found for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($attendanceRecords->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $attendanceRecords->firstItem() }}–{{ $attendanceRecords->lastItem() }} of {{ $attendanceRecords->total() }} records</p>
                    {{ $attendanceRecords->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
@push('scripts')
<script>
    document.getElementById('time_card_year').addEventListener('change', function () {
        document.getElementById('time_card_start_date').value = '';
        document.getElementById('time_card_end_date').value = '';
        document.getElementById('time_card_filter_form').submit();
    });
</script>
@endpush
@endsection
