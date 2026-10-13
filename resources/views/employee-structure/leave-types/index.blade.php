@extends('layout.mainlayout')
@section('content')
@php
    $canCreateLeaveType = \App\Support\TenantPermissions::userCan('LeaveType', 'create');
    $canImportLeaveType = \App\Support\TenantPermissions::userCan('LeaveType', 'import');
    $canExportLeaveType = \App\Support\TenantPermissions::userCan('LeaveType', 'export');
@endphp
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3"><div class="my-auto mb-2"><h2 class="mb-1">Leave Types</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Employee Structure</li><li class="breadcrumb-item active">Leave Types</li></ol></nav></div><div class="d-flex my-xl-auto right-content align-items-center flex-wrap">@if ($canExportLeaveType)<div class="mb-2 me-2"><a href="{{ url('employee-structure/leave-types/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>@endif @if ($canImportLeaveType)<div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_leave_type_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>@endif @if ($canCreateLeaveType)<div class="mb-2"><a href="{{ url('employee-structure/leave-types/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Leave Type</a></div>@endif</div></div>
    <div id="leave-type-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert"><i class="ti ti-circle-check me-2 alert-icon"></i><span class="alert-message"></span><button type="button" class="btn-close" aria-label="Close"></button></div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><h5>Leave Type List</h5><div class="d-flex gap-2 flex-wrap"><div class="dropdown"><button type="button" class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button><ul class="dropdown-menu"><li><button type="button" class="dropdown-item leave-type-status-filter" data-status="all">All</button></li><li><button type="button" class="dropdown-item leave-type-status-filter" data-status="1">Active</button></li><li><button type="button" class="dropdown-item leave-type-status-filter" data-status="0">Inactive</button></li></ul></div></div></div>
        <div class="card-body p-0"><div class="custom-datatable-filter table-responsive"><table class="table datatable"><thead class="thead-light"><tr><th class="no-sort"><div class="form-check form-check-md"><input class="form-check-input" type="checkbox" id="select-all"></div></th><th>Code</th><th>Description</th><th>Location</th><th>Status</th><th></th></tr></thead><tbody>@include('employee-structure.leave-types.partials.rows', ['leaveTypes' => $leaveTypes])</tbody></table></div></div>
        @if ($leaveTypes->hasPages())<div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2"><p class="mb-0 text-muted fs-13">Showing {{ $leaveTypes->firstItem() }}–{{ $leaveTypes->lastItem() }} of {{ $leaveTypes->total() }} leave types</p>{{ $leaveTypes->links() }}</div>@endif
    </div>
</div>@include('partials.footer')</div>
<div class="modal fade" id="delete_leave_type_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span><h4 class="mb-1">Confirm Delete</h4><p class="mb-3 text-muted" id="delete_leave_type_message">Are you sure you want to delete this leave type?</p><form id="delete_leave_type_form" method="POST">@csrf @method('DELETE')<button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button></form></div></div></div></div>
@if ($canImportLeaveType)<div class="modal fade" id="import_leave_type_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Import Leave Types</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('employee-structure/leave-types/import') }}" enctype="multipart/form-data" data-import-form="true" data-status-alert="#leave-type-status-alert" data-modal="#import_leave_type_modal" data-url-match="/employee-structure/leave-types" data-entity-label="Leave Type">@csrf<div class="modal-body"><label for="leave_type_import_file" class="form-label">CSV File</label><input id="leave_type_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required><a href="{{ url('employee-structure/leave-types/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_leave_type_duplicates" value="1"><label class="form-check-label" for="update_leave_type_duplicates">Update Duplicate Records</label></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div></form></div></div></div>@endif
@push('scripts')
@include('partials.import-poll')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.delete-leave-type-btn');
        if (!button) return;
        document.getElementById('delete_leave_type_form').action = button.dataset.url;
        document.getElementById('delete_leave_type_message').textContent =
            'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
    }, true);

    document.addEventListener('click', function (event) {
        var filterButton = event.target.closest('.leave-type-status-filter');
        if (!filterButton || filterButton.disabled) return;
        event.preventDefault();
        var alert = document.getElementById('leave-type-status-alert');
        filterButton.disabled = true;
        fetch('{{ url('employee-structure/leave-types/filter') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({status: filterButton.dataset.status})
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.message || 'Unable to filter leave types.');
                    return data;
                });
            })
            .then(function (data) {
                var table = document.querySelector('table.datatable');
                var rowContainer = document.createElement('tbody');
                var visibleBody = document.querySelector('.gridjs-tbody');
                rowContainer.innerHTML = data.html;
                table.querySelector('tbody').innerHTML = data.html;
                if (visibleBody) {
                    visibleBody.innerHTML = '';
                    rowContainer.querySelectorAll('tr').forEach(function (row) {
                        row.classList.add('gridjs-tr');
                        row.querySelectorAll('td').forEach(function (cell) { cell.classList.add('gridjs-td'); });
                        visibleBody.appendChild(row);
                    });
                }
                var summary = document.querySelector('.gridjs-summary');
                if (summary) summary.textContent = data.count === 0 ? 'No records to show' : 'Showing 1-' + data.count + ' of ' + data.count + ' entries';
                if (typeof window.importPollShowAlert === 'function') window.importPollShowAlert(alert, 'success', 'Leave Type status filter applied successfully.');
            })
            .catch(function (error) {
                if (typeof window.importPollShowAlert === 'function') window.importPollShowAlert(alert, 'danger', error.message);
            })
            .finally(function () { filterButton.disabled = false; });
    }, true);
</script>
@endpush
@endsection
