@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('ShortLeaveApplication', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Short Leave Applications</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Short Leave Applications</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExport)
                    <div class="mb-2 me-2"><a href="{{ url('time-management/short-leave-applications/export') }}" class="btn btn-light"><i class="ti ti-file-export me-2"></i>Export</a></div>
                @endif
                @if ($canImport)
                    <div class="mb-2 me-2"><button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_short_leave_application_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                @endif
                @if ($canCreate)
                    <div class="mb-2"><a href="{{ url('time-management/short-leave-applications/create') }}" class="btn btn-primary"><i class="ti ti-circle-plus me-2"></i>Add Application</a></div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Short Leave Application List</h5></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th>Employee</th><th>Type</th><th>Date</th><th>Minutes</th><th>Time Range</th><th>Reason</th><th>Approval Status</th><th></th>
                    </tr></thead>
                    <tbody>@include('time-management.short-leave-applications.partials.rows', ['applications' => $applications])</tbody>
                </table>
            </div></div>
            @if ($applications->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }} short leave applications</p>
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>

@if ($canImport)
    <div class="modal fade" id="import_short_leave_application_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Short Leave Applications</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/short-leave-applications/import') }}" enctype="multipart/form-data"
                data-import-form="true" data-modal="#import_short_leave_application_modal"
                data-url-match="/time-management/short-leave-applications" data-entity-label="Short leave application">
                @csrf
                <div class="modal-body">
                    <label for="short_leave_application_import_file" class="form-label">CSV File</label>
                    <input id="short_leave_application_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                    <a href="{{ url('time-management/short-leave-applications/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                    <small class="text-muted d-block mt-2">Each row applies to one employee and is submitted to that employee's short leave approval flow.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
            </form>
        </div></div>
    </div>
@endif
@push('scripts')
    @if ($canImport)
        @include('partials.import-poll')
    @endif
@endpush
@endsection
