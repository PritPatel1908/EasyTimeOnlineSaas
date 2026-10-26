@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('ApprovalFlow', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('ApprovalFlow', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('ApprovalFlow', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div id="approval-flow-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i><span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">Approval Flows</h2>
                <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Employee Structure</li><li class="breadcrumb-item active">Approval Flows</li></ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($canExport)<a class="btn btn-light" href="{{ url('employee-structure/approval-flows/export') }}"><i class="ti ti-file-export me-2"></i>Export</a>@endif
                @if($canImport)<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_approval_flow_modal"><i class="ti ti-file-import me-2"></i>Import</button>@endif
                @if($canCreate)<a class="btn btn-primary" href="{{ url('employee-structure/approval-flows/create') }}"><i class="ti ti-circle-plus me-2"></i>Add Approval Flow</a>@endif
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Approval Flow List</h5>
                <div>
                    <div class="dropdown">
                        <button type="button" class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown" aria-label="Filter by approval flow status">
                            Status
                        </button>
                        <ul class="dropdown-menu">
                            <li><button type="button" class="dropdown-item approval-flow-status-filter" data-status="all">All</button></li>
                            <li><button type="button" class="dropdown-item approval-flow-status-filter" data-status="1">Active</button></li>
                            <li><button type="button" class="dropdown-item approval-flow-status-filter" data-status="0">Inactive</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable" data-searchable="false">
                        <thead class="thead-light"><tr><th class="no-sort"><div class="form-check form-check-md"><input class="form-check-input datatable-select-all" type="checkbox" id="select-all" aria-label="Select all rows"></div></th><th>Approval Flow Code</th><th>Status</th><th>Approval Levels</th><th>Approvers</th><th></th></tr></thead>
                        <tbody>@include('employee-structure.approval-flows.partials.rows')</tbody>
                    </table>
                </div>
            </div>
            @if($approvalFlows->hasPages())
                <div class="card-footer d-flex justify-content-between">
                    <span class="text-muted">Showing {{ $approvalFlows->firstItem() }}–{{ $approvalFlows->lastItem() }} of {{ $approvalFlows->total() }} approval flows</span>
                    {{ $approvalFlows->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
<div class="modal fade" id="delete_approval_flow_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center">
        <h4>Confirm Delete</h4><p id="delete_approval_flow_message">Are you sure you want to delete this approval flow?</p>
        <form id="delete_approval_flow_form" method="POST">@csrf @method('DELETE')
            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Yes, Delete</button>
        </form>
    </div></div></div>
</div>
@if($canImport)
<div class="modal fade" id="import_approval_flow_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Import Approval Flows</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="import_approval_flow_form" method="POST" action="{{ url('employee-structure/approval-flows/import') }}" enctype="multipart/form-data" data-import-form="true" data-status-alert="#approval-flow-status-alert" data-modal="#import_approval_flow_modal" data-url-match="/employee-structure/approval-flows" data-entity-label="Approval flow">
            @csrf
            <div class="modal-body">
                <label for="approval_flow_import_file" class="form-label">CSV File</label>
                <input id="approval_flow_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                <a href="{{ url('employee-structure/approval-flows/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                <p class="small text-muted mt-2 mb-0">Use one row per approval level. Separate approver emails or user IDs with |.</p>
                <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_approval_flow_duplicates" value="1"><label class="form-check-label" for="update_approval_flow_duplicates">Update Duplicate Records</label></div>
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
    var button = event.target.closest('.delete-approval-flow-btn');
    if (button) {
        document.getElementById('delete_approval_flow_form').action = button.dataset.url;
        document.getElementById('delete_approval_flow_message').textContent = 'Are you sure you want to delete "' + button.dataset.code + '"?';
    }
});
function applyApprovalFlowStatusFilter(status) {
    var alert = document.getElementById('approval-flow-status-alert');
    fetch('{{ url('employee-structure/approval-flows/filter') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    })
        .then(function (response) {
            if (!response.ok) throw new Error('Unable to filter approval flows.');
            return response.json();
        })
        .then(function (data) {
            var table = document.querySelector('table.datatable');
            var sourceBody = table.querySelector('tbody');
            var rowContainer = document.createElement('tbody');
            rowContainer.innerHTML = data.html;
            sourceBody.innerHTML = data.html;

            var visibleBody = document.querySelector('.gridjs-tbody');
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
                summary.textContent = data.count === 0 ? 'No records to show' : 'Showing 1-' + data.count + ' of ' + data.count + ' entries';
            }

            window.importPollShowAlert(alert, 'success', 'Approval flow status filter applied successfully.');
        })
        .catch(function (error) {
            window.importPollShowAlert(alert, 'danger', error.message);
        });
}
document.querySelectorAll('.approval-flow-status-filter').forEach(function (button) {
    button.addEventListener('click', function () {
        applyApprovalFlowStatusFilter(this.dataset.status);
    });
});
</script>
@endpush
@endsection
