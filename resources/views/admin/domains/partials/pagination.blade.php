<div data-pagination-container>
    <div class="d-flex justify-content-end mb-3">
        <form method="POST" action="{{ route('admin.domains.pagination') }}" data-ajax-pagination>
            @csrf
            <input type="hidden" name="page" value="{{ $domains->currentPage() }}">
            <label for="domain-per-page" class="mr-2">Records per page</label>
            <select id="domain-per-page" name="per_page" class="form-control d-inline-block w-auto" onchange="this.form.requestSubmit()">
                @foreach ([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($domains->perPage() == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Domain</th><th>Tenant</th><th>Status</th><th>Created</th><th class="text-right">Actions</th></tr></thead><tbody>
        @forelse ($domains as $domain)
            <tr><td>{{ $domain->domain }}</td><td>{{ $domain->tenant_id }}</td><td><span class="badge {{ $domain->status === 'Active' ? 'badge-success' : 'badge-secondary' }}">{{ $domain->status }}</span></td><td>{{ $domain->created_at ?? 'Not available' }}</td><td class="text-right"><a href="{{ route('admin.domains.edit', $domain->id) }}" class="btn btn-sm btn-outline-primary">Edit</a><form action="{{ route('admin.domains.destroy', $domain->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this domain?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No domains found.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-3" data-ajax-pagination-links>{{ $domains->links() }}</div>
</div>
