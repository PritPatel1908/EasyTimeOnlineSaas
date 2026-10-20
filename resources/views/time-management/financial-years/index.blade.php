@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('FinancialYear', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('FinancialYear', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('FinancialYear', 'export');
@endphp

<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Financial Years</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Financial Years</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExport)
                    <div class="mb-2 me-2"><a href="{{ url('time-management/financial-years/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>
                @endif
                @if ($canImport)
                    <div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_financial_year_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                @endif
                @if ($canCreate)
                    <div class="mb-2"><a href="{{ url('time-management/financial-years/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Financial Year</a></div>
                @endif
            </div>
        </div>

        <div id="financial-year-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i><span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Financial Year List</h5>
                <div class="dropdown">
                    <button type="button" class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                    <ul class="dropdown-menu">
                        <li><button type="button" class="dropdown-item financial-year-status-filter" data-status="all">All</button></li>
                        <li><button type="button" class="dropdown-item financial-year-status-filter" data-status="1">Active</button></li>
                        <li><button type="button" class="dropdown-item financial-year-status-filter" data-status="0">Inactive</button></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light"><tr>
                            <th class="no-sort"><div class="form-check form-check-md"><input class="form-check-input" type="checkbox" id="select-all"></div></th>
                            <th>Code</th><th>Name</th><th>Start Date</th><th>End Date</th><th>Location</th><th>Company</th>
                            <th>Status</th><th>Action</th>
                        </tr></thead>
                        <tbody>@include('time-management.financial-years.partials.rows', ['financialYears' => $financialYears])</tbody>
                    </table>
                </div>
            </div>
            @if ($financialYears->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $financialYears->firstItem() }}–{{ $financialYears->lastItem() }} of {{ $financialYears->total() }} financial years</p>
                    {{ $financialYears->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>

<div class="modal fade" id="delete_financial_year_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center">
        <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
        <h4 class="mb-1">Confirm Delete</h4>
        <p class="mb-3 text-muted" id="delete_financial_year_message">Are you sure you want to delete this financial year?</p>
        <form id="delete_financial_year_form" method="POST" action="{{ url('time-management/financial-years') }}">
            @csrf @method('DELETE')
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button>
            </div>
        </form>
    </div></div></div>
</div>

@if ($canImport)
<div class="modal fade" id="import_financial_year_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Import Financial Years</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form method="POST" action="{{ url('time-management/financial-years/import') }}" enctype="multipart/form-data"
            data-import-form="true" data-status-alert="#financial-year-status-alert"
            data-modal="#import_financial_year_modal" data-url-match="/time-management/financial-years"
            data-entity-label="Financial Year">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label for="financial_year_import_file" class="form-label">CSV File</label>
                    <input id="financial_year_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                    <a href="{{ url('time-management/financial-years/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_financial_year_duplicates" value="1">
                    <label class="form-check-label" for="update_financial_year_duplicates">Update Duplicate Records</label>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
        </form>
    </div></div>
</div>
@endif

@push('scripts')
@include('partials.import-poll')
<script>
document.addEventListener('click', function (event) {
    var flagAction = event.target.closest('.financial-year-flag-action');
    if (flagAction) {
        event.preventDefault();
        var form = document.getElementById(flagAction.dataset.form);
        if (form) {
            form.requestSubmit();
        }
        return;
    }

    var deleteButton = event.target.closest('.delete-financial-year-btn');
    if (deleteButton) {
        document.getElementById('delete_financial_year_form').action = deleteButton.dataset.url;
        document.getElementById('delete_financial_year_message').textContent =
            'Are you sure you want to delete "' + deleteButton.dataset.name + '"? This action cannot be undone.';
        return;
    }

    var filterButton = event.target.closest('.financial-year-status-filter');
    if (!filterButton || filterButton.disabled) {
        return;
    }

    event.preventDefault();
    filterButton.disabled = true;
    fetch('{{ url('time-management/financial-years/filter') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: filterButton.dataset.status })
    }).then(function (response) {
        return response.json().then(function (data) {
            if (!response.ok) {
                throw new Error(data.message || 'Unable to filter financial years.');
            }
            return data;
        });
    }).then(function (data) {
        var table = document.querySelector('table.datatable');
        var rows = document.createElement('tbody');
        rows.innerHTML = data.html;
        table.querySelector('tbody').innerHTML = data.html;
        var visibleBody = document.querySelector('.gridjs-tbody');
        if (visibleBody) {
            visibleBody.innerHTML = '';
            rows.querySelectorAll('tr').forEach(function (row) {
                row.classList.add('gridjs-tr');
                row.querySelectorAll('td').forEach(function (cell) { cell.classList.add('gridjs-td'); });
                visibleBody.appendChild(row);
            });
        }
        if (typeof window.importPollShowAlert === 'function') {
            window.importPollShowAlert(document.getElementById('financial-year-status-alert'), 'success', 'Financial Year status filter applied.');
        }
    }).catch(function (error) {
        if (typeof window.importPollShowAlert === 'function') {
            window.importPollShowAlert(document.getElementById('financial-year-status-alert'), 'danger', error.message);
        }
    }).finally(function () {
        filterButton.disabled = false;
    });
});
</script>
@endpush
@endsection
