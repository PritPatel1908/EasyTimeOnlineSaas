@forelse ($teams as $team)
<tr>
    <td><input id="team_{{ $team->id }}" class="form-check-input" type="checkbox"></td>
    <td><h6 class="fw-medium fs-14 mb-0">{{ $team->name }}</h6></td>
    <td>{{ $team->code }}</td>
    <td>{{ $team->email ?? '—' }}</td>
    <td>{{ $team->companies->pluck('name')->join(', ') ?: '—' }}</td>
    <td>{{ $team->locations->pluck('name')->join(', ') ?: '—' }}</td>
    <td>
        @if (\App\Support\TenantPermissions::userCan('Team', 'write'))
            <button type="button" class="btn btn-link p-0 team-status-btn" data-url="{{ url('company-structure/teams/'.$team->id.'/status') }}" data-status="{{ $team->status === 1 ? 0 : 1 }}" title="Change status">
                <span class="badge {{ $team->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $team->status === 1 ? 'Active' : 'Inactive' }}</span>
            </button>
        @else
            <span class="badge {{ $team->status === 1 ? 'badge-success' : 'badge-danger' }}">{{ $team->status === 1 ? 'Active' : 'Inactive' }}</span>
        @endif
    </td>
    <td class="text-center"><div class="action-icon d-inline-flex">
        @if (\App\Support\TenantPermissions::userCan('Team', 'read'))<a class="me-2" href="{{ url('company-structure/teams/'.$team->id) }}" title="View"><i class="ti ti-eye"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('Team', 'write'))<a class="me-2" href="{{ url('company-structure/teams/'.$team->id.'/edit') }}" title="Edit"><i class="ti ti-edit"></i></a>@endif
        @if (\App\Support\TenantPermissions::userCan('Team', 'delete'))<a href="javascript:void(0)" class="text-danger delete-team-btn" data-url="{{ url('company-structure/teams/'.$team->id) }}" data-name="{{ $team->name }}" data-bs-toggle="modal" data-bs-target="#delete_team_modal" title="Delete"><i class="ti ti-trash"></i></a>@endif
    </div></td>
</tr>
@empty
<tr><td colspan="8" class="py-4 text-center text-muted">No teams found.</td></tr>
@endforelse
