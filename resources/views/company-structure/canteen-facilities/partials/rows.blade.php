@forelse ($canteenFacilities as $canteenFacility)
	<tr>
		<td><div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div></td>
		<td><h6 class="fw-medium fs-14">{{ $canteenFacility->name }}</h6></td>
		<td>{{ $canteenFacility->code }}</td>
		<td>{{ number_format((float) $canteenFacility->total_cfa, 2) }}</td>
		<td>{{ $canteenFacility->location?->name ?? '—' }}</td>
		<td><span class="badge {{ $canteenFacility->status === 1 ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i><span>{{ $canteenFacility->status === 1 ? 'Active' : 'Inactive' }}</span></span></td>
		<td><div class="action-icon d-inline-flex">
			@if (\App\Support\TenantPermissions::userCan('CanteenFacility', 'read'))
			<a href="{{ url('company-structure/canteen-facilities/'.$canteenFacility->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
			@endif
			@if (\App\Support\TenantPermissions::userCan('CanteenFacility', 'write'))
			<a href="{{ url('company-structure/canteen-facilities/'.$canteenFacility->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
			@endif
			@if (\App\Support\TenantPermissions::userCan('CanteenFacility', 'delete'))
			<a href="javascript:void(0);" class="text-danger delete-canteen-facility-btn" data-url="{{ url('company-structure/canteen-facilities/'.$canteenFacility->id) }}" data-name="{{ $canteenFacility->name }}" data-bs-toggle="modal" data-bs-target="#delete_canteen_facility_modal" title="Delete"><i class="ti ti-trash"></i></a>
			@endif
		</div></td>
	</tr>
@empty
	<tr><td colspan="7" class="text-center py-4 text-muted"><i class="ti ti-building-store-off fs-24 d-block mb-2"></i>No canteen facilities found.</td></tr>
@endforelse
