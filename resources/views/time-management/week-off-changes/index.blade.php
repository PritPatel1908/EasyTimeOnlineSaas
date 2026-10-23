@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('WeekOffChange', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('WeekOffChange', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('WeekOffChange', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Week-off Changes</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item">Manual Structure</li>
                    <li class="breadcrumb-item active" aria-current="page">Week-off Changes</li>
                </ol></nav>
            </div>
            @if ($canCreate || $canImport || $canExport)
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if ($canExport)
                        <div class="mb-2 me-2"><a href="{{ url('time-management/week-off-changes/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>
                    @endif
                    @if ($canImport)
                        <div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_week_off_change_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                    @endif
                    @if ($canCreate)
                    <div class="mb-2"><a href="{{ url('time-management/week-off-changes/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Week-off Change</a></div>
                    @endif
                </div>
            @endif
        </div>
        <div class="card">
            <div class="card-header"><h5>Week-off Change List</h5></div>
            <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th></th><th>Employees</th><th>Week-off Details</th><th>From Date</th><th>To Date</th><th>Reason</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($weekOffChanges as $weekOffChange)
                            <tr>
                                <td><input class="form-check-input" type="checkbox" value="{{ $weekOffChange->id }}"></td>
                                <td class="text-center">{{ $weekOffChange->Users->map(fn ($user) => $user->name . ($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
                                <td class="text-center">{{ $weekOffChange->WeekOffChangeDetails->count() }} day(s)</td>
                                <td class="text-center">{{ $weekOffChange->from_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-center">{{ $weekOffChange->is_forever ? 'No end date' : ($weekOffChange->to_date?->format('Y-m-d') ?? '—') }}</td>
                                <td class="text-center">{{ $weekOffChange->reason ?: '—' }}</td>
                                <td class="text-center">
                                    <div class="action-icon d-inline-flex">
                                        @if (\App\Support\TenantPermissions::userCan('WeekOffChange', 'read'))
                                            <a href="{{ url('time-management/week-off-changes/'.$weekOffChange->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                                        @endif
                                        @if (\App\Support\TenantPermissions::userCan('WeekOffChange', 'write'))
                                            <a href="{{ url('time-management/week-off-changes/'.$weekOffChange->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                                        @endif
                                        @if (\App\Support\TenantPermissions::userCan('WeekOffChange', 'delete'))
                                            <a href="javascript:void(0);" class="text-danger delete-week-off-change-btn"
                                                data-url="{{ url('time-management/week-off-changes/'.$weekOffChange->id) }}"
                                                data-name="{{ $weekOffChange->from_date?->format('Y-m-d') }}"
                                                data-bs-toggle="modal" data-bs-target="#delete_week_off_change_modal" title="Delete"><i class="ti ti-trash"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No week-off changes found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
            @if ($weekOffChanges->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $weekOffChanges->firstItem() }}–{{ $weekOffChanges->lastItem() }} of {{ $weekOffChanges->total() }} week-off changes</p>
                    {{ $weekOffChanges->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
@if ($canImport)
    <div class="modal fade" id="import_week_off_change_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Week-off Changes</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/week-off-changes/import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <label for="week_off_change_import_file" class="form-label">CSV File</label>
                    <input id="week_off_change_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                    <a href="{{ url('time-management/week-off-changes/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                    <small class="text-muted d-block mt-2">Separate employee codes and each parallel week-off detail list with commas.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
            </form>
        </div></div>
    </div>
@endif
<div class="modal fade" id="delete_week_off_change_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-body text-center">
            <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
            <h4 class="mb-1">Confirm Delete</h4>
            <p class="mb-3 text-muted" id="delete_week_off_change_message">Are you sure you want to delete this week-off change?</p>
            <form id="delete_week_off_change_form" method="POST">@csrf @method('DELETE')
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Yes, Delete</button>
                </div>
            </form>
        </div>
    </div></div>
</div>
@push('scripts')
<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('.delete-week-off-change-btn');
    if (!button) return;
    document.getElementById('delete_week_off_change_form').action = button.dataset.url;
    document.getElementById('delete_week_off_change_message').textContent =
        'Are you sure you want to delete "' + button.dataset.name + '"? This action cannot be undone.';
}, true);
</script>
@endpush
@endsection
