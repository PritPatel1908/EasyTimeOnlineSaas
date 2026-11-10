@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('CoffApplication', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('CoffApplication', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('CoffApplication', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">COFF Applications</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item active" aria-current="page">COFF Applications</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @if ($canExport)
                    <div class="mb-2 me-2"><a href="{{ url('time-management/coff-applications/export') }}" class="btn btn-light"><i class="ti ti-file-export me-2"></i>Export</a></div>
                @endif
                @if ($canImport)
                    <div class="mb-2 me-2"><button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_coff_application_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                @endif
                @if ($canCreate)
                    <div class="mb-2"><a href="{{ url('time-management/coff-applications/create') }}" class="btn btn-primary"><i class="ti ti-circle-plus me-2"></i>Add Application</a></div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">COFF Application List</h5></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th>Employee</th><th>Against Date</th><th>COFF Date Range</th><th>Half Day</th><th>Approval Status</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($applications as $application)
                            @php($approvalStatus = $application->overallApprovalStatus())
                            <tr>
                                <td>{{ $application->user?->name ?? '—' }}{{ $application->user?->code ? ' ('.$application->user->code.')' : '' }}</td>
                                <td>{{ $application->coff_against_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $application->coff_from_date?->format('Y-m-d') ?? '—' }} – {{ $application->coff_to_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $application->halfDayDescription() }}</td>
                                <td><span class="badge bg-{{ match ($approvalStatus) {'pending' => 'primary', 'approved' => 'success', 'rejected' => 'danger', 'draft' => 'secondary', 'cancelled' => 'dark', default => 'light text-dark'} }}">{{ ucfirst($approvalStatus) }}</span></td>
                                <td class="text-center"><div class="action-icon d-inline-flex">
                                    @if (\App\Support\TenantPermissions::userCan('CoffApplication', 'read'))
                                        <a href="{{ url('time-management/coff-applications/'.$application->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                                    @endif
                                    @if ($application->status !== 'approved' && \App\Support\TenantPermissions::userCan('CoffApplication', 'write'))
                                        <a href="{{ url('time-management/coff-applications/'.$application->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                                    @endif
                                    @if ($application->status !== 'approved' && \App\Support\TenantPermissions::userCan('CoffApplication', 'delete'))
                                        <form action="{{ url('time-management/coff-applications/'.$application->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this COFF application?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0" title="Delete"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No COFF applications found.</td></tr>
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
    <div class="modal fade" id="import_coff_application_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import COFF Applications</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/coff-applications/import') }}" enctype="multipart/form-data"
                data-import-form="true" data-modal="#import_coff_application_modal"
                data-url-match="/time-management/coff-applications" data-entity-label="COFF application">
                @csrf
                <div class="modal-body">
                    <label for="coff_application_import_file" class="form-label">CSV File</label>
                    <input id="coff_application_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                    <a href="{{ url('time-management/coff-applications/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                    <small class="text-muted d-block mt-2">Each row submits one employee's request to their assigned COFF approval flow.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
            </form>
        </div></div>
    </div>
    @push('scripts')
        @include('partials.import-poll')
    @endpush
@endif
@endsection
