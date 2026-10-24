@forelse ($applications as $application)
    @php($status = $application->overallApprovalStatus())
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $application->id }}"></td>
        <td class="text-center">{{ $application->Users->map(fn ($user) => $user->name.($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
        <td class="text-center">{{ $application->week_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $application->swap_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $application->reason ?: '—' }}</td>
        <td class="text-center"><span class="badge bg-{{ match ($status) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', default => 'light text-dark'} }}">{{ ucfirst($status) }}</span></td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('WeekOffSwapApplication', 'read'))
                    <a href="{{ url('time-management/week-off-swap-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('WeekOffSwapApplication', 'write'))
                    <a href="{{ url('time-management/week-off-swap-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('WeekOffSwapApplication', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-week-off-swap-application-btn"
                        data-url="{{ url('time-management/week-off-swap-applications/'.$application->id) }}"
                        data-name="{{ $application->week_date?->format('Y-m-d') }} → {{ $application->swap_date?->format('Y-m-d') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_week_off_swap_application_modal" title="Delete"><i class="ti ti-trash"></i></a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No week-off swap applications found.</td></tr>
@endforelse
