@forelse ($areas as $area)
    <tr>
        <td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
        <td><h6 class="fw-medium fs-14">{{ $area->name }}</h6></td>
        <td>{{ $area->code }}</td>
        <td>{{ $area->companies->pluck('name')->join(', ') ?: '—' }}</td>
        <td>{{ $area->locations->pluck('name')->join(', ') ?: '—' }}</td>
        <td>{{ $area->time_limit_in_min ?? 0 }}</td>
        <td>{{ $area->dms_area_id ?? '—' }}</td>
        <td>
            <span class="badge {{ $area->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs">
                <i class="ti ti-point-filled me-1"></i><span>{{ $area->status === 1 ? 'Active' : 'Inactive' }}</span>
            </span>
        </td>
        <td><div class="action-icon d-inline-flex">
            @if (\App\Support\TenantPermissions::userCan('Area', 'read'))<a href="{{ url('company-structure/areas/'.$area->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>@endif
            @if (\App\Support\TenantPermissions::userCan('Area', 'write'))<a href="{{ url('company-structure/areas/'.$area->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>@endif
            @if (\App\Support\TenantPermissions::userCan('Area', 'delete'))<a href="javascript:void(0);" class="text-danger delete-area-btn" data-url="{{ url('company-structure/areas/'.$area->id) }}" data-name="{{ $area->name }}" data-bs-toggle="modal" data-bs-target="#delete_area_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="9" class="py-4 text-muted"><div class="d-flex flex-column align-items-center justify-content-center text-center"><i class="ti ti-map-off fs-24 mb-2"></i><span>No areas found.</span></div></td></tr>
@endforelse
