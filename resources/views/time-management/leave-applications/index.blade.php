@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('LeaveApplication', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('LeaveApplication', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('LeaveApplication', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Leave Applications</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Leave Applications</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExport)
                    <div class="mb-2 me-2"><a href="{{ url('time-management/leave-applications/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>
                @endif
                @if ($canImport)
                    <div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_leave_application_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                @endif
                @if ($canCreate)
                    <div class="mb-2"><a href="{{ url('time-management/leave-applications/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Application</a></div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5>Leave Application List</h5></div>
            <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th></th><th>Employees</th><th>Leave Type</th><th>Date Range</th><th>Leave Count</th><th>Application Date</th><th>Status</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td><input class="form-check-input" type="checkbox" value="{{ $application->id }}"></td>
                                <td>{{ $application->users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
                                <td>{{ $application->leave_type?->code ?: '—' }}</td>
                                <td class="text-center">{{ $application->from_date?->format('Y-m-d') ?? '—' }} – {{ $application->to_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-center">{{ $application->leave_count ?? '—' }}</td>
                                <td class="text-center">{{ $application->application_date?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-center"><span class="badge bg-{{ (int) $application->status === 1 ? 'success' : 'secondary' }}">{{ (int) $application->status === 1 ? 'Active' : 'Inactive' }}</span></td>
                                <td class="text-center"><div class="action-icon d-inline-flex">
                                    @if (\App\Support\TenantPermissions::userCan('LeaveApplication', 'read'))
                                        <a href="{{ url('time-management/leave-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                                    @endif
                                    @if (\App\Support\TenantPermissions::userCan('LeaveApplication', 'write'))
                                        <a href="{{ url('time-management/leave-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                                    @endif
                                    @if (\App\Support\TenantPermissions::userCan('LeaveApplication', 'delete'))
                                        <a href="javascript:void(0);" class="text-danger delete-leave-application-btn"
                                            data-url="{{ url('time-management/leave-applications/'.$application->id) }}"
                                            data-name="Leave application #{{ $application->id }}"
                                            data-bs-toggle="modal" data-bs-target="#delete_leave_application_modal" title="Delete"><i class="ti ti-trash"></i></a>
                                    @endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No leave applications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
            @if ($applications->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }} applications</p>
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
@if ($canImport)
    <div class="modal fade" id="import_leave_application_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Leave Applications</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/leave-applications/import') }}" enctype="multipart/form-data"
                data-import-form="true" data-modal="#import_leave_application_modal"
                data-url-match="/time-management/leave-applications" data-entity-label="Leave application">
                @csrf
                <div class="modal-body">
                    <label for="leave_application_import_file" class="form-label">CSV File</label>
                    <input id="leave_application_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                    <a href="{{ url('time-management/leave-applications/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                    <small class="text-muted d-block mt-2">Separate employee codes with commas. Leave type code and leave reason must match existing records.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
            </form>
        </div></div>
    </div>
@endif
<div class="modal fade" id="delete_leave_application_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center">
        <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
        <h4 class="mb-1">Confirm Delete</h4>
        <p class="mb-3 text-muted" id="delete_leave_application_message">Are you sure you want to delete this leave application?</p>
        <form id="delete_leave_application_form" method="POST">@csrf @method('DELETE')
            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button>
        </form>
    </div></div></div>
</div>
@push('scripts')
    @if ($canImport)
        @include('partials.import-poll')
    @endif
    <script>
        document.addEventListener('click', function (event) {
            var button = event.target.closest('.delete-leave-application-btn');
            if (!button) return;
            document.getElementById('delete_leave_application_form').action = button.dataset.url;
            document.getElementById('delete_leave_application_message').textContent =
                'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
        }, true);
    </script>
@endpush
@endsection
