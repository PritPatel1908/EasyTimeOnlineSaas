@extends('layout.mainlayout')
@section('content')
@php
    $canCreate = \App\Support\TenantPermissions::userCan('WeekOffSwap', 'create');
    $canImport = \App\Support\TenantPermissions::userCan('WeekOffSwap', 'import');
    $canExport = \App\Support\TenantPermissions::userCan('WeekOffSwap', 'export');
@endphp
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Week-off Swaps</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('index') }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">Time Management</li>
                    <li class="breadcrumb-item">Manual Structure</li>
                    <li class="breadcrumb-item active" aria-current="page">Week-off Swaps</li>
                </ol></nav>
            </div>
            @if ($canCreate || $canImport || $canExport)
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    @if ($canExport)
                        <div class="mb-2 me-2"><a href="{{ url('time-management/week-off-swaps/export') }}" class="btn btn-light d-flex align-items-center"><i class="ti ti-file-export me-2"></i>Export</a></div>
                    @endif
                    @if ($canImport)
                        <div class="mb-2 me-2"><button type="button" class="btn btn-light d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#import_week_off_swap_modal"><i class="ti ti-file-import me-2"></i>Import</button></div>
                    @endif
                    @if ($canCreate)
                        <div class="mb-2"><a href="{{ url('time-management/week-off-swaps/create') }}" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Week-off Swap</a></div>
                    @endif
                </div>
            @endif
        </div>
        <div class="card">
            <div class="card-header"><h5>Week-off Swap List</h5></div>
            <div class="card-body p-0"><div class="custom-datatable-filter table-responsive">
                <table class="table datatable">
                    <thead class="thead-light"><tr>
                        <th></th><th>Employees</th><th>Week Date</th><th>Swap Date</th><th>Reason</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($weekOffSwaps as $weekOffSwap)
                            <tr>
                                <td><input class="form-check-input" type="checkbox" value="{{ $weekOffSwap->id }}"></td>
                                <td class="text-center">{{ $weekOffSwap->Users->map(fn ($user) => $user->name.($user->code ? ' ('.$user->code.')' : ''))->join(', ') ?: '—' }}</td>
                                <td class="text-center">{{ $weekOffSwap->week_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-center">{{ $weekOffSwap->swap_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-center">{{ $weekOffSwap->reason ?: '—' }}</td>
                                <td class="text-center">
                                    <div class="action-icon d-inline-flex">
                                        @if (\App\Support\TenantPermissions::userCan('WeekOffSwap', 'read'))
                                            <a href="{{ url('time-management/week-off-swaps/'.$weekOffSwap->id) }}" class="me-2" title="View"><i class="ti ti-eye"></i></a>
                                        @endif
                                        @if (\App\Support\TenantPermissions::userCan('WeekOffSwap', 'write'))
                                            <a href="{{ url('time-management/week-off-swaps/'.$weekOffSwap->id.'/edit') }}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                                        @endif
                                        @if (\App\Support\TenantPermissions::userCan('WeekOffSwap', 'delete'))
                                            <a href="javascript:void(0);" class="text-danger delete-week-off-swap-btn"
                                                data-url="{{ url('time-management/week-off-swaps/'.$weekOffSwap->id) }}"
                                                data-name="{{ $weekOffSwap->week_date?->format('Y-m-d') }}"
                                                data-bs-toggle="modal" data-bs-target="#delete_week_off_swap_modal" title="Delete"><i class="ti ti-trash"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-muted text-center"><i class="ti ti-calendar-off fs-24 mb-2 d-block"></i>No week-off swaps found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
            @if ($weekOffSwaps->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <p class="mb-0 text-muted fs-13">Showing {{ $weekOffSwaps->firstItem() }}–{{ $weekOffSwaps->lastItem() }} of {{ $weekOffSwaps->total() }} week-off swaps</p>
                    {{ $weekOffSwaps->links() }}
                </div>
            @endif
        </div>
    </div>
    @include('partials.footer')
</div>
@if ($canImport)
    <div class="modal fade" id="import_week_off_swap_modal">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Week-off Swaps</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form method="POST" action="{{ url('time-management/week-off-swaps/import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <label for="week_off_swap_import_file" class="form-label">CSV File</label>
                    <input id="week_off_swap_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required>
                    <a href="{{ url('time-management/week-off-swaps/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a>
                    <small class="text-muted d-block mt-2">Use employee codes separated by commas and dates in YYYY-MM-DD format.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
            </form>
        </div></div>
    </div>
@endif
<div class="modal fade" id="delete_week_off_swap_modal">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-body text-center">
            <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
            <h4 class="mb-1">Confirm Delete</h4>
            <p class="mb-3 text-muted" id="delete_week_off_swap_message">Are you sure you want to delete this week-off swap?</p>
            <form id="delete_week_off_swap_form" method="POST">@csrf @method('DELETE')
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div></div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-week-off-swap-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('delete_week_off_swap_form').action = button.dataset.url;
            const date = button.dataset.name;
            document.getElementById('delete_week_off_swap_message').textContent =
                'Are you sure you want to delete the week-off swap for ' + (date || 'this date') + '?';
        });
    });
});
</script>
@endpush
@endsection
