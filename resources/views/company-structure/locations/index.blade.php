@extends('layout.mainlayout')
@section('content')
@php
    $canCreateLocation = \App\Support\TenantPermissions::userCan('Location', 'create');
    $canImportLocation = \App\Support\TenantPermissions::userCan('Location', 'import');
    $canExportLocation = \App\Support\TenantPermissions::userCan('Location', 'export');
@endphp
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3"><div class="my-auto mb-2"><h2 class="mb-1">Locations</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Company Structure</li><li class="breadcrumb-item active">Locations</li></ol></nav></div><div class="d-flex my-xl-auto right-content align-items-center flex-wrap">@if ($canExportLocation)<div class="mb-2 me-2"><a href="{{ url('company-structure/locations/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>@endif @if ($canImportLocation)<div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_location_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>@endif @if ($canCreateLocation)<div class="mb-2"><a href="{{ url('company-structure/locations/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Location</a></div>@endif</div></div>
    <div id="location-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
        <i class="ti ti-circle-check me-2 alert-icon"></i>
        <span class="alert-message"></span>
        <button type="button" class="btn-close" aria-label="Close"></button>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <h5>Location List</h5>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <div class="dropdown me-3">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Status</a>
                    <ul class="dropdown-menu dropdown-menu-end p-3">
                        <li><button type="button" class="dropdown-item rounded-1 location-status-filter" data-status="all">All</button></li>
                        <li><button type="button" class="dropdown-item rounded-1 location-status-filter" data-status="1">Active</button></li>
                        <li><button type="button" class="dropdown-item rounded-1 location-status-filter" data-status="0">Inactive</button></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="custom-datatable-filter table-responsive">
                <table class="table datatable">
                    <thead class="thead-light">
                        <tr>
                            <th class="no-sort"><div class="form-check form-check-md"><input class="form-check-input" type="checkbox" id="select-all"></div></th>
                            <th>Location Name</th>
                            <th>Code</th>
                            <th>Email</th>
                            <th>Coordinates</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>@include('company-structure.locations.partials.rows', ['locations' => $locations])</tbody>
                </table>
            </div>
        </div>
        @if ($locations->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                <p class="mb-0 text-muted fs-13">Showing {{ $locations->firstItem() }}–{{ $locations->lastItem() }} of {{ $locations->total() }} locations</p>
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</div>@include('partials.footer')</div>
<div class="modal fade" id="delete_location_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span><h4 class="mb-1">Confirm Delete</h4><p class="mb-3 text-muted" id="delete_location_message">Are you sure you want to delete this location?</p><form id="delete_location_form" method="POST">@csrf @method('DELETE')<button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button></form></div></div></div></div>
@if ($canImportLocation)<div class="modal fade" id="import_location_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Import Locations</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('company-structure/locations/import') }}" enctype="multipart/form-data" data-import-form="true" data-status-alert="#location-status-alert" data-modal="#import_location_modal" data-url-match="locations" data-entity-label="Location">@csrf<div class="modal-body"><label for="location_import_file" class="form-label">CSV File</label><input id="location_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required><a href="{{ url('company-structure/locations/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_location_duplicates" value="1"><label class="form-check-label" for="update_location_duplicates">Update Duplicate Records</label></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div></form></div></div></div>@endif
@push('scripts')
@include('partials.import-poll')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.delete-location-btn');

        if (!button) {
            return;
        }

        document.getElementById('delete_location_form').action = button.dataset.url;
        document.getElementById('delete_location_message').textContent =
            'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
    }, true);

    document.addEventListener('click', function (event) {
        var filterButton = event.target.closest('.location-status-filter');

        if (!filterButton || filterButton.disabled) {
            return;
        }

        event.preventDefault();

        var alert = document.getElementById('location-status-alert');
        filterButton.disabled = true;

        fetch('{{ url('company-structure/locations/filter') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: filterButton.dataset.status })
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to filter locations.');
                    }

                    return data;
                });
            })
            .then(function (data) {
                var table = document.querySelector('table.datatable');
                var sourceBody = table.querySelector('tbody');
                var rowContainer = document.createElement('tbody');
                var visibleBody = document.querySelector('.gridjs-tbody');

                rowContainer.innerHTML = data.html;
                sourceBody.innerHTML = data.html;

                if (visibleBody) {
                    visibleBody.innerHTML = '';
                    rowContainer.querySelectorAll('tr').forEach(function (row) {
                        row.classList.add('gridjs-tr');
                        row.querySelectorAll('td').forEach(function (cell) {
                            cell.classList.add('gridjs-td');
                        });
                        visibleBody.appendChild(row);
                    });
                }

                var summary = document.querySelector('.gridjs-summary');
                if (summary) {
                    summary.textContent = data.count === 0
                        ? 'No records to show'
                        : 'Showing 1-' + data.count + ' of ' + data.count + ' entries';
                }

                var pages = document.querySelector('.gridjs-pages');
                if (pages) {
                    pages.innerHTML = '';
                }

                if (typeof window.importPollShowAlert === 'function') {
                    window.importPollShowAlert(alert, 'success', 'Location status filter applied successfully.');
                }
            })
            .catch(function (error) {
                if (typeof window.importPollShowAlert === 'function') {
                    window.importPollShowAlert(alert, 'danger', error.message);
                }
            })
            .finally(function () {
                filterButton.disabled = false;
            });
    }, true);
</script>
@endpush
@endsection
