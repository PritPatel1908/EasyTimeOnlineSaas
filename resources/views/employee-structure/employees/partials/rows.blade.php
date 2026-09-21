@forelse($employees as $employee)
<tr>
    <td><input id="employee_{{ $employee->id }}" class="form-check-input" type="checkbox"></td>
    <td><a href="{{ url('employee-structure/employees/'.$employee->id) }}" class="fw-medium fs-14">{{ $employee->name }}</a><small class="d-block text-muted">{{ $employee->code }}</small></td>
    <td>{{ $employee->email ?: '-' }}</td>
    <td>{{ $employee->company?->name ?: '-' }}</td>
    <td>{{ $employee->department?->name ?: '-' }}</td>
    <td><span class="badge {{ $employee->status ? 'badge-success' : 'badge-danger' }}"><i class="ti ti-point-filled"></i>{{ $employee->status ? 'Active' : 'Inactive' }}</span><small class="d-block text-muted">Mobile login: {{ $employee->allow_mobile_login ? 'Allowed' : 'Denied' }} | punch: {{ $employee->allow_mobile_punch ? 'Allowed' : 'Denied' }}</small></td>
    <td class="text-center"><div class="action-icon d-inline-flex">@if(\App\Support\TenantPermissions::userCan('User', 'read'))<a class="me-2" href="{{ url('employee-structure/employees/'.$employee->id) }}" title="View"><i class="ti ti-eye"></i></a>@endif @if(\App\Support\TenantPermissions::userCan('User', 'write'))<a class="me-2" href="{{ url('employee-structure/employees/'.$employee->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif @if(\App\Support\TenantPermissions::userCan('User', 'delete'))<a href="javascript:void(0)" class="text-danger delete-employee-btn" data-url="{{ url('employee-structure/employees/'.$employee->id) }}" data-name="{{ $employee->name }}" data-bs-toggle="modal" data-bs-target="#delete_employee_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif</div></td>
</tr>
@empty
<tr><td colspan="7" class="py-4 text-center text-muted">No employees found.</td></tr>
@endforelse
