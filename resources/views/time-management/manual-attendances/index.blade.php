@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('ManualAttendance', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('ManualAttendance', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('ManualAttendance', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Manual Attendances</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Manual Attendances</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExport)
                    <div class="mb-2 me-2"><a href="{{ url('time-management/manual-attendances/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>
                @endif
                @if ($canImport)
                    <div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_manual_attendance_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                @endif
                @if ($canCreate)
                    <div class="mb-2"><a href="{{ url('time-management/manual-attendances/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Manual Attendance</a></div>
                @endif
            </div>
        </div>

        <div id="manual-attendance-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
            <i class="ti ti-circle-check me-2 alert-icon"></i><span class="alert-message"></span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>

        <div class="card">
            <div class="card-header"><h5>Manual Attendance List</h5></div>
            <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th></th><th>Employees</th><th>In Time</th><th>Out Time</th><th>Company</th>
                        <th>Location</th><th>Reason</th><th></th>
                    </tr></thead>
                    <tbody>@include('time-management.manual-attendances.partials.rows', [
                        'attendances' => $attendances,
                        'companiesById' => $companiesById,
                        'locationsById' => $locationsById,
                    ])</tbody>
                </table>
            </div></div>
            @if ($attendances->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} of {{ $attendances->total() }} manual attendances</p>
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>

<div class="modal fade" id="delete_manual_attendance_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-body text-center">
            <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
            <h4 class="mb-1">Confirm Delete</h4>
            <p class="mb-3 text-muted" id="delete_manual_attendance_message">Are you sure you want to delete this manual attendance?</p>
            <form id="delete_manual_attendance_form" method="POST">@csrf @method('DELETE')
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button>
                </div>
            </form>
        </div>
    </div></div>
</div>

@if ($canImport)
    <div class="modal fade" id="import_manual_attendance_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Manual Attendances</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/manual-attendances/import') }}" enctype="multipart/form-data"
                data-import-form="true" data-status-alert="#manual-attendance-status-alert" data-modal="#import_manual_attendance_modal"
                data-url-match="/time-management/manual-attendances" data-entity-label="Manual attendance">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="manual_attendance_import_file" class="form-label">CSV File</label>
                        <input id="manual_attendance_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                        <a href="{{ url('time-management/manual-attendances/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                    </div>
                    <small class="text-muted">Use semicolons to separate multiple employee codes. Each attendance record uses one company and location.</small>
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
        var button = event.target.closest('.delete-manual-attendance-btn');
        if (!button) return;
        document.getElementById('delete_manual_attendance_form').action = button.dataset.url;
        document.getElementById('delete_manual_attendance_message').textContent =
            'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
    }, true);
</script>
@endpush
@endsection
