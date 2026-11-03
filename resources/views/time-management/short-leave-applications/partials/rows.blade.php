@forelse ($applications as $application)
    @php($approvalStatus = $application->overallApprovalStatus())
    <tr>
        <td>{{ $application->user?->name }}{{ $application->user?->code ? ' ('.$application->user->code.')' : '' }}</td>
        <td>{{ \App\Enums\ShortLeaveType::tryFrom((int) $application->short_leave_type)?->getLabel() ?? '—' }}</td>
        <td>{{ $application->date?->format('Y-m-d') ?? '—' }}</td>
        <td>{{ $application->minutes ?? '—' }}</td>
        <td>{{ $application->from_time?->format('H:i') ?? '—' }}{{ $application->to_time ? ' – '.$application->to_time->format('H:i') : '' }}</td>
        <td>{{ $application->reason ?: '—' }}</td>
        <td><span class="badge bg-{{ match ($approvalStatus) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', default => 'light text-dark'} }}">{{ ucfirst($approvalStatus) }}</span></td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'read'))
                    <a href="{{ url('time-management/short-leave-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'create') && \App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'read'))
                    <a href="{{ url('time-management/short-leave-applications/'.$application->id.'/clone') }}" class="me-2" title="Clone"><i class="ti ti-copy"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'write'))
                    <a href="{{ url('time-management/short-leave-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'delete'))
                    <form action="{{ url('time-management/short-leave-applications/'.$application->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this short leave application?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link p-0 text-danger" title="Delete"><i class="ti ti-trash"></i></button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="py-4 text-muted text-center"><i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No short leave applications found.</td></tr>
@endforelse
