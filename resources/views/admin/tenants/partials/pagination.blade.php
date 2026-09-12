<div data-pagination-container>
    <div class="d-flex justify-content-end mb-3">
        <form method="POST" action="{{ route('admin.tenants.pagination') }}" data-ajax-pagination>
            @csrf
            <input type="hidden" name="page" value="{{ $tenants->currentPage() }}">
            <label for="tenant-per-page" class="mr-2">Records per page</label>
            <select id="tenant-per-page" name="per_page" class="form-control d-inline-block w-auto" onchange="this.form.requestSubmit()">
                @foreach ([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($tenants->perPage() == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-responsive"><table class="table mb-0 text-nowrap"><thead><tr><th class="border-top-0">Company</th><th class="border-top-0">Subdomain slug</th><th class="border-top-0">Domains</th><th class="border-top-0">Created</th><th class="border-top-0 text-right">Actions</th></tr></thead><tbody>
        @forelse ($tenants as $tenant)
            <tr><td>{{ $tenant->name }}</td><td>{{ $tenant->id }}</td><td>{{ $tenant->domains_count }}</td><td>{{ $tenant->created_at ?? 'Not available' }}</td><td class="text-right">@if ($tenant->database_status === 'ready')<button type="button" class="btn btn-sm btn-outline-success mr-2" title="{{ $tenant->database_status_label }}" disabled><i class="{{ $tenant->database_status_icon }} mr-1"></i>{{ $tenant->database_status_label }}</button>@else<form action="{{ url('/tenants/'.$tenant->id.'/database-action') }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-outline-success mr-2" title="{{ $tenant->database_status_label }}"><i class="{{ $tenant->database_status_icon }} mr-1"></i>{{ $tenant->database_status_label }}</button></form>@endif<form action="{{ route('admin.tenants.migrate', $tenant->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-outline-info mr-2">Migrate</button></form><form action="{{ route('admin.tenants.seed', $tenant->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-outline-warning mr-2">Seed</button></form><a href="{{ route('admin.tenants.edit', $tenant->id) }}" class="btn btn-sm btn-outline-primary">Edit</a><form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tenant?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No tenants found.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-3" data-ajax-pagination-links>{{ $tenants->links() }}</div>
</div>
