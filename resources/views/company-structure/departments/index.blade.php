@extends('layout.mainlayout')
@section('content')
@php $canCreate=\App\Support\TenantPermissions::userCan('Department','create'); $canImport=\App\Support\TenantPermissions::userCan('Department','import'); $canExport=\App\Support\TenantPermissions::userCan('Department','export'); @endphp
<div class="page-wrapper"><div class="content">@include('partials.flash-alerts')
    <div id="department-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
        <i class="ti ti-circle-check me-2 alert-icon"></i>
        <span class="alert-message"></span>
        <button type="button" class="btn-close" aria-label="Close"></button>
    </div><div class="d-flex justify-content-between page-breadcrumb mb-3"><div><h2 class="mb-1">Departments</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Company Structure</li><li class="breadcrumb-item active">Departments</li></ol></nav></div><div class="d-flex align-items-center gap-2">@if($canExport)<a class="btn btn-light" href="{{ url('company-structure/departments/export') }}"><i class="ti ti-file-export me-2"></i>Export</a>@endif @if($canImport)<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_department_modal"><i class="ti ti-file-import me-2"></i>Import</button>@endif @if($canCreate)<a class="btn btn-primary" href="{{ url('company-structure/departments/create') }}"><i class="ti ti-circle-plus me-2"></i>Add Department</a>@endif</div></div><div class="card"><div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><h5>Department List</h5><div class="d-flex gap-2 flex-wrap"><div class="dropdown"><button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Location</button><ul class="dropdown-menu"><li><button class="dropdown-item department-location-filter" data-location-id="">All Locations</button></li>@foreach ($locations as $location)<li><button class="dropdown-item department-location-filter" data-location-id="{{ $location->id }}">{{ $location->name }}</button></li>@endforeach</ul></div><div class="dropdown"><button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button><ul class="dropdown-menu"><li><button class="dropdown-item department-status-filter" data-status="all">All</button></li><li><button class="dropdown-item department-status-filter" data-status="1">Active</button></li><li><button class="dropdown-item department-status-filter" data-status="0">Inactive</button></li></ul></div></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table datatable"><thead class="thead-light"><tr><th></th><th>Name</th><th>Code</th><th>Email</th><th>Companies</th><th>Locations</th><th>Status</th><th></th></tr></thead><tbody>@include('company-structure.departments.partials.rows')</tbody></table></div></div>@if($departments->hasPages())<div class="card-footer d-flex justify-content-between"><span class="text-muted">Showing {{ $departments->firstItem() }}–{{ $departments->lastItem() }} of {{ $departments->total() }} departments</span>{{ $departments->links() }}</div>@endif</div></div>@include('partials.footer')</div>
<div class="modal fade" id="delete_department_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><h4>Confirm Delete</h4><p id="delete_department_message">Are you sure you want to delete this department?</p><form id="delete_department_form" method="POST">@csrf @method('DELETE')<button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Yes, Delete</button></form></div></div></div></div>
@if ($canImport)
<div class="modal fade" id="import_department_modal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Import Departments</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="import_department_form" method="POST" action="{{ url('company-structure/departments/import') }}" enctype="multipart/form-data"
				data-import-form="true"
				data-status-alert="#department-status-alert"
				data-modal="#import_department_modal"
				data-url-match="departments"
				data-entity-label="Department">
				@csrf
				<div class="modal-body">
					<div class="mb-3">
						<label for="department_import_file" class="form-label">CSV File</label>
						<input id="department_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
						<a href="{{ url('company-structure/departments/import/sample') }}" class="d-inline-flex align-items-center mt-2">
							<i class="ti ti-download me-1"></i>Download Sample CSV
						</a>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_department_duplicates" value="1">
						<label class="form-check-label" for="update_department_duplicates">
							Update Duplicate Records
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-primary">
						<i class="ti ti-file-import me-1"></i>Import
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
@push('scripts')
@include('partials.import-poll')
<script>
    // Delete modal handling
    document.addEventListener('click', function(e) {
        var b = e.target.closest('.delete-department-btn');
        if (b) {
            document.getElementById('delete_department_form').action = b.dataset.url;
            document.getElementById('delete_department_message').textContent = 'Are you sure you want to delete "' + b.dataset.name + '"?';
        }
    });

    // Get current filter values
    function getDepartmentFilters() {
        var currentStatus = document.querySelector('.department-status-filter[data-status="1"], .department-status-filter[data-status="0"], .department-status-filter[data-status="all"]');
        var currentLocation = document.querySelector('.department-location-filter[data-location-id]');

        return {
            status: currentStatus ? currentStatus.dataset.status : 'all',
            location_id: currentLocation ? currentLocation.dataset.locationId : ''
        };
    }

    // Apply filters
    function applyDepartmentFilters(status, locationId) {
        fetch('{{ url('company-structure/departments/filter') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status, location_id: locationId || null })
        })
            .then(r => r.json())
            .then(d => {
                document.querySelector('table.datatable tbody').innerHTML = d.html;
                var countText = document.querySelector('.card-footer span.text-muted');
                if (countText) {
                    if (d.count === 0) {
                        countText.textContent = 'No departments to show';
                    } else {
                        countText.textContent = 'Showing 1–' + d.count + ' of ' + d.count + ' departments';
                    }
                }
            })
            .catch(err => console.error('Filter error:', err));
    }

    // Status filter handling
    document.querySelectorAll('.department-status-filter').forEach(function(b) {
        b.addEventListener('click', function() {
            var filters = getDepartmentFilters();
            applyDepartmentFilters(this.dataset.status, filters.location_id);
        });
    });

    // Location filter handling
    document.querySelectorAll('.department-location-filter').forEach(function(b) {
        b.addEventListener('click', function() {
            var filters = getDepartmentFilters();
            applyDepartmentFilters(filters.status, this.dataset.locationId);
        });
    });
</script>
@endpush
@endsection
