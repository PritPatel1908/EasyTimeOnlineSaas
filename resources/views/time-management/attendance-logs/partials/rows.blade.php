@forelse ($attendanceLogs as $attendanceLog)
    <tr>
        <td>#{{ $attendanceLog->id }}</td>
        <td>
            <h6 class="fw-medium fs-14 mb-0">{{ $attendanceLog->user?->name ?? 'Unknown employee' }}</h6>
            <small class="d-block text-muted">{{ $attendanceLog->user_code ?: '—' }}</small>
        </td>
        <td>{{ $attendanceLog->datetime?->format('Y-m-d H:i:s') ?? '—' }}</td>
        <td>{{ $attendanceLog->is_out ? 'Out' : 'In' }}</td>
        <td>{{ $attendanceLog->is_manual ? 'Manual' : 'Device' }}</td>
        <td>
            @if ($attendanceLog->trashed())
                <span class="badge badge-danger">Deleted</span>
            @elseif ($attendanceLog->is_locked)
                <span class="badge badge-secondary">Locked</span>
            @elseif ($attendanceLog->is_ignored)
                <span class="badge badge-warning">Ignored</span>
            @elseif ($attendanceLog->has_error)
                <span class="badge badge-danger">Error</span>
            @elseif ($attendanceLog->is_staged)
                <span class="badge badge-warning">Staged</span>
            @elseif ($attendanceLog->is_calculated)
                <span class="badge badge-success">Processed</span>
            @else
                <span class="badge badge-light">Pending</span>
            @endif
        </td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                <a class="me-2" href="{{ url('time-management/attendance-logs/'.$attendanceLog->id) }}" title="View">
                    <i class="ti ti-eye"></i>
                </a>
                @if ($showDeleted && \App\Support\TenantPermissions::userCan('AttendanceLog', 'delete'))
                    <form method="POST" action="{{ url('time-management/attendance-logs/'.$attendanceLog->id.'/restore') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-success p-0" title="Restore" aria-label="Restore punch">
                            <i class="ti ti-refresh"></i>
                        </button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="py-4 text-center text-muted">No attendance logs found.</td></tr>
@endforelse
