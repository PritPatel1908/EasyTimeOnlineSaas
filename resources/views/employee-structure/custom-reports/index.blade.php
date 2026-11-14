@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('CustomReport', 'create');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div id="custom-report-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i>
            <span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
        <div class="d-flex justify-content-between page-breadcrumb mb-3">
            <div>
                <h2 class="mb-1">Custom Reports</h2>
                <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Reports</li><li class="breadcrumb-item active">Custom Reports</li></ol></nav>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($canCreate)
                    <a class="btn btn-primary" href="{{ url('employee-structure/custom-reports/create') }}"><i class="ti ti-circle-plus me-2"></i>Add Custom Report</a>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5>Custom Report List</h5>
                <div class="dropdown">
                    <button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                    <ul class="dropdown-menu">
                        <li><button class="dropdown-item custom-report-status-filter" data-status="all">All</button></li>
                        <li><button class="dropdown-item custom-report-status-filter" data-status="1">Active</button></li>
                        <li><button class="dropdown-item custom-report-status-filter" data-status="0">Inactive</button></li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr><th>Report Name</th><th>From Date</th><th>To Date</th><th>Orientation</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>@include('employee-structure.custom-reports.partials.rows')</tbody>
                    </table>
                </div>
            </div>
            @if($customReports->hasPages())
                <div class="card-footer d-flex justify-content-between">
                    <span class="text-muted">Showing {{ $customReports->firstItem() }}–{{ $customReports->lastItem() }} of {{ $customReports->total() }} custom reports</span>
                    {{ $customReports->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
<div class="modal fade" id="delete_custom_report_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center">
        <h4>Confirm Delete</h4>
        <p id="delete_custom_report_message">Are you sure you want to delete this custom report?</p>
        <form id="delete_custom_report_form" method="POST">@csrf @method('DELETE')
            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-danger">Yes, Delete</button>
        </form>
    </div></div></div>
</div>
@push('scripts')
@include('partials.import-poll')
<script>
document.addEventListener('click', function (event) {
    var button = event.target.closest('.delete-custom-report-btn');
    if (button) {
        document.getElementById('delete_custom_report_form').action = button.dataset.url;
        document.getElementById('delete_custom_report_message').textContent = 'Are you sure you want to delete "' + button.dataset.name + '"?';
    }
});

document.querySelectorAll('.custom-report-status-filter').forEach(function (button) {
    button.addEventListener('click', function () {
        var alert = document.getElementById('custom-report-status-alert');
        fetch('{{ url('employee-structure/custom-reports/filter') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({status: this.dataset.status})
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw new Error(data.message || 'Unable to filter custom reports.');
                }
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
            window.importPollShowAlert(alert, 'success', 'Custom report status filter applied successfully.');
        }).catch(function (error) {
            window.importPollShowAlert(alert, 'danger', error.message);
        });
    });
});
</script>
@endpush
@endsection
