@forelse ($punches as $punch)
    <tr>
        <td><input class="form-check-input" type="checkbox" value="{{ $punch->id }}"></td>
        <td class="text-center">{{ $punch->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
        <td class="text-center">{{ \App\Models\Tenant\ManualPunch::punchTypeLabel($punch->punch_type) }}</td>
        <td class="text-center">{{ $punch->punch_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $punch->punch_time?->format('H:i:s') ?? '—' }}</td>
        <td class="text-center">{{ $punch->reason ?: '—' }}</td>
        <td class="text-center">
            <div class="action-icon d-inline-flex">
                @if (\App\Support\TenantPermissions::userCan('ManualPunch', 'read'))
                    <a href="{{ url('time-management/manual-punches/'.$punch->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ManualPunch', 'write'))
                    <a href="{{ url('time-management/manual-punches/'.$punch->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                @endif
                @if (\App\Support\TenantPermissions::userCan('ManualPunch', 'delete'))
                    <a href="javascript:void(0);" class="text-danger delete-manual-punch-btn"
                        data-url="{{ url('time-management/manual-punches/'.$punch->id) }}"
                        data-name="{{ $punch->punch_type }} - {{ $punch->punch_date?->format('Y-m-d') }}"
                        data-bs-toggle="modal" data-bs-target="#delete_manual_punch_modal" title="Delete">
                        <i class="ti ti-trash"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="py-4 text-muted text-center"><i class="ti ti-clock-off fs-24 mb-2 d-block"></i>No manual punches found.</td></tr>
@endforelse
