@extends('layout.mainlayout')
@section('content')
@php($tenant = request()->route('tenant'))
@php(\Illuminate\Support\Facades\URL::defaults(['tenant' => $tenant]))
<div class="page-wrapper">
    <div class="content">
        @include('partials.flash-alerts')
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Roles & Permissions</h2>
                <nav><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard', ['tenant' => $tenant]) }}"><i class="ti ti-smart-home"></i></a></li>
                    <li class="breadcrumb-item">User Management</li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol></nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <button type="button" class="btn btn-primary d-flex align-items-center mb-2" data-bs-toggle="modal" data-bs-target="#add_role">
                    <i class="ti ti-circle-plus me-2"></i>Add Role
                </button>
                <div class="head-icons ms-2"><a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header"><i class="ti ti-chevrons-up"></i></a></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="card mb-0"><div class="card-body d-flex align-items-center"><span class="avatar avatar-lg bg-primary-transparent text-primary me-3"><i class="ti ti-shield-check fs-24"></i></span><div><p class="text-muted mb-1">Total roles</p><h4 class="mb-0">{{ $roles->total() }}</h4></div></div></div></div>
            <div class="col-md-4"><div class="card mb-0"><div class="card-body d-flex align-items-center"><span class="avatar avatar-lg bg-success-transparent text-success me-3"><i class="ti ti-users fs-24"></i></span><div><p class="text-muted mb-1">Assigned users</p><h4 class="mb-0">{{ $roles->sum('users_count') }}</h4></div></div></div></div>
            <div class="col-md-4"><div class="card mb-0"><div class="card-body d-flex align-items-center"><span class="avatar avatar-lg bg-warning-transparent text-warning me-3"><i class="ti ti-lock-access fs-24"></i></span><div><p class="text-muted mb-1">Permission coverage</p><h4 class="mb-0">{{ $roles->sum(fn ($role) => $role->permissions->count()) }}</h4></div></div></div></div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3"><div><h5 class="mb-1">Role directory</h5><p class="text-muted mb-0 fs-13">Control access to modules across this tenant workspace.</p></div><span class="badge badge-soft-primary">Tenant scope</span></div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table datatable mb-0"><thead class="thead-light"><tr><th>Role</th><th>Permissions</th><th>Users</th><th>Created</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td><div class="d-flex align-items-center"><span class="avatar avatar-md bg-primary-transparent text-primary me-2"><i class="ti ti-shield"></i></span><div><h6 class="fs-14 fw-medium mb-1">{{ $role->name }}</h6><span class="text-muted fs-12">Guard: {{ $role->guard_name }}</span></div></div></td>
                        <td><span class="badge badge-primary">{{ $role->permissions->count() }} assigned</span></td>
                        <td>{{ $role->users_count }}</td>
                        <td>{{ optional($role->created_at)->format('d M Y') }}</td>
                        <td><span class="badge {{ $role->status ? 'badge-success' : 'badge-danger' }} d-inline-flex align-items-center badge-xs"><i class="ti ti-point-filled me-1"></i>{{ $role->status ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end"><div class="action-icon d-inline-flex align-items-center">
                            <a href="{{ route('tenant.roles.permissions', ['tenant' => $tenant, 'role' => $role]) }}" class="me-2" data-bs-toggle="tooltip" title="Manage permissions"><i class="ti ti-key"></i></a>
                            <a href="#edit_role_{{ $role->id }}" class="me-2" data-bs-toggle="modal" title="Edit role"><i class="ti ti-edit"></i></a>
                            <form method="POST" action="{{ route('tenant.roles.destroy', ['tenant' => $tenant, 'role' => $role]) }}" class="d-inline" onsubmit="return confirm('Delete this role?')">@csrf @method('DELETE')<button type="submit" class="btn btn-link p-0 text-danger" title="Delete role"><i class="ti ti-trash"></i></button></form>
                        </div></td>
                    </tr>
                    <div class="modal fade" id="edit_role_{{ $role->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit role</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form method="POST" action="{{ route('tenant.roles.update', ['tenant' => $tenant, 'role' => $role]) }}">@csrf @method('PUT')<div class="modal-body"><label for="role_name_{{ $role->id }}" class="form-label">Role name</label><input id="role_name_{{ $role->id }}" name="name" value="{{ $role->name }}" class="form-control" required maxlength="100"><label for="role_status_{{ $role->id }}" class="form-label mt-3">Status</label><select id="role_status_{{ $role->id }}" name="status" class="form-control select" required><option value="1" @selected($role->status)>Active</option><option value="0" @selected(! $role->status)>Inactive</option></select></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save changes</button></div></form></div></div></div>
                @empty
                    <tr><td colspan="6" class="text-center py-5"><span class="avatar avatar-xl bg-light text-muted mb-3"><i class="ti ti-shield-off fs-28"></i></span><h6>No roles created yet</h6><p class="text-muted mb-0">Create the first role for this tenant workspace.</p></td></tr>
                @endforelse
                </tbody></table></div>
            </div>
            @if ($roles->hasPages())<div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2"><span class="text-muted fs-13">Showing {{ $roles->firstItem() }}–{{ $roles->lastItem() }} of {{ $roles->total() }} roles</span>{{ $roles->links() }}</div>@endif
        </div>
    </div>
    @include('partials.footer')
</div>

<div class="modal fade" id="add_role" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create role</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form method="POST" action="{{ route('tenant.roles.store') }}">@csrf<div class="modal-body"><label for="role_name" class="form-label">Role name</label><input id="role_name" name="name" class="form-control" placeholder="e.g. HR Manager" required maxlength="100"><label for="role_status" class="form-label mt-3">Status</label><select id="role_status" name="status" class="form-control select" required><option value="1" selected>Active</option><option value="0">Inactive</option></select><p class="text-muted fs-12 mt-2 mb-0">You can assign module permissions after creating the role.</p></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create role</button></div></form></div></div></div>
@push('scripts')
<script>
    document.querySelectorAll('.auto-dismiss-alert').forEach(function (alert) {
        var remaining = 5000;
        var timer;
        var startedAt;

        function startTimer() {
            startedAt = Date.now();
            timer = window.setTimeout(function () {
                alert.remove();
            }, remaining);
        }

        alert.addEventListener('mouseenter', function () {
            window.clearTimeout(timer);
            remaining = Math.max(0, remaining - (Date.now() - startedAt));
        });

        alert.addEventListener('mouseleave', function () {
            if (remaining > 0) {
                startTimer();
            }
        });

        startTimer();
    });
</script>
@endpush
@endsection
