@extends('layout.mainlayout')
@section('content')
@php
    $canCreateRule = \App\Support\TenantPermissions::userCan('OvertimeRule', 'create');
    $canImportRule = \App\Support\TenantPermissions::userCan('OvertimeRule', 'import');
    $canExportRule = \App\Support\TenantPermissions::userCan('OvertimeRule', 'export');
@endphp

<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Overtime Rules</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Overtime Rules</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExportRule)
                    <div class="mb-2 me-2"><a href="{{ url('time-management/overtime-rules/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>
                @endif
                @if ($canImportRule)
                    <div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_overtime_rule_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                @endif
                @if ($canCreateRule)
                    <div class="mb-2"><a href="{{ url('time-management/overtime-rules/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Rule</a></div>
                @endif
            </div>
        </div>

        <div id="overtime-rule-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i><span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Overtime Rule List</h5>
                <div class="dropdown">
                    <button type="button" class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                    <ul class="dropdown-menu">
                        <li><button type="button" class="dropdown-item overtime-rule-status-filter" data-status="all">All</button></li>
                        <li><button type="button" class="dropdown-item overtime-rule-status-filter" data-status="1">Active</button></li>
                        <li><button type="button" class="dropdown-item overtime-rule-status-filter" data-status="0">Inactive</button></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light"><tr>
                            <th></th><th>Rule Code</th><th>Description</th><th>Ignore Early Coming</th>
                            <th>Ignore Late Coming</th><th>Overtime Threshold (HH:mm)</th><th>Slabs</th><th>Status</th><th></th>
                        </tr></thead>
                        <tbody>@include('time-management.overtime-rules.partials.rows', ['overtimeRules' => $overtimeRules])</tbody>
                    </table>
                </div>
            </div>
            @if ($overtimeRules->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $overtimeRules->firstItem() }}–{{ $overtimeRules->lastItem() }} of {{ $overtimeRules->total() }} overtime rules</p>
                    {{ $overtimeRules->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>

<div class="modal fade" id="delete_overtime_rule_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center">
        <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
        <h4 class="mb-1">Confirm Delete</h4>
        <p class="mb-3 text-muted" id="delete_overtime_rule_message">Are you sure you want to delete this rule?</p>
        <form id="delete_overtime_rule_form" method="POST" action="{{ url('time-management/overtime-rules') }}">
            @csrf @method('DELETE')
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button>
            </div>
        </form>
    </div></div></div>
</div>

@if ($canImportRule)
    <div class="modal fade" id="import_overtime_rule_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Overtime Rules</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/overtime-rules/import') }}" enctype="multipart/form-data"
                data-import-form="true" data-status-alert="#overtime-rule-status-alert"
                data-modal="#import_overtime_rule_modal" data-url-match="/time-management/overtime-rules"
                data-entity-label="Overtime rule">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="overtime_rule_import_file" class="form-label">CSV File</label>
                        <input id="overtime_rule_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                        <a href="{{ url('time-management/overtime-rules/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                        @error('file')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_overtime_rule_duplicate_records" value="1">
                        <label class="form-check-label" for="update_overtime_rule_duplicate_records">Update Duplicate Records</label>
                    </div>
                    <small class="text-muted d-block mt-2">Use one CSV row per slab; repeat the overtime rule fields for each slab.</small>
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
        var deleteButton = event.target.closest('.delete-overtime-rule-btn');
        if (deleteButton) {
            document.getElementById('delete_overtime_rule_form').action = deleteButton.dataset.url;
            document.getElementById('delete_overtime_rule_message').textContent =
                'Are you sure you want to delete "' + deleteButton.dataset.name + '"? This action cannot be undone.';
            return;
        }

        var filterButton = event.target.closest('.overtime-rule-status-filter');
        if (!filterButton || filterButton.disabled) {
            return;
        }

        event.preventDefault();
        filterButton.disabled = true;
        var alert = document.getElementById('overtime-rule-status-alert');

        fetch('{{ url('time-management/overtime-rules/filter') }}', {
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
                        throw new Error(data.message || 'Unable to filter overtime rules.');
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
                    window.importPollShowAlert(alert, 'success', 'Overtime rule status filter applied.');
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
    });
</script>
@endpush
@endsection
