@forelse ($applications as $application)
    @php($status = $application->overallApprovalStatus())
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $application->id }}"></td>
        <td class="text-center">{{ $application->user?->name }}{{ $application->user?->code ? ' ('.$application->user->code.')' : '' }}</td>
        <td class="text-center">{{ $application->date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $application->overtime_minutes }}</td>
        <td class="text-center">{{ $application->reason ?: '—' }}</td>
        <td class="text-center"><span class="badge bg-{{ match ($status) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', default => 'light text-dark'} }}">{{ ucfirst($status) }}</span></td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('OvertimeApplication', 'read'))
                    <a href="{{ url('time-management/overtime-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('OvertimeApplication', 'write'))
                    <a href="{{ url('time-management/overtime-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('OvertimeApplication', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-overtime-application-btn"
                        data-url="{{ url('time-management/overtime-applications/'.$application->id) }}"
                        data-name="{{ ($application->user?->name ?? 'Employee').' - '.$application->date?->format('Y-m-d') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_overtime_application_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="py-4 text-muted text-center"><i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No overtime applications found.</td></tr>
@endforelse
