@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('LeaveGroup', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('LeaveGroup', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('LeaveGroup', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div id="leave-group-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i>
            <span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">Leave Groups</h2>
                <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item active">Leave Groups</li></ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($canExport)<a class="btn btn-light" href="{{ url('employee-structure/leave-groups/export') }}"><i class="ti ti-file-export me-2"></i>Export</a>@endif
                {{-- @if($canImport)<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_leave_group_modal"><i class="ti ti-file-import me-2"></i>Import</button>@endif --}}
                @if($canCreate)<a class="btn btn-primary" href="{{ url('employee-structure/leave-groups/create') }}"><i class="ti ti-circle-plus me-2"></i>Add Leave Group</a>@endif
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5>Leave Group List</h5>
                <div class="dropdown">
                    <button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                    <ul class="dropdown-menu">
                        <li><button class="dropdown-item leave-group-status-filter" data-status="all">All</button></li>
                        <li><button class="dropdown-item leave-group-status-filter" data-status="1">Active</button></li>
                        <li><button class="dropdown-item leave-group-status-filter" data-status="0">Inactive</button></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light"><tr><th></th><th>Name</th><th>Financial Year</th><th>Frequency</th><th>Monthly Rules</th><th>Yearly Rules</th><th>Status</th><th></th></tr></thead>
                        <tbody>@include('employee-structure.leave-groups.partials.rows')</tbody>
                    </table>
                </div>
            </div>
            @if($leaveGroups->hasPages())
                <div class="card-footer d-flex justify-content-between">
                    <span class="text-muted">Showing {{ $leaveGroups->firstItem() }}–{{ $leaveGroups->lastItem() }} of {{ $leaveGroups->total() }} leave groups</span>
                    {{ $leaveGroups->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
<div class="modal fade" id="delete_leave_group_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center">
        <h4>Confirm Delete</h4><p id="delete_leave_group_message">Are you sure you want to delete this Leave Group?</p>
        <form id="delete_leave_group_form" method="POST">@csrf @method('DELETE')
            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-danger">Yes, Delete</button>
        </form>
    </div></div></div>
</div>
@if($canImport)
<div class="modal fade" id="import_leave_group_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Import Leave Groups</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="import_leave_group_form" method="POST" action="{{ url('employee-structure/leave-groups/import') }}" enctype="multipart/form-data" data-import-form="true" data-status-alert="#leave-group-status-alert" data-modal="#import_leave_group_modal" data-url-match="/employee-structure/leave-groups" data-entity-label="Leave Group">
            @csrf
            <div class="modal-body">
                <label for="leave_group_import_file" class="form-label">CSV File</label>
                <input id="leave_group_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                <a href="{{ url('employee-structure/leave-groups/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_leave_group_duplicates" value="1"><label class="form-check-label" for="update_leave_group_duplicates">Update Duplicate Records</label></div>
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
    var button = event.target.closest('.delete-leave-group-btn');
    if (!button) return;
    document.getElementById('delete_leave_group_form').action = button.dataset.url;
    document.getElementById('delete_leave_group_message').textContent = 'Are you sure you want to delete "' + button.dataset.name + '"?';
});

function applyLeaveGroupFilter(status) {
    var alert = document.getElementById('leave-group-status-alert');
    fetch('{{ url('employee-structure/leave-groups/filter') }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({status: status})
    }).then(function (response) {
        return response.json().then(function (data) {
            if (!response.ok) throw new Error(data.message || 'Unable to filter Leave Groups.');
            return data;
        });
    }).then(function (data) {
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
                row.querySelectorAll('td').forEach(function (cell) { cell.classList.add('gridjs-td'); });
                visibleBody.appendChild(row);
            });
        }
        var summary = document.querySelector('.gridjs-summary');
        if (summary) summary.textContent = data.count === 0 ? 'No records to show' : 'Showing 1-' + data.count + ' of ' + data.count + ' entries';
        window.importPollShowAlert(alert, 'success', 'Leave Group status filter applied successfully.');
    }).catch(function (error) {
        window.importPollShowAlert(alert, 'danger', error.message);
    });
}

document.querySelectorAll('.leave-group-status-filter').forEach(function (button) {
    button.addEventListener('click', function () { applyLeaveGroupFilter(this.dataset.status); });
});
</script>
@endpush
@endsection
