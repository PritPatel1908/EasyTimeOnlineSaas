<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-2">
        <h5 class="mb-0">Attendance Records — {{ $year }}</h5>
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
                        <th>Late</th>
                        <th>Early</th>
                        <th>Half Day</th>
                        <th>Absent</th>
                        <th>Overtime</th>
                        <th>Approved Overtime</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceRecords as $attendance)
                        @php
                            $needsOvertimeApproval = $attendance->user?->resolvedCategory()?->need_approval_for_overtime;
                            $rawOvertimeMinutes = (int) ($attendance->total_overtime_minutes > 0
                                ? $attendance->total_overtime_minutes
                                : $attendance->row_overtime_minutes);
                            $displayOvertimeMinutes = $rawOvertimeMinutes;
                            $displayApprovedOvertimeMinutes = (int) $attendance->approved_overtime_minutes;
                            if ($needsOvertimeApproval === false) {
                                $displayApprovedOvertimeMinutes = $attendance->is_over_time
                                    ? $rawOvertimeMinutes
                                    : 0;
                            }
                        @endphp
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
                                @if ($attendance->is_late)
                                    <span class="badge bg-warning">Yes</span>
                                    @if ($attendance->late_minutes > 0)
                                        <small class="d-block text-muted">{{ intdiv($attendance->late_minutes, 60) }}h {{ $attendance->late_minutes % 60 }}m</small>
                                    @endif
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance->is_early)
                                    <span class="badge bg-info">Yes</span>
                                    @if ($attendance->early_minutes > 0)
                                        <small class="d-block text-muted">{{ intdiv($attendance->early_minutes, 60) }}h {{ $attendance->early_minutes % 60 }}m</small>
                                    @endif
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance->is_half_day)
                                    <span class="badge bg-warning">Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance->is_absent)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance->is_over_time || $displayOvertimeMinutes > 0)
                                    <span class="badge bg-primary">Yes</span>
                                    @if ($displayOvertimeMinutes > 0)
                                        <small class="d-block text-muted">{{ intdiv($displayOvertimeMinutes, 60) }}h {{ $displayOvertimeMinutes % 60 }}m</small>
                                    @endif
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($displayApprovedOvertimeMinutes > 0)
                                    <span class="badge bg-primary">Yes</span>
                                    <small class="d-block text-muted">{{ intdiv($displayApprovedOvertimeMinutes, 60) }}h {{ $displayApprovedOvertimeMinutes % 60 }}m</small>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusLabel = 'Present';
                                    $statusClass = 'bg-success';

                                    if ($attendance->has_error) {
                                        $statusLabel = 'Error';
                                        $statusClass = 'bg-danger';
                                    } elseif ($attendance->is_absent) {
                                        $statusLabel = 'Absent';
                                        $statusClass = 'bg-danger';
                                    } elseif ($attendance->is_half_day) {
                                        $statusLabel = 'Half Day';
                                        $statusClass = 'bg-warning';
                                    } elseif ($attendance->is_holiday) {
                                        $statusLabel = 'Holiday';
                                        $statusClass = 'bg-info';
                                    } elseif ($attendance->is_weekoff) {
                                        $statusLabel = 'Week Off';
                                        $statusClass = 'bg-secondary';
                                    } elseif ($attendance->is_late && $attendance->is_early) {
                                        $statusLabel = 'Late + Early';
                                        $statusClass = 'bg-warning';
                                    } elseif ($attendance->is_late) {
                                        $statusLabel = 'Late';
                                        $statusClass = 'bg-warning';
                                    } elseif ($attendance->is_early) {
                                        $statusLabel = 'Early';
                                        $statusClass = 'bg-info';
                                    } elseif ($displayApprovedOvertimeMinutes > 0) {
                                        $statusLabel = 'Approved Overtime';
                                        $statusClass = 'bg-primary';
                                    } elseif ($attendance->is_over_time || $displayOvertimeMinutes > 0) {
                                        $statusLabel = 'Overtime';
                                        $statusClass = 'bg-primary';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $attendance->status_master?->alias ?? $statusLabel }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="14" class="text-center py-4 text-muted">No attendance records found for the selected filters.</td></tr>
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
