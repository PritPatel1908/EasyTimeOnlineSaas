@forelse ($subDepartments as $subDepartment)
<tr>
    <td><input id="sub_department_{{ $subDepartment->id }}" class="form-check-input" type="checkbox"></td>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $subDepartment->name }}</h6></td>
    <td>{{ $subDepartment->code }}</td>
    <td>{{ $subDepartment->email ?? '—' }}</td>
    <td>{{ $subDepartment->departments->pluck('name')->join(', ') ?: '—' }}</td>
    <td>{{ $subDepartment->locations()->pluck('name')->join(', ') ?: '—' }}</td>
    <td><span class="badge {{ $subDepartment->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $subDepartment->status === 1 ? 'Active' : 'Inactive' }}</span></td>
    <td class="text-center"><div class="action-icon d-inline-flex">
        @if (\App\Support\TenantPermissions::userCan('SubDepartment', 'read'))<a class="me-2" href="{{ url('company-structure/sub-departments/'.$subDepartment->id) }}" title="View"><i class="ti ti-eye"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('SubDepartment', 'write'))<a class="me-2" href="{{ url('company-structure/sub-departments/'.$subDepartment->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('SubDepartment', 'delete'))<a href="javascript:void(0)" class="text-danger delete-sub-department-btn" data-url="{{ url('company-structure/sub-departments/'.$subDepartment->id) }}" data-name="{{ $subDepartment->name }}" data-bs-toggle="modal" data-bs-target="#delete_sub_department_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
    </div></td>
</tr>
@empty
<tr><td colspan="8" class="py-4 text-center text-muted">No sub departments found.</td></tr>
@endforelse
