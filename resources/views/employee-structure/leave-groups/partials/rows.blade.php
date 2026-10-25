@forelse($leaveGroups as $leaveGroup)
<tr>
    <td><input id="leave_group_{{ $leaveGroup->id }}" class="form-check-input" type="checkbox"></td>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $leaveGroup->name }}</h6></td>
    <td>{{ $leaveGroup->financial_year?->name ?? '—' }}</td>
    <td>{{ $leaveGroup->monthly_rules_count }}</td>
    <td>{{ $leaveGroup->yearly_rules_count }}</td>
    <td><span class="badge {{ $leaveGroup->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $leaveGroup->status === 1 ? 'Active' : 'Inactive' }}</span></td>
    <td class="text-center"><div class="action-icon d-inline-flex">
        @if(\App\Support\TenantPermissions::userCan('LeaveGroup', 'read'))<a class="me-2" href="{{ url('employee-structure/leave-groups/'.$leaveGroup->id) }}" title="View"><i class="ti ti-eye"></i></a>@endif
        @if(\App\Support\TenantPermissions::userCan('LeaveGroup', 'write'))<a class="me-2" href="{{ url('employee-structure/leave-groups/'.$leaveGroup->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif
        @if(\App\Support\TenantPermissions::userCan('LeaveGroup', 'delete'))<a href="javascript:void(0)" class="text-danger delete-leave-group-btn" data-url="{{ url('employee-structure/leave-groups/'.$leaveGroup->id) }}" data-name="{{ $leaveGroup->name }}" data-bs-toggle="modal" data-bs-target="#delete_leave_group_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
    </div></td>
</tr>
@empty
<tr><td colspan="7" class="py-4 text-center text-muted">No Leave Groups found.</td></tr>
@endforelse
