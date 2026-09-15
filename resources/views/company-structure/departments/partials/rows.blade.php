@forelse ($departments as $department)
<tr>
    <td><input id="department_{{ $department->id }}" class="form-check-input" type="checkbox"></td>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $department->name }}</h6></td>
    <td>{{ $department->code }}</td>
    <td>{{ $department->email ?? '—' }}</td>
    <td>{{ $department->companies->pluck('name')->join(', ') ?: '—' }}</td>
    <td>{{ $department->locations->pluck('name')->join(', ') ?: '—' }}</td>
    <td><span class="badge {{ $department->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $department->status === 1 ? 'Active' : 'Inactive' }}</span></td>
    <td class="text-center"><div class="action-icon d-inline-flex">
        @if (\App\Support\TenantPermissions::userCan('Department', 'write'))<a class="me-2" href="{{ url('company-structure/departments/'.$department->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('Department', 'delete'))<a href="javascript:void(0)" class="text-danger delete-department-btn" data-url="{{ url('company-structure/departments/'.$department->id) }}" data-name="{{ $department->name }}" data-bs-toggle="modal" data-bs-target="#delete_department_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
    </div></td>
</tr>
@empty
<tr><td colspan="8" class="py-4 text-center text-muted">No departments found.</td></tr>
@endforelse
