@forelse ($applications as $application)
    @php
        $cancellationStatus = $cancellationStatuses->get($application->id);
        $status = match ($cancellationStatus) {
            'pending' => 'cancellation_pending',
            'failed' => 'cancellation_failed',
            'completed' => 'cancelled',
            default => $application->overallApprovalStatus(),
        };
        $statusLabel = match ($status) {
            'cancellation_pending' => 'Cancellation Pending',
            'cancellation_failed' => 'Cancellation Failed',
            default => ucfirst($status),
        };
        $statusClass = match ($status) {
            'pending' => 'primary',
            'approved' => 'success',
            'rejected', 'cancellation_failed' => 'danger',
            'cancelled' => 'secondary',
            'cancellation_pending' => 'info',
            'draft' => 'secondary',
            default => 'light text-dark',
        };
    @endphp
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $application->id }}"></td>
        <td class="text-center">{{ $application->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
        <td class="text-center">{{ ucfirst($application->shift_type ?? '—') }}</td>
        <td class="text-center">
            @if ($application->shift_type === 'rotational')
                {{ $application->shift_rotation?->code ?? '—' }}
            @else
                {{ $application->shifts->map(fn ($shift) => $shift->name)->join(', ') ?: '—' }}
            @endif
        </td>
        <td class="text-center">{{ $application->from_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $application->is_forever ? 'No end date' : ($application->to_date?->format('Y-m-d') ?? '—') }}</td>
        <td class="text-center"><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td>
        <td class="text-center">{{ $application->reason ?: '—' }}</td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('ShiftChangeApplication', 'read'))
                    <a href="{{ url('time-management/shift-change-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShiftChangeApplication', 'write'))
                    <a href="{{ url('time-management/shift-change-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShiftChangeApplication', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-shift-change-application-btn"
                        data-url="{{ url('time-management/shift-change-applications/'.$application->id) }}"
                        data-name="{{ $application->from_date?->format('Y-m-d') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_shift_change_application_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="9" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No shift change applications found.</td></tr>
@endforelse
