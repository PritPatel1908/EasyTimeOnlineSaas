<div class="sidebar">
    <a href="#" class="sidebarCollapse float-right h6 dropdown-menu-right mr-2 mt-2 position-absolute d-block d-lg-none"><i class="icon-close"></i></a>
    <a href="{{ route('admin.dashboard') }}" class="sidebar-logo d-flex"><img src="{{ asset('admin-dist/images/logo.png') }}" alt="EasyTime Online SaaS" width="25" class="img-fluid mr-2"><span class="h5 align-self-center mb-0">EASYTIME</span></a>
    <ul id="side-menu" class="sidebar-menu">
        <li class="active"><a href="{{ route('admin.dashboard') }}"><i class="icon-speedometer"></i>Dashboard</a></li>
        <li class="dropdown"><a href="{{ route('admin.tenants.index') }}"><i class="icon-grid"></i>Tenants</a><div><ul><li><a href="{{ route('admin.companies.index') }}"><i class="icon-briefcase"></i>Companies</a></li><li><a href="{{ route('admin.tenants.index') }}"><i class="icon-list"></i>All Tenants</a></li><li><a href="{{ route('admin.domains.index') }}"><i class="icon-globe"></i>Domains</a></li></ul></div></li>
        <li><a href="{{ route('admin.users.index') }}"><i class="icon-people"></i>Users</a></li>
        <li class="dropdown"><a href="{{ route('admin.reports') }}"><i class="icon-chart"></i>Reports</a><div><ul><li><a href="{{ route('admin.reports') }}"><i class="icon-graph"></i>Tenant Growth</a></li><li><a href="{{ route('admin.reports') }}"><i class="icon-clock"></i>Activity</a></li></ul></div></li>
        <li><a href="{{ route('admin.settings') }}"><i class="icon-settings"></i>Settings</a></li>
    </ul>
</div>
