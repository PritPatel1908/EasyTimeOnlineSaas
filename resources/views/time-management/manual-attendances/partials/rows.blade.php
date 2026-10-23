@forelse ($attendances as $attendance)
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $attendance->id }}"></td>
        <td class="text-center">{{ $attendance->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
        <td class="text-center">{{ $attendance->in_time?->format('Y-m-d H:i:s') ?? '—' }}</td>
        <td class="text-center">{{ $attendance->out_time?->format('Y-m-d H:i:s') ?? '—' }}</td>
        <td class="text-center">{{ $attendance->reason ?: '—' }}</td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('ManualAttendance', 'read'))
                    <a href="{{ url('time-management/manual-attendances/'.$attendance->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ManualAttendance', 'write'))
                    <a href="{{ url('time-management/manual-attendances/'.$attendance->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ManualAttendance', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-manual-attendance-btn"
                        data-url="{{ url('time-management/manual-attendances/'.$attendance->id) }}"
                        data-name="{{ $attendance->in_time?->format('Y-m-d H:i') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_manual_attendance_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="py-4 text-muted text-center"><i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No manual attendances found.</td></tr>
@endforelse
