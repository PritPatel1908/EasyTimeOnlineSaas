@forelse ($units as $unit)
<tr>
    <td><input id="unit_{{ $unit->id }}" class="form-check-input" type="checkbox"></td>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $unit->name }}</h6></td>
    <td>{{ $unit->code }}</td>
    <td>{{ $unit->locations->pluck('name')->join(', ') ?: '—' }}</td>
    <td>
        @if (\App\Support\TenantPermissions::userCan('Unit', 'write'))
            <button type="button" class="btn btn-link p-0 unit-status-btn" data-url="{{ url('company-structure/units/'.$unit->id.'/status') }}" data-status="{{ $unit->status === 1 ? 0 : 1 }}" title="Change status">
                <span class="badge {{ $unit->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $unit->status === 1 ? 'Active' : 'Inactive' }}</span>
            </button>
        @else
            <span class="badge {{ $unit->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $unit->status === 1 ? 'Active' : 'Inactive' }}</span>
        @endif
    </td>
    <td class="text-center"><div class="action-icon d-inline-flex">
        @if (\App\Support\TenantPermissions::userCan('Unit', 'read'))<a class="me-2" href="{{ url('company-structure/units/'.$unit->id) }}" title="View"><i class="ti ti-eye"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('Unit', 'write'))<a class="me-2" href="{{ url('company-structure/units/'.$unit->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('Unit', 'delete'))<a href="javascript:void(0)" class="text-danger delete-unit-btn" data-url="{{ url('company-structure/units/'.$unit->id) }}" data-name="{{ $unit->name }}" data-bs-toggle="modal" data-bs-target="#delete_unit_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
    </div></td>
</tr>
@empty
<tr><td colspan="6" class="py-4 text-center text-muted">No units found.</td></tr>
@endforelse