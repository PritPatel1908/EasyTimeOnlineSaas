@extends('layout.mainlayout')
@section('content')
@php
    $canCreateShift = \App\Support\TenantPermissions::userCan('Shift', 'create');
    $canImportShift = \App\Support\TenantPermissions::userCan('Shift', 'import');
    $canExportShift = \App\Support\TenantPermissions::userCan('Shift', 'export');
@endphp

<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Shifts</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Time Management</li>
                        <li class="breadcrumb-item active" aria-current="page">Shifts</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExportShift)
                    <div class="mb-2 me-2">
                        <a href="{{ url('time-management/shifts/export') }}" class="btn btn-light d-flex align-items-center">
                            <i class="ti ti-file-export me-2"></i>Export
                        </a>
                    </div>
                @endif
                @if ($canImportShift)
                    <div class="mb-2 me-2">
                        <button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_shift_modal">
                            <i class="ti ti-file-import me-2"></i>Import
                        </button>
                    </div>
                @endif
                @if ($canCreateShift)
                    <div class="mb-2">
                        <a href="{{ url('time-management/shifts/create') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Shift
                        </a>
                    </div>
                @endif
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
        </div>

        <div id="shift-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i>
            <span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Shift List</h5>
                <div class="dropdown">
                    <button type="button" class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                    <ul class="dropdown-menu">
                        <li><button type="button" class="dropdown-item shift-status-filter" data-status="all">All</button></li>
                        <li><button type="button" class="dropdown-item shift-status-filter" data-status="1">Active</button></li>
                        <li><button type="button" class="dropdown-item shift-status-filter" data-status="0">Inactive</button></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>
                                <th class="no-sort"><div class="form-check form-check-md"><input class="form-check-input datatable-select-all" type="checkbox" id="select-all" aria-label="Select all rows"></div></th>
                                <th>Shift Name</th>
                                <th>Code</th>
                                <th>Companies</th>
                                <th>Locations</th>
                                <th>Shift Time</th>
                                <th>Night Shift</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('time-management.shifts.partials.rows', ['shifts' => $shifts])
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($shifts->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $shifts->firstItem() }}–{{ $shifts->lastItem() }} of {{ $shifts->total() }} shifts</p>
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>

<div class="modal fade" id="delete_shift_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
                <h4 class="mb-1">Confirm Delete</h4>
                <p class="mb-3 text-muted" id="delete_shift_message">Are you sure you want to delete this shift?</p>
                <form id="delete_shift_form" method="POST" action="{{ url('time-management/shifts') }}">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if ($canImportShift)
    <div class="modal fade" id="import_shift_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Shifts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ url('time-management/shifts/import') }}" enctype="multipart/form-data"
                    data-import-form="true"
                    data-status-alert="#shift-status-alert"
                    data-modal="#import_shift_modal"
                    data-url-match="/time-management/shifts"
                    data-entity-label="Shift">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="shift_import_file" class="form-label">CSV File</label>
                            <input id="shift_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                            <a href="{{ url('time-management/shifts/import/sample') }}" class="d-inline-flex align-items-center mt-2">
                                <i class="ti ti-download me-1"></i>Download Sample CSV
                            </a>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_shift_duplicate_records" value="1">
                            <label class="form-check-label" for="update_shift_duplicate_records">Update Duplicate Records</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
@include('partials.import-poll')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.delete-shift-btn');
        if (!button) {
            return;
        }

        document.getElementById('delete_shift_form').action = button.dataset.url;
        document.getElementById('delete_shift_message').textContent =
            'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
    }, true);

    document.addEventListener('click', function (event) {
        var filterButton = event.target.closest('.shift-status-filter');
        if (!filterButton || filterButton.disabled) {
            return;
        }

        event.preventDefault();
        filterButton.disabled = true;
        var alert = document.getElementById('shift-status-alert');

        fetch('{{ url('time-management/shifts/filter') }}', {
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
                        throw new Error(data.message || 'Unable to filter shifts.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                var sourceBody = document.querySelector('table.datatable tbody');
                sourceBody.innerHTML = data.html;
                var visibleBody = document.querySelector('.gridjs-tbody');
                if (visibleBody) {
                    visibleBody.innerHTML = '';
                    sourceBody.querySelectorAll('tr').forEach(function (row) {
                        row.classList.add('gridjs-tr');
                        row.querySelectorAll('td').forEach(function (cell) {
                            cell.classList.add('gridjs-td');
                        });
                        visibleBody.appendChild(row.cloneNode(true));
                    });
                }
                if (typeof window.importPollShowAlert === 'function') {
                    window.importPollShowAlert(alert, 'success', 'Shift status filter applied successfully.');
                } else {
                    alert.className = 'alert alert-success alert-dismissible d-flex align-items-center';
                    alert.querySelector('.alert-message').textContent = 'Shift status filter applied successfully.';
                    alert.classList.remove('d-none');
                }
            })
            .catch(function (error) {
                if (typeof window.importPollShowAlert === 'function') {
                    window.importPollShowAlert(alert, 'danger', error.message);
                } else {
                    alert.className = 'alert alert-danger alert-dismissible d-flex align-items-center';
                    alert.querySelector('.alert-message').textContent = error.message;
                    alert.classList.remove('d-none');
                }
            })
            .finally(function () {
                filterButton.disabled = false;
            });
    }, true);
</script>
@endpush
@endsection
