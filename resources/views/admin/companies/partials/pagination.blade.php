<div data-pagination-container>
    <div class="d-flex justify-content-end mb-3">
        <form method="POST" action="{{ route('admin.companies.pagination') }}" data-ajax-pagination>
            @csrf
            <input type="hidden" name="page" value="{{ $companies->currentPage() }}">
            <label for="company-per-page" class="mr-2">Records per page</label>
            <select id="company-per-page" name="per_page" class="form-control d-inline-block w-auto" onchange="this.form.requestSubmit()">
                @foreach ([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($companies->perPage() == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Tenants</th><th class="text-right">Actions</th></tr></thead><tbody>
        @forelse ($companies as $company)
            <tr><td>{{ $company->name }}</td><td>{{ $company->email ?: 'Not available' }}</td><td>{{ $company->phone ?: 'Not available' }}</td><td><span class="badge {{ $company->status === 'Active' ? 'badge-success' : 'badge-secondary' }}">{{ $company->status }}</span></td><td>{{ $company->tenants_count }}</td><td class="text-right"><a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">Edit</a><form action="{{ route('admin.companies.destroy', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this company?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No companies found.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-3" data-ajax-pagination-links>{{ $companies->links() }}</div>
</div>
