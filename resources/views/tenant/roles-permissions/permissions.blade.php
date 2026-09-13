@extends('layout.mainlayout')

@section('content')
    @php
        $tenant = request()->route('tenant');
    @endphp

    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Permission matrix</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard', ['tenant' => $tenant]) }}"><i class="ti ti-smart-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('tenant.roles.index', ['tenant' => $tenant]) }}">Roles</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $role->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <a href="{{ route('tenant.roles.index', ['tenant' => $tenant]) }}" class="btn btn-light d-flex align-items-center mb-2"><i class="ti ti-arrow-left me-2"></i>Back to roles</a>
                    <div class="head-icons ms-2">
                        <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Collapse" id="collapse-header"><i class="ti ti-chevrons-up"></i></a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show auto-dismiss-alert d-flex align-items-center" role="alert">
                    <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                    <div>
                        <h5 class="mb-1">{{ $role->name }} access</h5>
                        <p class="text-muted mb-0 fs-13">Select the actions this role can perform in each workspace module.</p>
                    </div>
                    <span class="badge badge-soft-primary"><i class="ti ti-shield-check me-1"></i>{{ $role->permissions->count() }} permissions</span>
                </div>

                <form method="POST" action="{{ route('tenant.roles.permissions.update', ['tenant' => $tenant, 'role' => $role]) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table permission-table mb-0 align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="ps-4">Module</th>
                                        <th class="text-center">Allow all</th>
                                        @foreach ($actions as $action)
                                            <th class="text-center text-capitalize">{{ $action }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $module)
                                        @php
                                            $key = strtolower(str_replace(' ', '-', $module));
                                            $assigned = $role->permissions->pluck('name');
                                        @endphp

                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm bg-primary-transparent text-primary me-2">
                                                        <i class="ti ti-{{ $key === 'dashboard' ? 'layout-dashboard' : ($key === 'employees' ? 'users' : 'box') }}"></i>
                                                    </span>
                                                    <span class="text-gray-9 fw-medium">{{ $module }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-md d-inline-block">
                                                    <input id="allow_{{ $key }}" class="form-check-input allow-all" type="checkbox" data-module="{{ $key }}">
                                                    <label for="allow_{{ $key }}" class="visually-hidden">Allow all {{ $module }} permissions</label>
                                                </div>
                                            </td>

                                            @foreach ($actions as $action)
                                                @php
                                                    $permission = $key . '.' . $action;
                                                    $permissionId = $key . '_' . $action;
                                                @endphp

                                                <td class="text-center">
                                                    <div class="form-check form-check-md d-inline-block">
                                                        <input id="{{ $permissionId }}" class="form-check-input permission-{{ $key }}" type="checkbox" name="permissions[]" value="{{ $permission }}" {{ $assigned->contains($permission) ? 'checked' : '' }}>
                                                        <label for="{{ $permissionId }}" class="visually-hidden">{{ $module }} {{ $action }}</label>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                        <p class="text-muted fs-13 mb-0"><i class="ti ti-info-circle me-1"></i>Changes apply immediately to users with this role.</p>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save permissions</button>
                    </div>
                </form>
            </div>
        </div>

        @include('partials.footer')
    </div>

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

            document.querySelectorAll('.allow-all').forEach(function (toggle) {
                var module = toggle.dataset.module;
                var permissions = document.querySelectorAll('.permission-' + module);
                var syncToggle = function () {
                    toggle.checked = Array.from(permissions).every(function (permission) {
                        return permission.checked;
                    });
                };

                toggle.addEventListener('change', function () {
                    permissions.forEach(function (permission) {
                        permission.checked = toggle.checked;
                    });
                });

                permissions.forEach(function (permission) {
                    permission.addEventListener('change', syncToggle);
                });

                syncToggle();
            });
        </script>
    @endpush
@endsection

