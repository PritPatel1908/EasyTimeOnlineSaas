@forelse ($leaveTypes as $leaveType)
    <tr>
        <td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
        <td><h6 class="fw-medium fs-14">{{ $leaveType->code }}</h6></td>
        <td>{{ $leaveType->description ?: '—' }}</td>
        <td>{{ $leaveType->location?->name ?? '—' }}</td>
        <td><span class="badge {{ $leaveType->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i><span>{{ $leaveType->status === 1 ? 'Active' : 'Inactive' }}</span></span></td>
        <td><div class="action-icon d-inline-flex">
            @if (\App\Support\TenantPermissions::userCan('LeaveType', 'read'))<a href="{{ url('employee-structure/leave-types/'.$leaveType->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>@endif
            @if (\App\Support\TenantPermissions::userCan('LeaveType', 'write'))<a href="{{ url('employee-structure/leave-types/'.$leaveType->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>@endif
            @if (\App\Support\TenantPermissions::userCan('LeaveType', 'delete'))<a href="javascript:void(0);" class="text-danger delete-leave-type-btn" data-url="{{ url('employee-structure/leave-types/'.$leaveType->id) }}" data-name="{{ $leaveType->code }}" data-bs-toggle="modal" data-bs-target="#delete_leave_type_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center py-4 text-muted"><i class="ti ti-calendar-off fs-24 d-block mb-2"></i>No leave types found.</td></tr>
@endforelse
