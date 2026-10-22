@forelse ($shiftChanges as $shiftChange)
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $shiftChange->id }}"></td>
        <td class="text-center">{{ $shiftChange->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
        <td class="text-center">{{ ucfirst($shiftChange->shift_type ?? '—') }}</td>
        <td class="text-center">
            @if ($shiftChange->shift_type === 'rotational')
                {{ $shiftChange->shift_rotation?->code ?? '—' }}
            @else
                {{ $shiftChange->shifts->map(fn ($shift) => $shift->name)->join(', ') ?: '—' }}
            @endif
        </td>
        <td class="text-center">{{ $shiftChange->from_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $shiftChange->is_forever ? 'No end date' : ($shiftChange->to_date?->format('Y-m-d') ?? '—') }}</td>
        <td class="text-center">{{ $shiftChange->reason ?: '—' }}</td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('ShiftChange', 'read'))
                    <a href="{{ url('time-management/manual-shift-changes/'.$shiftChange->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShiftChange', 'write'))
                    <a href="{{ url('time-management/manual-shift-changes/'.$shiftChange->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ShiftChange', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-manual-shift-change-btn"
                        data-url="{{ url('time-management/manual-shift-changes/'.$shiftChange->id) }}"
                        data-name="{{ $shiftChange->from_date?->format('Y-m-d') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_manual_shift_change_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No manual shift changes found.</td></tr>
@endforelse
