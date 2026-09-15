@forelse ($locations as $location)
    <tr>
        <td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
        <td><h6 class="fw-medium fs-14">{{ $location->name }}</h6></td>
        <td>{{ $location->code }}</td>
        <td>{{ $location->email ?? '—' }}</td>
        <td>{{ $location->latitude !== null && $location->longitude !== null ? $location->latitude . ', ' . $location->longitude : '—' }}</td>
        <td><span class="badge {{ $location->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i><span>{{ $location->status === 1 ? 'Active' : 'Inactive' }}</span></span></td>
        <td><div class="action-icon d-inline-flex">
            @if (\App\Support\TenantPermissions::userCan('Location', 'read'))
            <a href="{{ url('company-structure/locations/'.$location->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
            @endif
            @if (\App\Support\TenantPermissions::userCan('Location', 'write'))
            <a href="{{ url('company-structure/locations/'.$location->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
            @endif
            @if (\App\Support\TenantPermissions::userCan('Location', 'delete'))
            <a href="javascript:void(0);" class="text-danger delete-location-btn" data-id="{{ $location->id }}" data-url="{{ url('company-structure/locations/'.$location->id) }}" data-name="{{ $location->name }}" data-bs-toggle="modal" data-bs-target="#delete_location_modal" title="Delete"><i class="ti ti-trash"></i></a>
            @endif
        </div></td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ti ti-map-pin-off fs-24 d-block mb-2"></i>No locations found.</td></tr>
@endforelse
