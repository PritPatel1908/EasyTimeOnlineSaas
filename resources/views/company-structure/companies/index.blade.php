@extends('layout.mainlayout')
@section('content')
@php
    $canCreateCompany = \App\Support\TenantPermissions::userCan('Company', 'create');
    $canImportCompany = \App\Support\TenantPermissions::userCan('Company', 'import');
    $canExportCompany = \App\Support\TenantPermissions::userCan('Company', 'export');
@endphp

<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        {{-- Breadcrumb --}}
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Companies</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Company Structure</li>
                        <li class="breadcrumb-item active" aria-current="page">Companies</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExportCompany)<div class="mb-2 me-2">
                    <a href="{{ url('company-structure/companies/export') }}"
                        class="btn btn-light d-flex align-items-center">
                        <i class="ti ti-file-export me-2"></i>Export
                    </a>
                </div>@endif
                @if ($canImportCompany)<div class="mb-2 me-2">
                    <button type="button"
                        class="btn btn-light d-flex align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target="#import_company_modal">
                        <i class="ti ti-file-import me-2"></i>Import
                    </button>
                </div>@endif
                @if ($canCreateCompany)<div class="mb-2">
                    <a href="{{ url('company-structure/companies/create') }}"
                        class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Add Company
                    </a>
                </div>@endif
                <div class="head-icons ms-2">
                    <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-original-title="Collapse" id="collapse-header">
                        <i class="ti ti-chevrons-up"></i>
                    </a>
                </div>
            </div>
        </div>
        {{-- /Breadcrumb --}}

        <div id="company-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i>
            <span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>

        {{-- Company List --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Company List</h5>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown me-3">
                        <a href="javascript:void(0);"
                            class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
                            data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li>
                                <button type="button" class="dropdown-item rounded-1 company-status-filter"
                                    data-status="all">All</button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-1 company-status-filter"
                                    data-status="1">Active</button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item rounded-1 company-status-filter"
                                    data-status="0">Inactive</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>
                                <th class="no-sort">
                                    <div class="form-check form-check-md">
                                        <input class="form-check-input" type="checkbox" id="select-all">
                                    </div>
                                </th>
                                <th>Company Name</th>
                                <th>Code</th>
                                <th>Email</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('company-structure.companies.partials.rows', ['companies' => $companies])
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($companies->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">
                        Showing {{ $companies->firstItem() }}–{{ $companies->lastItem() }} of {{ $companies->total() }} companies
                    </p>
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
        {{-- /Company List --}}

    </div>

    @include('partials.footer')
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="delete_company_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                    <i class="ti ti-trash-x fs-36"></i>
                </span>
                <h4 class="mb-1">Confirm Delete</h4>
                <p class="mb-3 text-muted" id="delete_company_message">
                    Are you sure you want to delete this company? This action cannot be undone.
                </p>
                <form id="delete_company_form" method="POST" action="{{ url('company-structure/companies') }}">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-trash me-1"></i>Yes, Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- /Delete Confirmation Modal --}}

{{-- Import Company Modal --}}
@if ($canImportCompany)<div class="modal fade" id="import_company_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Companies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ url('company-structure/companies/import') }}" enctype="multipart/form-data"
                data-import-form="true"
                data-status-alert="#company-status-alert"
                data-modal="#import_company_modal"
                data-url-match="companies"
                data-entity-label="Company">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="company_import_file" class="form-label">CSV File</label>
                        <input id="company_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                        <a href="{{ url('company-structure/companies/import/sample') }}" class="d-inline-flex align-items-center mt-2">
                            <i class="ti ti-download me-1"></i>Download Sample CSV
                        </a>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_duplicate_records" value="1">
                        <label class="form-check-label" for="update_duplicate_records">
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
{{-- /Import Company Modal --}}

@push('scripts')
@include('partials.import-poll')
<script>
    // Wire up the delete modal with the correct company id and name (delegated so it works after dynamic table updates)
    document.addEventListener('click', function (event) {
        var btn = event.target.closest('.delete-company-btn');

        if (!btn) {
            return;
        }

        var id   = btn.dataset.id;
        var name = btn.dataset.name;
        var url  = btn.dataset.url || '{{ url('company-structure/companies') }}/' + id;

        document.getElementById('delete_company_form').action = url;
        document.getElementById('delete_company_message').textContent =
            'Are you sure you want to delete "' + name + '"? This action cannot be undone.';
    }, true);

    document.addEventListener('click', function (event) {
        var filterButton = event.target.closest('.company-status-filter');

        if (!filterButton || filterButton.disabled) {
            return;
        }

        event.preventDefault();

        var alert = document.getElementById('company-status-alert');
        filterButton.disabled = true;

        fetch('{{ url('company-structure/companies/filter') }}', {
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
                        throw new Error(data.message || 'Unable to filter companies.');
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

                // Reuse the shared alert helper exposed by import-poll partial
                if (typeof window.importPollShowAlert === 'function') {
                    window.importPollShowAlert(alert, 'success', 'Company status filter applied successfully.');
                } else {
                    alert.className = 'alert alert-success alert-dismissible d-flex align-items-center';
                    alert.querySelector('.alert-message').textContent = 'Company status filter applied successfully.';
                    alert.classList.remove('d-none');
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
