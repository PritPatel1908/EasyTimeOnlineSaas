<div id="header-fix" class="header fixed-top">
    <nav class="navbar navbar-expand-lg p-0">
        <div class="navbar-header h4 mb-0 align-self-center d-flex">
            <a href="{{ route('admin.dashboard') }}" class="horizontal-logo align-self-center d-flex d-lg-none">
                <img src="{{ asset('admin-dist/images/logo.png') }}" alt="Pollo" width="23" class="img-fluid">
                <span class="h5 align-self-center mb-0">EASYTIME</span>
            </a>
            <a href="#" class="sidebarCollapse ml-2" id="collapse"><i class="icon-menu body-color"></i></a>
        </div>
        <div class="d-inline-block position-relative">
            <button id="tourfirst" data-toggle="dropdown" class="btn btn-primary p-2 rounded mx-3 h4 mb-0 line-height-1 d-none d-lg-block"><span class="text-white font-weight-bold h4">+</span></button>
            <div class="dropdown-menu left p-0">
                <a href="#" class="dropdown-item px-2">Create Page</a>
                <a href="#" class="dropdown-item px-2">Add New User</a>
                <a href="#" class="dropdown-item px-2">New Campaign</a>
            </div>
        </div>
        <form class="float-left d-none d-lg-block search-form">
            <div class="form-group mb-0 position-relative">
                <label for="admin-search" class="sr-only">Search administration</label>
                <input id="admin-search" type="search" class="form-control border-0 rounded bg-search pl-5" placeholder="Search tenants, domains, and users...">
                <div class="btn-search position-absolute top-0"><a href="#"><i class="h5 icon-magnifier body-color"></i></a></div>
            </div>
        </form>
        <div class="navbar-right ml-auto">
            <ul class="ml-auto p-0 m-0 list-unstyled d-flex">
                <li class="mr-1 d-inline-block my-auto"><div id="options" data-input-name="country2" data-selected-country="US"></div></li>
                <li class="dropdown align-self-center mr-1" data-notification-panel data-notification-poll-url="{{ url('/notifications/poll') }}" data-refresh-interval="15000">
                    <a href="#" class="nav-link px-2" data-toggle="dropdown">
                        <i class="icon-bell h4"></i>
                        @if ($adminNotifications->isNotEmpty())
                            <span class="badge badge-danger" data-notification-count>{{ $adminNotifications->count() }}</span>
                        @else
                            <span class="badge badge-danger" data-notification-count hidden>0</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right border py-0" data-notification-list>
                        <li><span class="dropdown-item px-2 py-2"><strong>Notifications</strong></span></li>
                        @forelse ($adminNotifications as $notification)
                            <li>
                                <div class="dropdown-item px-2 py-2">
                                    <a href="{{ data_get($notification->data, 'url', '#') }}" class="d-block"><strong>{{ data_get($notification->data, 'title', 'Notification') }}</strong><br><span class="text-muted">{{ data_get($notification->data, 'message') }}</span></a>
                                    @if (data_get($notification->data, 'download_url'))
                                        <a href="{{ data_get($notification->data, 'download_url') }}" class="btn btn-sm btn-primary mt-2">Download</a>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li><span class="dropdown-item px-2 py-2 text-muted">No new notifications</span></li>
                        @endforelse
                        <li>
                            <a href="{{ url('/notifications') }}" class="dropdown-item px-2 py-2 text-primary fw-medium">
                                View all notifications
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown user-profile d-inline-block py-1 mr-2">
                    <a href="#" class="nav-link px-2 py-0" data-toggle="dropdown">
                        <div class="media"><div class="media-body align-self-center d-none d-sm-block mr-2"><p class="mb-0 text-uppercase line-height-1"><b>John Deo</b><br><span>Admin</span></p></div><img src="{{ asset('admin-dist/images/author.jpg') }}" alt="John Deo" class="d-flex img-fluid rounded-circle" width="45"></div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right p-0"><a href="#" class="dropdown-item px-2"><span class="icon-user mr-2"></span>View Profile</a><form method="POST" action="{{ route('logout', absolute: false) }}">@csrf<button type="submit" class="dropdown-item px-2 text-danger"><span class="icon-logout mr-2"></span>Sign Out</button></form></div>
                </li>
            </ul>
        </div>
    </nav>
</div>
