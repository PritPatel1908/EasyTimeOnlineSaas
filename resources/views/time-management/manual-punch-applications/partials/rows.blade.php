@forelse ($applications as $application)
    @php($status = $application->overallApprovalStatus())
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $application->id }}"></td>
        <td class="text-center">{{ $application->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
        <td class="text-center">{{ \App\Models\Tenant\ManualPunch::punchTypeLabel($application->punch_type) }}</td>
        <td class="text-center">{{ $application->punch_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $application->punch_time?->format('H:i:s') ?? '—' }}</td>
        <td class="text-center">{{ $application->reason ?: '—' }}</td>
        <td class="text-center"><span class="badge bg-{{ match ($status) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', default => 'light text-dark'} }}">{{ ucfirst($status) }}</span></td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('ManualPunchApplication', 'read'))
                    <a href="{{ url('time-management/manual-punch-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ManualPunchApplication', 'write'))
                    <a href="{{ url('time-management/manual-punch-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ManualPunchApplication', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-manual-punch-application-btn"
                        data-url="{{ url('time-management/manual-punch-applications/'.$application->id) }}"
                        data-name="{{ \App\Models\Tenant\ManualPunch::punchTypeLabel($application->punch_type) }} - {{ $application->punch_date?->format('Y-m-d') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_manual_punch_application_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="py-4 text-muted text-center"><i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No manual punch applications found.</td></tr>
@endforelse
