@forelse ($shiftRotations as $shiftRotation)
    <tr>
        <td><input id="shift_rotation_{{ $shiftRotation->id }}" class="form-check-input" type="checkbox"></td>
        <td class="text-center"><h6 class="fw-medium fs-14">{{ $shiftRotation->code }}</h6></td>
        <td class="text-center">{{ $shiftRotation->start_date?->format('Y-m-d') ?? '—' }}</td>
        <td class="text-center">{{ $shiftRotation->is_forever ? 'Forever' : ($shiftRotation->end_date?->format('Y-m-d') ?? '—') }}</td>
        <td class="text-center">{{ $shiftRotation->rotation_values_count }}</td>
        <td class="text-center">{{ $shiftRotation->skip_days_on_month_end ? 'Yes' : 'No' }}</td>
        <td class="text-center">{{ $shiftRotation->skip_days_on_year_end ? 'Yes' : 'No' }}</td>
        <td class="text-center">
            <span class="badge {{ (int) $shiftRotation->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i>{{ (int) $shiftRotation->status === 1 ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="text-center"><div class="action-icon d-inline-flex">
            @if (\App\Support\TenantPermissions::userCan('ShiftRotation', 'read'))
                <a href="{{ url('time-management/shift-rotations/'.$shiftRotation->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
            @endif
            @if (\App\Support\TenantPermissions::userCan('ShiftRotation', 'write'))
                <a href="{{ url('time-management/shift-rotations/'.$shiftRotation->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
            @endif
            @if (\App\Support\TenantPermissions::userCan('ShiftRotation', 'delete'))
                <a href="javascript:void(0);" class="text-danger delete-shift-rotation-btn"
                    data-url="{{ url('time-management/shift-rotations/'.$shiftRotation->id) }}"
                    data-name="{{ $shiftRotation->code }}" data-bs-toggle="modal"
                    data-bs-target="#delete_shift_rotation_modal" title="Delete"><i class="ti ti-trash"></i></a>
            @endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="9" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No shift rotations found.</td></tr>
@endforelse
