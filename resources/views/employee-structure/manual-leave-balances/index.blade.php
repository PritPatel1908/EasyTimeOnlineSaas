@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('ManualLeaveBalance', 'export');
@endphp
<div class="page-wrapper"><div class="content">
    @include('partials.flash-alerts')
    <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
        <div class="my-auto mb-2"><h2 class="mb-1">Manual Leave Balances</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li><li class="breadcrumb-item">Leave Structure</li><li class="breadcrumb-item active">Manual Leave Balances</li></ol></nav></div>
        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
            @if ($canExport)<div class="mb-2 me-2"><a href="{{ url('employee-structure/manual-leave-balances/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>@endif
            @if ($canImport)<div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_manual_leave_balance_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>@endif
            @if ($canCreate)<div class="mb-2"><a href="{{ url('employee-structure/manual-leave-balances/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Manual Leave Balance</a></div>@endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5>Manual Leave Balance List</h5></div>
        <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
            <table class="table datatable">
                <thead class="thead-light"><tr><th class="no-sort"><div class="form-check form-check-md"><input class="form-check-input" type="checkbox" id="select-all"></div></th><th>Employees</th><th>Leave Type</th><th>Balance</th><th>Date &amp; Time</th><th>Locations</th><th>Companies</th><th>Reason</th><th></th></tr></thead>
                <tbody>@include('employee-structure.manual-leave-balances.partials.rows', ['balances' => $balances])</tbody>
            </table>
        </div></div>
        @if ($balances->hasPages())<div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2"><p class="mb-0 text-muted fs-13">Showing {{ $balances->firstItem() }}–{{ $balances->lastItem() }} of {{ $balances->total() }} manual leave balances</p>{{ $balances->links() }}</div>@endif
    </div>
</div>@include('partials.footer')</div>
<div class="modal fade" id="delete_manual_leave_balance_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span><h4 class="mb-1">Confirm Delete</h4><p class="mb-3 text-muted" id="delete_manual_leave_balance_message">Are you sure you want to delete this balance?</p><form id="delete_manual_leave_balance_form" method="POST">@csrf @method('DELETE')<button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button></form></div></div></div></div>
@if ($canImport)<div class="modal fade" id="import_manual_leave_balance_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Import Manual Leave Balances</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ url('employee-structure/manual-leave-balances/import') }}" enctype="multipart/form-data" data-import-form="true" data-modal="#import_manual_leave_balance_modal" data-url-match="/employee-structure/manual-leave-balances" data-entity-label="Manual leave balance">@csrf<div class="modal-body"><label for="manual_leave_balance_import_file" class="form-label">CSV File</label><input id="manual_leave_balance_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required><a href="{{ url('employee-structure/manual-leave-balances/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_manual_leave_balance_duplicates" value="1"><label class="form-check-label" for="update_manual_leave_balance_duplicates">Update Duplicate Records</label></div><small class="text-muted d-block mt-2">Use semicolons to separate multiple employee, location or company codes.</small></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div></form></div></div></div>@endif
@push('scripts')
@include('partials.import-poll')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.delete-manual-leave-balance-btn');
        if (!button) return;
        document.getElementById('delete_manual_leave_balance_form').action = button.dataset.url;
        document.getElementById('delete_manual_leave_balance_message').textContent =
            'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
    }, true);
</script>
@endpush
@endsection
