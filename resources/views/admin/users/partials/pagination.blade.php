<div data-pagination-container>
    <div class="d-flex justify-content-end mb-3">
        <form method="POST" action="{{ route('admin.users.pagination') }}" data-ajax-pagination>
            @csrf
            <input type="hidden" name="page" value="{{ $users->currentPage() }}">
            <label for="user-per-page" class="mr-2">Records per page</label>
            <select id="user-per-page" name="per_page" class="form-control d-inline-block w-auto" onchange="this.form.requestSubmit()">
                @foreach ([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" @selected($users->perPage() == $option)>{{ $option }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Email</th><th>Created</th><th class="text-right">Actions</th></tr></thead><tbody>
        @forelse ($users as $user)
            <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->created_at?->format('d M Y') ?? 'Not available' }}</td><td class="text-right"><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a><form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">No users found.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-3" data-ajax-pagination-links>{{ $users->links() }}</div>
</div>
