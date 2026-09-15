@extends('layout.mainlayout')
@section('content')
@php $canCreate=\App\Support\TenantPermissions::userCan('Team','create'); $canImport=\App\Support\TenantPermissions::userCan('Team','import'); $canExport=\App\Support\TenantPermissions::userCan('Team','export'); @endphp
<div class="page-wrapper"><div class="content">@include('partials.flash-alerts')
    <div id="team-status-alert" class="alert alert-dismissible d-none align-items-center" role="alert">
        <i class="ti ti-circle-check me-2 alert-icon"></i>
        <span class="alert-message"></span>
        <button type="button" class="btn-close" aria-label="Close"></button>
    </div><div class="d-flex justify-content-between page-breadcrumb mb-3"><div><h2 class="mb-1">Teams</h2><nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item">Company Structure</li><li class="breadcrumb-item active">Teams</li></ol></nav></div><div class="d-flex align-items-center gap-2">@if($canExport)<a class="btn btn-light" href="{{ url('company-structure/teams/export') }}"><i class="ti ti-file-export me-2"></i>Export</a>@endif @if($canImport)<button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#import_team_modal"><i class="ti ti-file-import me-2"></i>Import</button>@endif @if($canCreate)<a class="btn btn-primary" href="{{ url('company-structure/teams/create') }}"><i class="ti ti-circle-plus me-2"></i>Add Team</a>@endif</div></div><div class="card"><div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><h5>Team List</h5><div class="d-flex gap-2 flex-wrap"><div class="dropdown"><button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Status</button><ul class="dropdown-menu"><li><button class="dropdown-item team-status-filter" data-status="all">All</button></li><li><button class="dropdown-item team-status-filter" data-status="1">Active</button></li><li><button class="dropdown-item team-status-filter" data-status="0">Inactive</button></li></ul></div></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table datatable"><thead class="thead-light"><tr><th></th><th>Name</th><th>Code</th><th>Email</th><th>Companies</th><th>Locations</th><th>Status</th><th></th></tr></thead><tbody>@include('company-structure.teams.partials.rows')</tbody></table></div></div>@if($teams->hasPages())<div class="card-footer d-flex justify-content-between"><span class="text-muted">Showing {{ $teams->firstItem() }}–{{ $teams->lastItem() }} of {{ $teams->total() }} teams</span>{{ $teams->links() }}</div>@endif</div></div>
<div class="modal fade" id="delete_team_modal"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><h4>Confirm Delete</h4><p id="delete_team_message">Are you sure you want to delete this team?</p><form id="delete_team_form" method="POST">@csrf @method('DELETE')<button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger">Yes, Delete</button></form></div></div></div></div>
@if ($canImport)
<div class="modal fade" id="import_team_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Import Teams</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="import_team_form" method="POST" action="{{ url('company-structure/teams/import') }}" enctype="multipart/form-data"
                data-import-form="true"
                data-status-alert="#team-status-alert"
                data-modal="#import_team_modal"
                data-url-match="/company-structure/teams"
                data-entity-label="Team">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label for="team_import_file" class="form-label">CSV File</label><input id="team_import_file" name="file" type="file" class="form-control" accept=".csv,.txt" required><a href="{{ url('company-structure/teams/import/sample') }}" class="d-inline-flex align-items-center mt-2"><i class="ti ti-download me-1"></i>Download Sample CSV</a></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="update_duplicate_records" id="update_team_duplicates" value="1"><label class="form-check-label" for="update_team_duplicates">Update Duplicate Records</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ti ti-file-import me-1"></i>Import</button></div>
            </form>
        </div>
    </div>
</div>
@endif
@push('scripts')
@include('partials.import-poll')
<script>
document.addEventListener('click', function (event) {
    var deleteButton = event.target.closest('.delete-team-btn');
    if (deleteButton) {
        document.getElementById('delete_team_form').action = deleteButton.dataset.url;
        document.getElementById('delete_team_message').textContent = 'Are you sure you want to delete "' + deleteButton.dataset.name + '"?';
    }

    var statusButton = event.target.closest('.team-status-btn');
    if (statusButton) {
        fetch(statusButton.dataset.url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ status: statusButton.dataset.status })
        }).then(function (response) {
            if (!response.ok) throw new Error('Status update failed');
            window.location.reload();
        }).catch(function (error) { console.error(error); });
    }
});

function applyTeamFilter(status) {
    fetch('{{ url('company-structure/teams/filter') }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({ status: status })
    }).then(function (response) { return response.json(); }).then(function (data) {
        document.querySelector('table.datatable tbody').innerHTML = data.html;
        var visibleBody = document.querySelector('.gridjs-tbody');
        if (visibleBody) {
            visibleBody.innerHTML = '';
            var rows = document.createElement('tbody');
            rows.innerHTML = data.html;
            rows.querySelectorAll('tr').forEach(function (row) {
                row.classList.add('gridjs-tr');
                row.querySelectorAll('td').forEach(function (cell) { cell.classList.add('gridjs-td'); });
                visibleBody.appendChild(row);
            });
        }
        var summary = document.querySelector('.gridjs-summary');
        if (summary) summary.textContent = data.count === 0 ? 'No records to show' : 'Showing 1-' + data.count + ' of ' + data.count + ' entries';
        var pages = document.querySelector('.gridjs-pages');
        if (pages) pages.innerHTML = '';
    }).catch(function (error) { console.error('Filter error:', error); });
}

document.querySelectorAll('.team-status-filter').forEach(function (button) {
    button.addEventListener('click', function () { applyTeamFilter(this.dataset.status); });
});
</script>
@endpush
@endsection
