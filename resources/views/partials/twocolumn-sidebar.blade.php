<!-- Two Col Sidebar -->
@php
	$canViewCompanyStructure =
		\App\Support\TenantPermissions::userCan('Company', 'read') ||
		\App\Support\TenantPermissions::userCan('Location', 'read') ||
		\App\Support\TenantPermissions::userCan('Department', 'read') ||
		\App\Support\TenantPermissions::userCan('SubDepartment', 'read') ||
		\App\Support\TenantPermissions::userCan('Unit', 'read') ||
		\App\Support\TenantPermissions::userCan('Team', 'read') ||
		\App\Support\TenantPermissions::userCan('CanteenFacility', 'read');
@endphp
<div class="two-col-sidebar" id="two-col-sidebar">
	<div class="sidebar sidebar-twocol">
		<div class="twocol-mini">
			<a href="{{url('dashboard')}}" class="logo-small">
				<img src="{{URL::asset('build/img/logo-small.svg')}}" alt="Logo">
			</a>
			<div class="sidebar-left slimscroll">
				<div class="nav flex-column align-items-center nav-pills" id="sidebar-tabs" role="tablist"
					aria-orientation="vertical">
					<a href="#"
						class="nav-link {{ Request::is('index', '/', 'employee-dashboard', 'deals-dashboard', 'leads-dashboard', 'hr-dashboard', 'payroll-dashboard', 'recruitment-dashboard', 'attendance-dashboard', 'finance-dashboard', 'it-admin-dashboard', 'asset-dashboard', 'help-desk-dashboard') ? 'active ' : '' }}"
						title="Dashboard" data-bs-toggle="tab" data-bs-target="#dashboard">
						<i class="ti ti-smart-home"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('chat', 'call', 'voice-call', 'video-call', 'outgoing-call', 'incoming-call', 'call-history', 'calendar', 'email', 'email-reply', 'todo', 'todo-list', 'notes', 'social-feed', 'file-manager', 'kanban-view', 'invoices', 'add-invoices', 'edit-invoices', 'invoice-details') ? 'active' : '' }}"
						title="Apps" data-bs-toggle="tab" data-bs-target="#application">
						<i class="ti ti-layout-grid-add"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('dashboard', 'companies', 'subscription', 'packages', 'packages-grid', 'domain', 'purchase-transaction', 'tenant-usage-metrics', 'tenant-support-tickets', 'tenant-ticket-details', 'agents', 'sla-policies', 'escalation-rules') ? 'active' : '' }}"
						title="Super Admin" data-bs-toggle="tab" data-bs-target="#super-admin">
						<i class="ti ti-user-star"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('ai-attendance-insights', 'ai-payroll-forecast', 'ai-hiring-forecast', 'ai-team-performance-insights', 'ai-configuration') ? 'active' : '' }}"
						title="AI Center" data-bs-toggle="tab" data-bs-target="#ai-center">
						<i class="ti ti-sparkles"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('company-structure/*') ? 'active' : '' }}"
						title="Company Structure" data-bs-toggle="tab" data-bs-target="#company-structure">
						<i class="ti ti-building-estate"></i>
					</a>
					<a href="#"
						class="nav-link  {{ Request::is('layout-horizontal', 'layout-detached', 'layout-modern', 'layout-two-column', 'layout-hovered', 'layout-box', 'layout-horizontal-single', 'layout-horizontal-overlay', 'layout-horizontal-box', 'layout-horizontal-sidemenu', 'layout-vertical-transparent', 'layout-without-header', 'layout-rtl', 'layout-dark') ? 'active' : '' }}"
						title="Layout" data-bs-toggle="tab" data-bs-target="#layout">
						<i class="ti ti-layout-board-split"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('clients-grid', 'projects-grid', 'projects', 'project-details', 'tasks', 'task-board') ? 'active ' : '' }}"
						title="Projects" data-bs-toggle="tab" data-bs-target="#projects">
						<i class="ti ti-users-group"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('contacts-grid', 'companies-grid', 'deals-grid', 'leads-grid', 'leads-details', 'leads', 'pipeline', 'analytics', 'activity') ? ' show active ' : '' }}"
						title="Crm" data-bs-toggle="tab" data-bs-target="#crm">
						<i class="ti ti-user-shield"></i>
					</a>
					<a href="#"
						class="nav-link  {{ Request::is('employees', 'employees-grid', 'employee-details', 'departments', 'designations', 'policy', 'tickets', 'ticket-details', 'ticket-automation', 'ticket-reports', 'holidays', 'leaves', 'leaves-employee', 'leave-settings', 'attendance-admin', 'attendance-employee', 'timesheets', 'schedule-timing', 'overtime', 'holiday-calendar', 'shift-swap-requests', 'work-from-home', 'performance-indicator', 'performance-review', 'performance-appraisal', 'goal-tracking', 'goal-type', 'training', 'trainers', 'training-type', 'certification-tracking', 'learning-analytics', 'probation-management', 'notice-period-tracker', 'promotion', 'resignation', 'termination', 'job-grid', 'job-list', 'candidates-grid', 'candidates-kanban', 'candidates', 'refferals', 'resume-parsing', 'campus-hiring') ? '  active ' : '' }}"
						title="Hrm" data-bs-toggle="tab" data-bs-target="#hrm">
						<i class="ti ti-user"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('estimates', 'invoices', 'payments', 'expenses', 'provident-fund', 'taxes', 'categories', 'budgets', 'budget-expenses', 'budget-revenues', 'employee-salary', 'payslip', 'payroll', 'payroll-overtime', 'payroll-deduction') ? ' show active ' : '' }}"
						title="Finance" data-bs-toggle="tab" data-bs-target="#finance">
						<i class="ti ti-shopping-cart-dollar"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('assets', 'asset-categories', 'knowledgebase', 'knowledgebase-details', 'knowledgebase-view', 'users', 'roles-permissions', 'permission', 'expenses-report', 'invoice-report', 'payment-report', 'project-report', 'task-report', 'user-report', 'employee-report', 'payslip-report', 'attendance-report', 'leave-report', 'daily-report', 'profile-settings', 'security-settings', 'notification-settings', 'connected-apps', 'business-settings', 'seo-settings', 'localization-settings', 'prefixes', 'preferences', 'appearance', 'language', 'add-language', 'language-web', 'authentication-settings', 'ai-settings', 'salary-settings', 'approval-settings', 'invoice-settings', 'leave-type', 'custom-fields', 'email-settings', 'email-template', 'sms-settings', 'sms-template', 'otp-settings', 'gdpr', 'maintenance-mode', 'payment-gateways', 'tax-rates', 'currencies', 'custom-css', 'custom-js', 'cronjob', 'storage-settings', 'ban-ip-address', 'backup', 'clear-cache') ? ' active' : '' }}"
						title="Administration" data-bs-toggle="tab" data-bs-target="#administration">
						<i class="ti ti-cash"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('pages', 'blogs', 'blog-categories', 'blog-comments', 'blog-tags', 'countries', 'states', 'cities', 'testimonials', 'faq') ? 'active' : '' }}"
						title="Content" data-bs-toggle="tab" data-bs-target="#content">
						<i class="ti ti-license"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('starter', 'profile', 'gallery', 'search-result', 'timeline', 'pricing', 'coming-soon', 'under-maintenance', 'under-construction', 'api-keys', 'privacy-policy', 'terms-condition') ? 'active' : '' }}"
						title="Pages" data-bs-toggle="tab" data-bs-target="#pages">
						<i class="ti ti-page-break"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('login', 'login-2', 'login-3', 'register', 'register-2', 'register-3', 'forgot-password', 'forgot-password-2', 'forgot-password-3', 'reset-password', 'reset-password-2', 'reset-password-3', 'email-verification', 'email-verification-2', 'email-verification-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3', 'lock-screen', 'error-404', 'error-500') ? '  active' : '' }}"
						title="Authentication" data-bs-toggle="tab" data-bs-target="#authentication">
						<i class="ti ti-lock-check"></i>
					</a>
					<a href="#"
						class="nav-link {{ Request::is('ui-alerts', 'ui-accordion', 'ui-avatar', 'ui-badges', 'ui-buttons', 'ui-buttons-group', 'ui-breadcrumb', 'ui-cards', 'ui-carousel', 'ui-dropdowns', 'ui-grid', 'ui-images', 'ui-modals', 'ui-offcanvas', 'ui-pagination', 'ui-popovers', 'ui-progress', 'ui-placeholders', 'ui-spinner', 'ui-nav-tabs', 'ui-toasts', 'ui-tooltips', 'ui-typography', 'ui-collapse', 'ui-links', 'ui-list-groups', 'ui-ratio', 'ui-clipboard', 'ui-dragula', 'ui-sweetalerts', 'ui-lightbox', 'ui-scrollbar', 'form-basic-inputs', 'form-checkbox-radios', 'form-input-groups', 'form-grid-gutters', 'form-select', 'form-mask', 'form-fileupload', 'form-horizontal', 'form-vertical', 'form-floating-labels', 'form-validation', 'form-wizard', 'form-select2', 'tables-basic', 'data-tables', 'chart-apex', 'chart-js', 'chart-morris', 'chart-flot', 'chart-c3', 'icon-fontawesome', 'icon-feather', 'icon-ionic', 'icon-material', 'icon-pe7', 'icon-simpleline', 'icon-themify', 'icon-weather', 'icon-typicon', 'icon-flag') ? 'active' : '' }}"
						title="UI Elements" data-bs-toggle="tab" data-bs-target="#ui-elements">
						<i class="ti ti-ux-circle"></i>
					</a>
					<a href="#" class="nav-link" title="Extras" data-bs-toggle="tab" data-bs-target="#extras">
						<i class="ti ti-vector-triangle"></i>
					</a>
				</div>
			</div>
		</div>
		<div class="sidebar-right">
			<div class="sidebar-logo mb-4">
				<a href="{{url('dashboard')}}" class="logo logo-normal">
					<img src="{{URL::asset('build/img/logo.svg')}}" alt="Logo">
				</a>
				<a href="{{url('dashboard')}}" class="dark-logo">
					<img src="{{URL::asset('build/img/logo-white.svg')}}" alt="Logo">
				</a>
			</div>
			<div class="sidebar-scroll">
				<h6 class="mb-3">Welcome to SmartHR</h6>
				<div class="text-center rounded bg-light p-3 mb-4">
					<div class="avatar avatar-lg online mb-3">
						<img src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}" alt="Img"
							class="img-fluid rounded-circle">
					</div>
					<h6 class="fs-12 fw-normal mb-1">Adrian Herman</h6>
					<p class="fs-10">System Admin</p>
				</div>
				<div class="tab-content" id="v-pills-tabContent">
					<div class="tab-pane fade {{ Request::is('index', '/', 'employee-dashboard', 'deals-dashboard', 'leads-dashboard', 'hr-dashboard', 'payroll-dashboard', 'recruitment-dashboard', 'attendance-dashboard', 'finance-dashboard', 'it-admin-dashboard', 'asset-dashboard', 'help-desk-dashboard') ? ' show active ' : '' }}"
						id="dashboard">
						<ul>
							<li class="menu-title"><span>MAIN MENU</span></li>
							<li><a href="{{url('index')}}" class="{{ Request::is('index', '/') ? 'active' : '' }}">Admin
									Dashboard</a></li>
							<li><a href="{{url('employee-dashboard')}}"
									class="{{ Request::is('employee-dashboard') ? 'active' : '' }}">Employee
									Dashboard</a></li>
							<li><a href="{{url('deals-dashboard')}}"
									class="{{ Request::is('deals-dashboard') ? 'active' : '' }}">Deals Dashboard</a>
							</li>
							<li><a href="{{url('leads-dashboard')}}"
									class="{{ Request::is('leads-dashboard') ? 'active' : '' }}">Leads Dashboard</a>
							</li>
							<li><a href="{{url('hr-dashboard')}}"
									class="{{ Request::is('hr-dashboard') ? 'active' : '' }}">HR Dashboard</a></li>
							<li><a href="{{url('payroll-dashboard')}}"
									class="{{ Request::is('payroll-dashboard') ? 'active' : '' }}">Payroll Dashboard</a>
							</li>
							<li><a href="{{url('recruitment-dashboard')}}"
									class="{{ Request::is('recruitment-dashboard') ? 'active' : '' }}">Recruitment
									Dashboard</a></li>
							<li><a href="{{url('attendance-dashboard')}}"
									class="{{ Request::is('attendance-dashboard') ? 'active' : '' }}">Attendance
									Dashboard</a></li>
							<li><a href="{{url('finance-dashboard')}}"
									class="{{ Request::is('finance-dashboard') ? 'active' : '' }}">Finance Dashboard</a>
							</li>
							<li><a href="{{url('it-admin-dashboard')}}"
									class="{{ Request::is('it-admin-dashboard') ? 'active' : '' }}">IT Admin
									Dashboard</a></li>
							<li><a href="{{url('asset-dashboard')}}"
									class="{{ Request::is('asset-dashboard') ? 'active' : '' }}">Asset Dashboard</a>
							</li>
							<li><a href="{{url('help-desk-dashboard')}}"
									class="{{ Request::is('help-desk-dashboard') ? 'active' : '' }}">Help Desk
									Dashboard</a></li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('chat', 'voice-call', 'video-call', 'outgoing-call', 'incoming-call', 'call-history', 'calendar', 'email', 'email-reply', 'todo', 'todo-list', 'notes', 'social-feed', 'file-manager', 'kanban-view', 'invoices', 'add-invoices', 'edit-invoices', 'invoice-details') ? ' show active ' : '' }}"
						id="application">
						<ul>
							<li class="menu-title"><span>APPLICATION</span></li>
							<li><a href="{{url('voice-call')}}"
									class="{{ Request::is('voice-call') ? 'active' : '' }}">Voice Call</a></li>
							<li><a href="{{url('video-call')}}"
									class="{{ Request::is('video-call') ? 'active' : '' }}">Video Call</a></li>
							<li><a href="{{url('outgoing-call')}}"
									class="{{ Request::is('outgoing-call') ? 'active' : '' }}">Outgoing Call</a></li>
							<li><a href="{{url('incoming-call')}}"
									class="{{ Request::is('incoming-call') ? 'active' : '' }}">Incoming Call</a></li>
							<li><a href="{{url('call-history')}}"
									class="{{ Request::is('call-history') ? 'active' : '' }}">Call History</a></li>
							<li><a href="{{url('calendar')}}"
									class="{{ Request::is('calendar') ? 'active' : '' }}">Calendar</a></li>
							<li><a href="{{url('email')}}"
									class="{{ Request::is('email', 'email-reply') ? 'active' : '' }}">Email</a></li>
							<li><a href="{{url('todo')}}"
									class="{{ Request::is('todo', 'todo-list') ? 'active' : '' }}">To Do</a></li>
							<li><a href="{{url('notes')}}" class="{{ Request::is('notes') ? 'active' : '' }}">Notes</a>
							</li>
							<li><a href="{{url('file-manager')}}"
									class="{{ Request::is('file-manager') ? 'active' : '' }}">File Manager</a></li>
							<li><a href="{{url('kanban-view')}}"
									class="{{ Request::is('kanban-view') ? 'active' : '' }}">Kanban</a></li>
							<li><a href="{{url('invoices')}}"
									class="{{ Request::is('invoices', 'add-invoices', 'edit-invoices', 'invoice-details') ? 'active' : '' }}">Invoices</a>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('dashboard', 'companies', 'subscription', 'packages', 'packages-grid', 'domain', 'purchase-transaction', 'tenant-usage-metrics', 'tenant-support-tickets', 'tenant-ticket-details', 'agents', 'sla-policies', 'escalation-rules') ? ' show active' : '' }}"
						id="super-admin">
						<ul>
							<li class="menu-title"><span>SUPER ADMIN</span></li>
							<li><a href="{{url('dashboard')}}"
									class="{{ Request::is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
							<li><a href="{{url('companies')}}"
									class="{{ Request::is('companies') ? 'active' : '' }}">Companies</a></li>
							<li><a href="{{url('subscription')}}"
									class="{{ Request::is('subscription') ? 'active' : '' }}">Subscriptions</a></li>
							<li><a href="{{url('packages')}}"
									class="{{ Request::is('packages', 'packages-grid') ? 'active' : '' }}">Packages</a>
							</li>
							<li><a href="{{url('domain')}}"
									class="{{ Request::is('domain') ? 'active' : '' }}">Domain</a></li>
							<li><a href="{{url('purchase-transaction')}}"
									class="{{ Request::is('purchase-transaction') ? 'active' : '' }}">Purchase
									Transaction</a></li>
							<li><a href="{{url('tenant-usage-metrics')}}"
									class="{{ Request::is('tenant-usage-metrics') ? 'active' : '' }}">Tenant Usage
									Metrics</a></li>
							<li><a href="{{url('tenant-support-tickets')}}"
									class="{{ Request::is('tenant-support-tickets', 'tenant-ticket-details') ? 'active' : '' }}">Tenant
									Support Tickets</a></li>
							<li class="submenu submenu-two">
								<a href="#"
									class="{{ Request::is('agents', 'sla-policies', 'escalation-rules') ? 'active subdrop' : '' }}">Tickets<span
										class="menu-arrow inside-submenu"></span></a>
								<ul>
									<li><a href="{{url('agents')}}"
											class="{{ Request::is('agents') ? 'active' : '' }}">Agents</a></li>
									<li><a href="{{url('sla-policies')}}"
											class="{{ Request::is('sla-policies') ? 'active' : '' }}">SLA Policies</a>
									</li>
									<li><a href="{{url('escalation-rules')}}"
											class="{{ Request::is('escalation-rules') ? 'active' : '' }}">Escalation
											Rules</a></li>
								</ul>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('ai-attendance-insights', 'ai-payroll-forecast', 'ai-hiring-forecast', 'ai-team-performance-insights', 'ai-configuration') ? 'show active' : '' }}"
						id="ai-center">
						<ul>
							<li class="menu-title"><span>AI Center</span></li>
							<li><a href="{{url('ai-attendance-insights')}}"
									class="{{ Request::is('ai-attendance-insights') ? 'active' : '' }}">AI Attendance
									Insights</a></li>
							<li><a href="{{url('ai-payroll-forecast')}}"
									class="{{ Request::is('ai-payroll-forecast') ? 'active' : '' }}">AI Payroll
									Forecast</a></li>
							<li><a href="{{url('ai-hiring-forecast')}}"
									class="{{ Request::is('ai-hiring-forecast') ? 'active' : '' }}">AI Hiring
									Forecast</a></li>
							<li><a href="{{url('ai-team-performance-insights')}}"
									class="{{ Request::is('ai-team-performance-insights') ? 'active' : '' }}">AI Team
									Performance Insights</a></li>
							<li><a href="{{url('ai-configuration')}}"
									class="{{ Request::is('ai-configuration') ? 'active' : '' }}">AI Settings</a></li>
						</ul>
					</div>
					@if ($canViewCompanyStructure)
					<div class="tab-pane fade {{ Request::is('company-structure/*') ? ' show active' : '' }}"
						id="company-structure">
						<ul>
							<li class="menu-title"><span>COMPANY STRUCTURE</span></li>
							@if (\App\Support\TenantPermissions::userCan('Company', 'read'))<li><a href="{{url('company-structure/companies')}}"
									class="{{ Request::is('company-structure/companies*') ? 'active' : '' }}">Companies</a></li>@endif
							@if (\App\Support\TenantPermissions::userCan('Location', 'read'))<li><a href="{{url('company-structure/locations')}}"
									class="{{ Request::is('company-structure/locations') ? 'active' : '' }}">Locations</a></li>@endif
							@if (\App\Support\TenantPermissions::userCan('Department', 'read'))<li><a href="{{url('company-structure/departments')}}"
									class="{{ Request::is('company-structure/departments') ? 'active' : '' }}">Departments</a></li>@endif
							@if (\App\Support\TenantPermissions::userCan('SubDepartment', 'read'))<li><a href="{{url('company-structure/sub-departments')}}"
									class="{{ Request::is('company-structure/sub-departments*') ? 'active' : '' }}">Sub Departments</a></li>@endif
							@if (\App\Support\TenantPermissions::userCan('Unit', 'read'))<li><a href="{{url('company-structure/units')}}"
									class="{{ Request::is('company-structure/units') ? 'active' : '' }}">Unit (Mill)</a></li>@endif
							@if (\App\Support\TenantPermissions::userCan('Team', 'read'))<li><a href="{{url('company-structure/teams')}}"
									class="{{ Request::is('company-structure/teams*') ? 'active' : '' }}">Team</a></li>@endif
							@if (\App\Support\TenantPermissions::userCan('CanteenFacility', 'read'))<li><a href="{{url('company-structure/canteen-facilities')}}"
									class="{{ Request::is('company-structure/canteen-facilities*') ? 'active' : '' }}">Canteen Facility</a></li>@endif
						</ul>
					</div>
					@endif
					<div class="tab-pane fade {{ Request::is('layout-horizontal', 'layout-detached', 'layout-modern', 'layout-two-column', 'layout-hovered', 'layout-box', 'layout-horizontal-single', 'layout-horizontal-overlay', 'layout-horizontal-box', 'layout-horizontal-sidemenu', 'layout-vertical-transparent', 'layout-without-header', 'layout-rtl', 'layout-dark') ? ' show active' : '' }}"
						id="layout">
						<ul>
							<li class="menu-title"><span>LAYOUT</span></li>
							<li><a href="{{url('layout-horizontal')}}"
									class="{{ Request::is('layout-horizontal') ? 'active' : '' }}"><span>Horizontal</span></a>
							</li>
							<li><a href="{{url('layout-detached')}}"
									class="{{ Request::is('layout-detached') ? 'active' : '' }}"><span>Detached</span></a>
							</li>
							<li><a href="{{url('layout-modern')}}"
									class="{{ Request::is('layout-modern') ? 'active' : '' }}"><span>Modern</span></a>
							</li>
							<li><a href="{{url('layout-two-column')}}"
									class="{{ Request::is('layout-two-column') ? 'active' : '' }}"><span>Two Column
									</span></a></li>
							<li><a href="{{url('layout-hovered')}}"
									class="{{ Request::is('layout-hovered') ? 'active' : '' }}"><span>Hovered</span></a>
							</li>
							<li><a href="{{url('layout-box')}}"
									class="{{ Request::is('layout-box') ? 'active' : '' }}"><span>Boxed</span></a></li>
							<li><a href="{{url('layout-horizontal-single')}}"
									class="{{ Request::is('layout-horizontal-single') ? 'active' : '' }}"><span>Horizontal
										Single</span></a></li>
							<li><a href="{{url('layout-horizontal-overlay')}}"
									class="{{ Request::is('layout-horizontal-overlay') ? 'active' : '' }}"><span>Horizontal
										Overlay</span></a></li>
							<li><a href="{{url('layout-horizontal-box')}}"
									class="{{ Request::is('layout-horizontal-box') ? 'active' : '' }}"><span>Horizontal
										Box</span></a></li>
							<li><a href="{{url('layout-horizontal-sidemenu')}}"
									class="{{ Request::is('layout-horizontal-sidemenu') ? 'active' : '' }}"><span>Menu
										Aside</span></a></li>
							<li><a href="{{url('layout-vertical-transparent')}}"
									class="{{ Request::is('layout-vertical-transparent') ? 'active' : '' }}"><span>Transparent</span></a>
							</li>
							<li><a href="{{url('layout-without-header')}}"
									class="{{ Request::is('layout-without-header') ? 'active' : '' }}"><span>Without
										Header</span></a></li>
							<li><a href="{{url('layout-rtl')}}"
									class="{{ Request::is('layout-rtl') ? 'active' : '' }}"><span>RTL</span></a></li>
							<li><a href="{{url('layout-dark')}}"
									class="{{ Request::is('layout-dark') ? 'active' : '' }}"><span>Dark</span></a></li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('clients-grid', 'projects-grid', 'projects', 'project-details', 'tasks', 'task-board') ? 'show active ' : '' }}"
						id="projects">
						<ul>
							<li class="menu-title"><span>PROJECTS</span></li>
							<li><a href="{{url('clients-grid')}}"
									class="{{ Request::is('clients-grid') ? 'active' : '' }}">Clients</a></li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('projects-grid', 'projects', 'project-details', 'tasks', 'task-board') ? 'active subdrop' : '' }}"><span>Projects</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('projects-grid')}}"
											class="{{ Request::is('projects-grid', 'projects', 'project-details') ? 'active' : '' }}">Projects</a>
									</li>
									<li><a href="{{url('tasks')}}"
											class="{{ Request::is('tasks') ? 'active' : '' }}">Tasks</a></li>
									<li><a href="{{url('task-board')}}"
											class="{{ Request::is('task-board') ? 'active' : '' }}">Task Board</a></li>
								</ul>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('contacts-grid', 'companies-grid', 'deals-grid', 'leads-grid', 'leads-details', 'leads', 'pipeline', 'analytics', 'activity') ? ' show active ' : '' }}"
						id="crm">
						<ul>
							<li class="menu-title"><span>CRM</span></li>
							<li><a href="{{url('contacts-grid')}}"
									class="{{ Request::is('contacts-grid') ? 'active' : '' }}"><span>Contacts</span></a>
							</li>
							<li><a href="{{url('companies-grid')}}"
									class="{{ Request::is('companies-grid') ? 'active' : '' }}"><span>Companies</span></a>
							</li>
							<li><a href="{{url('deals-grid')}}"
									class="{{ Request::is('deals-grid') ? 'active' : '' }}"><span>Deals</span></a></li>
							<li><a href="{{url('leads-grid')}}"
									class="{{ Request::is('leads-grid', 'leads-details', 'leads') ? 'active' : '' }}"><span>Leads</span></a>
							</li>
							<li><a href="{{url('pipeline')}}"
									class="{{ Request::is('pipeline') ? 'active' : '' }}"><span>Pipeline</span></a></li>
							<li><a href="{{url('analytics')}}"
									class="{{ Request::is('analytics') ? 'active' : '' }}"><span>Analytics</span></a>
							</li>
							<li><a href="{{url('activity')}}"
									class="{{ Request::is('activity') ? 'active' : '' }}"><span>Activities</span></a>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('employees', 'employees-grid', 'employee-details', 'departments', 'designations', 'policy', 'tickets', 'ticket-details', 'ticket-automation', 'ticket-reports', 'holidays', 'leaves', 'leaves-employee', 'leave-settings', 'attendance-admin', 'attendance-employee', 'timesheets', 'schedule-timing', 'overtime', 'holiday-calendar', 'shift-swap-requests', 'work-from-home', 'performance-indicator', 'performance-review', 'performance-appraisal', 'goal-tracking', 'goal-type', 'training', 'trainers', 'training-type', 'certification-tracking', 'learning-analytics', 'probation-management', 'notice-period-tracker', 'promotion', 'resignation', 'termination', 'job-grid', 'job-list', 'candidates-grid', 'candidates-kanban', 'candidates', 'refferals', 'resume-parsing', 'campus-hiring') ? ' show active ' : '' }}"
						id="hrm">
						<ul>
							<li class="menu-title"><span>HRM</span></li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('employees', 'employees-grid', 'employee-details', 'departments', 'designations', 'policy') ? 'active subdrop' : '' }}"><span>Employees</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('employees')}}"
											class="{{ Request::is('employees') ? 'active' : '' }}">Employee Lists</a>
									</li>
									<li><a href="{{url('employees-grid')}}"
											class="{{ Request::is('employees-grid') ? 'active' : '' }}">Employee
											Grid</a></li>
									<li><a href="{{url('employee-details')}}"
											class="{{ Request::is('employee-details') ? 'active' : '' }}">Employee
											Details</a></li>
									<li><a href="{{url('departments')}}"
											class="{{ Request::is('departments') ? 'active' : '' }}">Departments</a>
									</li>
									<li><a href="{{url('designations')}}"
											class="{{ Request::is('designations') ? 'active' : '' }}">Designations</a>
									</li>
									<li><a href="{{url('policy')}}"
											class="{{ Request::is('policy') ? 'active' : '' }}">Policies</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('tickets', 'ticket-details', 'ticket-automation', 'ticket-reports') ? 'active subdrop' : '' }}"><span>Tickets</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('tickets')}}"
											class="{{ Request::is('tickets') ? 'active' : '' }}">Tickets</a></li>
									<li><a href="{{url('ticket-details')}}"
											class="{{ Request::is('ticket-details') ? 'active' : '' }}">Ticket
											Details</a></li>
									<li><a href="{{url('ticket-automation')}}"
											class="{{ Request::is('ticket-automation') ? 'active' : '' }}">Ticket
											Automation</a></li>
									<li><a href="{{url('ticket-reports')}}"
											class="{{ Request::is('ticket-reports') ? 'active' : '' }}">Ticket
											Reports</a></li>
								</ul>
							</li>
							<li><a href="{{url('holidays')}}"
									class="{{ Request::is('holidays') ? 'active' : '' }}"><span>Holidays</span></a></li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('leaves', 'leaves-employee', 'leave-settings', 'attendance-admin', 'attendance-employee', 'timesheets', 'schedule-timing', 'overtime', 'holiday-calendar', 'shift-swap-requests', 'work-from-home') ? 'active subdrop' : '' }}"><span>Attendance</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);"
											class="{{ Request::is('leaves', 'leaves-employee', 'leave-settings') ? 'active subdrop' : '' }}">Leaves<span
												class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{url('leaves')}}"
													class="{{ Request::is('leaves') ? 'active' : '' }}">Leaves
													(Admin)</a></li>
											<li><a href="{{url('leaves-employee')}}"
													class="{{ Request::is('leaves-employee') ? 'active' : '' }}">Leave
													(Employee)</a></li>
											<li><a href="{{url('leave-settings')}}"
													class="{{ Request::is('leave-settings') ? 'active' : '' }}">Leave
													Settings</a></li>
										</ul>
									</li>
									<li><a href="{{url('attendance-admin')}}"
											class="{{ Request::is('attendance-admin') ? 'active' : '' }}">Attendance
											(Admin)</a></li>
									<li><a href="{{url('attendance-employee')}}"
											class="{{ Request::is('attendance-employee') ? 'active' : '' }}">Attendance
											(Employee)</a></li>
									<li><a href="{{url('timesheets')}}"
											class="{{ Request::is('timesheets') ? 'active' : '' }}">Timesheets</a></li>
									<li><a href="{{url('schedule-timing')}}"
											class="{{ Request::is('schedule-timing') ? 'active' : '' }}">Shift &
											Schedule</a></li>
									<li><a href="{{url('shift-swap-requests')}}"
											class="{{ Request::is('shift-swap-requests') ? 'active' : '' }}">Shift Swap
											Requests</a></li>
									<li><a href="{{url('overtime')}}"
											class="{{ Request::is('overtime') ? 'active' : '' }}">Overtime</a></li>
									<li><a href="{{url('holiday-calendar')}}"
											class="{{ Request::is('holiday-calendar') ? 'active' : '' }}">Holiday
											Calendar</a></li>
									<li><a href="{{url('work-from-home')}}"
											class="{{ Request::is('work-from-home') ? 'active' : '' }}">WFH
											Management</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('performance-indicator', 'performance-review', 'performance-appraisal', 'goal-tracking', 'goal-type') ? 'active subdrop' : '' }}"><span>Performance</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('performance-indicator')}}"
											class="{{ Request::is('performance-indicator') ? 'active' : '' }}">Performance
											Indicator</a></li>
									<li><a href="{{url('performance-review')}}"
											class="{{ Request::is('performance-review') ? 'active' : '' }}">Performance
											Review</a></li>
									<li><a href="{{url('performance-appraisal')}}"
											class="{{ Request::is('performance-appraisal') ? 'active' : '' }}">Performance
											Appraisal</a></li>
									<li><a href="{{url('goal-tracking')}}"
											class="{{ Request::is('goal-tracking') ? 'active' : '' }}">Goal List</a>
									</li>
									<li><a href="{{url('goal-type')}}"
											class="{{ Request::is('goal-type') ? 'active' : '' }}">Goal Type</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('training', 'trainers', 'training-type', 'certification-tracking', 'learning-analytics') ? 'active subdrop' : '' }}"><span>Training</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('training')}}"
											class="{{ Request::is('training') ? 'active' : '' }}">Training List</a></li>
									<li><a href="{{url('trainers')}}"
											class="{{ Request::is('trainers') ? 'active' : '' }}">Trainers</a></li>
									<li><a href="{{url('training-type')}}"
											class="{{ Request::is('training-type') ? 'active' : '' }}">Training Type</a>
									</li>
									<li><a href="{{url('certification-tracking')}}"
											class="{{ Request::is('certification-tracking') ? 'active' : '' }}"><span>Certification
												Tracking</span></a></li>
									<li><a href="{{url('learning-analytics')}}"
											class="{{ Request::is('learning-analytics') ? 'active' : '' }}">Learning
											Analytics</a></li>
								</ul>
							</li>
							<li><a href="{{url('probation-management')}}"
									class="{{ Request::is('probation-management') ? 'active' : '' }}"><span>Probation
										Management</span></a></li>
							<li><a href="{{url('notice-period-tracker')}}"
									class="{{ Request::is('notice-period-tracker') ? 'active' : '' }}"><span>Notice
										Period Tracker</span></a></li>
							<li><a href="{{url('promotion')}}"
									class="{{ Request::is('promotion') ? 'active' : '' }}"><span>Promotion</span></a>
							</li>
							<li><a href="{{url('resignation')}}"
									class="{{ Request::is('resignation') ? 'active' : '' }}"><span>Resignation</span></a>
							</li>
							<li><a href="{{url('termination')}}"
									class="{{ Request::is('termination') ? 'active' : '' }}"><span>Termination</span></a>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('job-grid', 'job-list', 'candidates-grid', 'candidates-kanban', 'candidates', 'refferals', 'resume-parsing', 'campus-hiring') ? 'active subdrop' : '' }}"><span>Recruitment</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('job-grid')}}"
											class="{{ Request::is('job-grid', 'job-list') ? 'active' : '' }}">Jobs</a>
									</li>
									<li><a href="{{url('candidates-grid')}}"
											class="{{ Request::is('candidates-grid', 'candidates-kanban', 'candidates') ? 'active' : '' }}">Candidates</a>
									</li>
									<li><a href="{{url('refferals')}}"
											class="{{ Request::is('refferals') ? 'active' : '' }}">Referrals</a></li>
									<li><a href="{{url('resume-parsing')}}"
											class="{{ Request::is('resume-parsing') ? 'active' : '' }}">Resume
											Parsing</a></li>
									<li><a href="{{url('campus-hiring')}}"
											class="{{ Request::is('campus-hiring') ? 'active' : '' }}">Campus Hiring</a>
									</li>
								</ul>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('estimates', 'invoice', 'payments', 'expenses', 'provident-fund', 'taxes', 'categories', 'budgets', 'budget-expenses', 'budget-revenues', 'employee-salary', 'payslip', 'payroll', 'payroll-overtime', 'payroll-deduction') ? ' show active ' : '' }}"
						id="finance">
						<ul>
							<li class="menu-title"><span>FINANCE & ACCOUNTS</span></li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('estimates', 'invoice', 'payments', 'expenses', 'provident-fund', 'taxes') ? 'active subdrop' : '' }}"><span>Sales</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('estimates')}}"
											class="{{ Request::is('estimates') ? 'active' : '' }}">Estimates</a></li>
									<li><a href="{{url('invoice')}}"
											class="{{ Request::is('invoice') ? 'active' : '' }}">Invoices</a></li>
									<li><a href="{{url('payments')}}"
											class="{{ Request::is('payments') ? 'active' : '' }}">Payments</a></li>
									<li><a href="{{url('expenses')}}"
											class="{{ Request::is('expenses') ? 'active' : '' }}">Expenses</a></li>
									<li><a href="{{url('provident-fund')}}"
											class="{{ Request::is('provident-fund') ? 'active' : '' }}">Provident
											Fund</a></li>
									<li><a href="{{url('taxes')}}"
											class="{{ Request::is('taxes') ? 'active' : '' }}">Taxes</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('categories', 'budgets', 'budget-expenses', 'budget-revenues') ? 'active subdrop' : '' }}"><span>Accounting</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('categories')}}"
											class="{{ Request::is('categories') ? 'active' : '' }}">Categories</a></li>
									<li><a href="{{url('budgets')}}"
											class="{{ Request::is('budgets') ? 'active' : '' }}">Budgets</a></li>
									<li><a href="{{url('budget-expenses')}}"
											class="{{ Request::is('budget-expenses') ? 'active' : '' }}">Budget
											Expenses</a></li>
									<li><a href="{{url('budget-revenues')}}"
											class="{{ Request::is('budget-revenues') ? 'active' : '' }}">Budget
											Revenues</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('employee-salary', 'payslip', 'payroll', 'payroll-overtime', 'payroll-deduction') ? 'active subdrop' : '' }}"><span>Payroll</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('employee-salary')}}"
											class="{{ Request::is('employee-salary') ? 'active' : '' }}">Employee
											Salary</a></li>
									<li><a href="{{url('payslip')}}"
											class="{{ Request::is('payslip') ? 'active' : '' }}">Payslip</a></li>
									<li><a href="{{url('payroll')}}"
											class="{{ Request::is('payroll', 'payroll-overtime', 'payroll-deduction') ? 'active' : '' }}">Payroll
											Items</a></li>
								</ul>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('assets', 'asset-categories', 'knowledgebase', 'knowledgebase-details', 'knowledgebase-view', 'users', 'roles-permissions', 'permission', 'expenses-report', 'invoice-report', 'payment-report', 'project-report', 'task-report', 'user-report', 'employee-report', 'payslip-report', 'attendance-report', 'leave-report', 'daily-report', 'profile-settings', 'security-settings', 'notification-settings', 'connected-apps', 'business-settings', 'seo-settings', 'localization-settings', 'prefixes', 'preferences', 'appearance', 'language', 'add-language', 'language-web', 'authentication-settings', 'ai-settings', 'salary-settings', 'approval-settings', 'invoice-settings', 'leave-type', 'custom-fields', 'email-settings', 'email-template', 'sms-settings', 'sms-template', 'otp-settings', 'gdpr', 'maintenance-mode', 'payment-gateways', 'tax-rates', 'currencies', 'custom-css', 'custom-js', 'cronjob', 'storage-settings', 'ban-ip-address', 'backup', 'clear-cache') ? 'show active' : '' }}"
						id="administration">
						<ul>
							<li class="menu-title"><span>ADMINISTRATION</span></li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('assets', 'asset-categories') ? 'active subdrop' : '' }}"><span>Assets</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('assets')}}"
											class="{{ Request::is('assets') ? 'active' : '' }}">Assets</a></li>
									<li><a href="{{url('asset-categories')}}"
											class="{{ Request::is('asset-categories') ? 'active' : '' }}">Asset
											Categories</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('knowledgebase', 'knowledgebase-details', 'knowledgebase-view') ? 'active subdrop' : '' }}"><span>Help
										& Supports</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('knowledgebase')}}"
											class="{{ Request::is('knowledgebase', 'knowledgebase-details', 'knowledgebase-view') ? 'active' : '' }}">Knowledge
											Base</a></li>
									<li><a href="{{url('activity')}}">Activities</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('users', 'roles-permissions', 'permission') ? 'active subdrop' : '' }}"><span>User
										Management</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('users')}}"
											class="{{ Request::is('users') ? 'active' : '' }}">Users</a></li>
									<li><a href="{{url('roles-permissions')}}"
											class="{{ Request::is('roles-permissions', 'permission') ? 'active' : '' }}">Roles
											& Permissions</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('expenses-report', 'invoice-report', 'payment-report', 'project-report', 'task-report', 'user-report', 'employee-report', 'payslip-report', 'attendance-report', 'leave-report', 'daily-report') ? 'active subdrop' : '' }}"><span>Reports</span>
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('expenses-report')}}"
											class="{{ Request::is('expenses-report') ? 'active' : '' }}">Expense
											Report</a></li>
									<li><a href="{{url('invoice-report')}}"
											class="{{ Request::is('invoice-report') ? 'active' : '' }}">Invoice
											Report</a></li>
									<li><a href="{{url('payment-report')}}"
											class="{{ Request::is('payment-report') ? 'active' : '' }}">Payment
											Report</a></li>
									<li><a href="{{url('project-report')}}"
											class="{{ Request::is('project-report') ? 'active' : '' }}">Project
											Report</a></li>
									<li><a href="{{url('task-report')}}"
											class="{{ Request::is('task-report') ? 'active' : '' }}">Task Report</a>
									</li>
									<li><a href="{{url('user-report')}}"
											class="{{ Request::is('user-report') ? 'active' : '' }}">User Report</a>
									</li>
									<li><a href="{{url('employee-report')}}"
											class="{{ Request::is('employee-report') ? 'active' : '' }}">Employee
											Report</a></li>
									<li><a href="{{url('payslip-report')}}"
											class="{{ Request::is('payslip-report') ? 'active' : '' }}">Payslip
											Report</a></li>
									<li><a href="{{url('attendance-report')}}"
											class="{{ Request::is('attendance-report') ? 'active' : '' }}">Attendance
											Report</a></li>
									<li><a href="{{url('leave-report')}}"
											class="{{ Request::is('leave-report') ? 'active' : '' }}">Leave Report</a>
									</li>
									<li><a href="{{url('daily-report')}}"
											class="{{ Request::is('daily-report') ? 'active' : '' }}">Daily Report</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('profile-settings', 'security-settings', 'notification-settings', 'connected-apps') ? 'active subdrop' : '' }}">
									General Settings
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('profile-settings')}}"
											class="{{ Request::is('profile-settings') ? 'active' : '' }}">Profile</a>
									</li>
									<li><a href="{{url('security-settings')}}"
											class="{{ Request::is('security-settings') ? 'active' : '' }}">Security</a>
									</li>
									<li><a href="{{url('notification-settings')}}"
											class="{{ Request::is('notification-settings') ? 'active' : '' }}">Notifications</a>
									</li>
									<li><a href="{{url('connected-apps')}}"
											class="{{ Request::is('connected-apps') ? 'active' : '' }}">Connected
											Apps</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('business-settings', 'seo-settings', 'localization-settings', 'prefixes', 'preferences', 'appearance', 'language', 'add-language', 'language-web', 'authentication-settings', 'ai-settings') ? 'active subdrop' : '' }}">
									Website Settings
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('business-settings')}}"
											class="{{ Request::is('business-settings') ? 'active' : '' }}">Business
											Settings</a></li>
									<li><a href="{{url('seo-settings')}}"
											class="{{ Request::is('seo-settings') ? 'active' : '' }}">SEO Settings</a>
									</li>
									<li><a href="{{url('localization-settings')}}"
											class="{{ Request::is('localization-settings') ? 'active' : '' }}">Localization</a>
									</li>
									<li><a href="{{url('prefixes')}}"
											class="{{ Request::is('prefixes') ? 'active' : '' }}">Prefixes</a></li>
									<li><a href="{{url('preferences')}}"
											class="{{ Request::is('preferences') ? 'active' : '' }}">Preferences</a>
									</li>
									<li><a href="{{url('appearance')}}"
											class="{{ Request::is('appearance') ? 'active' : '' }}">Appearance</a></li>
									<li><a href="{{url('language')}}"
											class="{{ Request::is('language', 'add-language', 'language-web') ? 'active' : '' }}">Language</a>
									</li>
									<li><a href="{{url('authentication-settings')}}"
											class="{{ Request::is('authentication-settings') ? 'active' : '' }}">Authentication</a>
									</li>
									<li><a href="{{url('ai-settings')}}"
											class="{{ Request::is('ai-settings') ? 'active' : '' }}">AI Settings</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('salary-settings', 'approval-settings', 'invoice-settings', 'leave-type', 'custom-fields') ? 'active subdrop' : '' }}">App
									Settings<span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{url('salary-settings')}}"
											class="{{ Request::is('salary-settings') ? 'active' : '' }}">Salary
											Settings</a></li>
									<li><a href="{{url('approval-settings')}}"
											class="{{ Request::is('approval-settings') ? 'active' : '' }}">Approval
											Settings</a></li>
									<li><a href="{{url('invoice-settings')}}"
											class="{{ Request::is('invoice-settings') ? 'active' : '' }}">Invoice
											Settings</a></li>
									<li><a href="{{url('leave-type')}}"
											class="{{ Request::is('leave-type') ? 'active' : '' }}">Leave Type</a></li>
									<li><a href="{{url('custom-fields')}}"
											class="{{ Request::is('custom-fields') ? 'active' : '' }}">Custom Fields</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('email-settings', 'email-template', 'sms-settings', 'sms-template', 'otp-settings', 'gdpr', 'maintenance-mode') ? 'active subdrop' : '' }}">
									System Settings
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('email-settings')}}"
											class="{{ Request::is('email-settings') ? 'active' : '' }}">Email
											Settings</a></li>
									<li><a href="{{url('email-template')}}"
											class="{{ Request::is('email-template') ? 'active' : '' }}">Email
											Templates</a></li>
									<li><a href="{{url('sms-settings')}}"
											class="{{ Request::is('sms-settings') ? 'active' : '' }}">SMS Settings</a>
									</li>
									<li><a href="{{url('sms-template')}}"
											class="{{ Request::is('sms-template') ? 'active' : '' }}">SMS Templates</a>
									</li>
									<li><a href="{{url('otp-settings')}}"
											class="{{ Request::is('otp-settings') ? 'active' : '' }}">OTP</a></li>
									<li><a href="{{url('gdpr')}}" class="{{ Request::is('gdpr') ? 'active' : '' }}">GDPR
											Cookies</a></li>
									<li><a href="{{url('maintenance-mode')}}"
											class="{{ Request::is('maintenance-mode') ? 'active' : '' }}">Maintenance
											Mode</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('payment-gateways', 'tax-rates', 'currencies') ? 'active subdrop' : '' }}">
									Financial Settings
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('payment-gateways')}}"
											class="{{ Request::is('payment-gateways') ? 'active' : '' }}">Payment
											Gateways</a></li>
									<li><a href="{{url('tax-rates')}}"
											class="{{ Request::is('tax-rates') ? 'active' : '' }}">Tax Rate</a></li>
									<li><a href="{{url('currencies')}}"
											class="{{ Request::is('currencies') ? 'active' : '' }}">Currencies</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('custom-css', 'custom-js', 'cronjob', 'storage-settings', 'ban-ip-address', 'backup', 'clear-cache') ? 'active subdrop' : '' }}">Other
									Settings<span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{url('custom-css')}}"
											class="{{ Request::is('custom-css') ? 'active' : '' }}">Custom CSS</a></li>
									<li><a href="{{url('custom-js')}}"
											class="{{ Request::is('custom-js') ? 'active' : '' }}">Custom JS</a></li>
									<li><a href="{{url('cronjob')}}"
											class="{{ Request::is('cronjob') ? 'active' : '' }}">Cronjob</a></li>
									<li><a href="{{url('storage-settings')}}"
											class="{{ Request::is('storage-settings') ? 'active' : '' }}">Storage</a>
									</li>
									<li><a href="{{url('ban-ip-address')}}"
											class="{{ Request::is('ban-ip-address') ? 'active' : '' }}">Ban IP
											Address</a></li>
									<li><a href="{{url('backup')}}"
											class="{{ Request::is('backup') ? 'active' : '' }}">Backup</a></li>
									<li><a href="{{url('clear-cache')}}"
											class="{{ Request::is('clear-cache') ? 'active' : '' }}">Clear Cache</a>
									</li>
								</ul>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('pages', 'blogs', 'blog-categories', 'blog-comments', 'blog-tags', 'countries', 'states', 'cities', 'testimonials', 'faq') ? ' show active' : '' }}"
						id="content">
						<ul>
							<li class="menu-title"><span>CONTENT</span></li>
							<li><a href="{{url('pages')}}" class="{{ Request::is('pages') ? 'active' : '' }}">Pages</a>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('blogs', 'blog-categories', 'blog-comments', 'blog-tags') ? 'active subdrop' : '' }}">
									Blogs
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('blogs')}}"
											class="{{ Request::is('blogs') ? 'active' : '' }}">All Blogs</a></li>
									<li><a href="{{url('blog-categories')}}"
											class="{{ Request::is('blog-categories') ? 'active' : '' }}">Categories</a>
									</li>
									<li><a href="{{url('blog-comments')}}"
											class="{{ Request::is('blog-comments') ? 'active' : '' }}">Comments</a></li>
									<li><a href="{{url('blog-tags')}}"
											class="{{ Request::is('blog-tags') ? 'active' : '' }}">Blog Tags</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('countries', 'states', 'cities') ? 'active subdrop' : '' }}">
									Locations
									<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('countries')}}"
											class="{{ Request::is('countries') ? 'active' : '' }}">Countries</a></li>
									<li><a href="{{url('states')}}"
											class="{{ Request::is('states') ? 'active' : '' }}">States</a></li>
									<li><a href="{{url('cities')}}"
											class="{{ Request::is('cities') ? 'active' : '' }}">Cities</a></li>
								</ul>
							</li>
							<li><a href="{{url('testimonials')}}"
									class="{{ Request::is('testimonials') ? 'active' : '' }}">Testimonials</a></li>
							<li><a href="{{url('faq')}}" class="{{ Request::is('faq') ? 'active' : '' }}">FAQ’S</a></li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('starter', 'profile', 'gallery', 'search-result', 'timeline', 'pricing', 'coming-soon', 'under-maintenance', 'under-construction', 'api-keys', 'privacy-policy', 'terms-condition') ? ' show active' : '' }}"
						id="pages">
						<ul>
							<li class="menu-title"><span>PAGES</span></li>
							<li><a href="{{url('starter')}}"
									class="{{ Request::is('starter') ? 'active' : '' }}"><span>Starter</span></a></li>
							<li><a href="{{url('profile')}}"
									class="{{ Request::is('profile') ? 'active' : '' }}"><span>Profile</span></a></li>
							<li><a href="{{url('gallery')}}"
									class="{{ Request::is('gallery') ? 'active' : '' }}"><span>Gallery</span></a></li>
							<li><a href="{{url('search-result')}}"
									class="{{ Request::is('search-result') ? 'active' : '' }}"><span>Search
										Results</span></a></li>
							<li><a href="{{url('timeline')}}"
									class="{{ Request::is('timeline') ? 'active' : '' }}"><span>Timeline</span></a></li>
							<li><a href="{{url('pricing')}}"
									class="{{ Request::is('pricing') ? 'active' : '' }}"><span>Pricing</span></a></li>
							<li><a href="{{url('coming-soon')}}"
									class="{{ Request::is('coming-soon') ? 'active' : '' }}"><span>Coming
										Soon</span></a></li>
							<li><a href="{{url('under-maintenance')}}"
									class="{{ Request::is('under-maintenance') ? 'active' : '' }}"><span>Under
										Maintenance</span></a></li>
							<li><a href="{{url('under-construction')}}"
									class="{{ Request::is('under-construction') ? 'active' : '' }}"><span>Under
										Construction</span></a></li>
							<li><a href="{{url('api-keys')}}"
									class="{{ Request::is('api-keys') ? 'active' : '' }}"><span>API Keys</span></a></li>
							<li><a href="{{url('privacy-policy')}}"
									class="{{ Request::is('privacy-policy') ? 'active' : '' }}"><span>Privacy
										Policy</span></a></li>
							<li><a href="{{url('terms-condition')}}"
									class="{{ Request::is('terms-condition') ? 'active' : '' }}"><span>Terms &
										Conditions</span></a></li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('login', 'login-2', 'login-3', 'register', 'register-2', 'register-3', 'forgot-password', 'forgot-password-2', 'forgot-password-3', 'reset-password', 'reset-password-2', 'reset-password-3', 'email-verification', 'email-verification-2', 'email-verification-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3', 'lock-screen', 'error-404', 'error-500') ? ' show active' : '' }}"
						id="authentication">
						<ul>
							<li class="menu-title"><span>AUTHENTICATION</span></li>
							<li class="submenu">
								<a href="javascript:void(0);">
									Login<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('login')}}"
											class="{{ Request::is('login') ? 'active' : '' }}">Cover</a></li>
									<li><a href="{{url('login-2')}}"
											class="{{ Request::is('login-2') ? 'active' : '' }}">Illustration</a></li>
									<li><a href="{{url('login-3')}}"
											class="{{ Request::is('login-3') ? 'active' : '' }}">Basic</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									Register<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('register')}}"
											class="{{ Request::is('register') ? 'active' : '' }}">Cover</a></li>
									<li><a href="{{url('register-2')}}"
											class="{{ Request::is('register-2') ? 'active' : '' }}">Illustration</a>
									</li>
									<li><a href="{{url('register-3')}}"
											class="{{ Request::is('register-3') ? 'active' : '' }}">Basic</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									Forgot Password<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('forgot-password')}}"
											class="{{ Request::is('forgot-password') ? 'active' : '' }}">Cover</a></li>
									<li><a href="{{url('forgot-password-2')}}"
											class="{{ Request::is('forgot-password-2') ? 'active' : '' }}">Illustration</a>
									</li>
									<li><a href="{{url('forgot-password-3')}}"
											class="{{ Request::is('forgot-password-3') ? 'active' : '' }}">Basic</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									Reset Password<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('reset-password')}}"
											class="{{ Request::is('reset-password') ? 'active' : '' }}">Cover</a></li>
									<li><a href="{{url('reset-password-2')}}"
											class="{{ Request::is('reset-password-2') ? 'active' : '' }}">Illustration</a>
									</li>
									<li><a href="{{url('reset-password-3')}}"
											class="{{ Request::is('reset-password-3') ? 'active' : '' }}">Basic</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									Email Verification<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('email-verification')}}"
											class="{{ Request::is('email-verification') ? 'active' : '' }}">Cover</a>
									</li>
									<li><a href="{{url('email-verification-2')}}"
											class="{{ Request::is('email-verification-2') ? 'active' : '' }}">Illustration</a>
									</li>
									<li><a href="{{url('email-verification-3')}}"
											class="{{ Request::is('email-verification-3') ? 'active' : '' }}">Basic</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);">
									2 Step Verification<span class="menu-arrow"></span>
								</a>
								<ul>
									<li><a href="{{url('two-step-verification')}}"
											class="{{ Request::is('two-step-verification') ? 'active' : '' }}">Cover</a>
									</li>
									<li><a href="{{url('two-step-verification-2')}}"
											class="{{ Request::is('two-step-verification-2') ? 'active' : '' }}">Illustration</a>
									</li>
									<li><a href="{{url('two-step-verification-3')}}"
											class="{{ Request::is('two-step-verification-3') ? 'active' : '' }}">Basic</a>
									</li>
								</ul>
							</li>
							<li class="{{ Request::is('lock-screen') ? 'active' : '' }}"><a
									href="{{url('lock-screen')}}">Lock Screen</a></li>
							<li class="{{ Request::is('error-404') ? 'active' : '' }}"><a
									href="{{url('error-404')}}">404 Error</a></li>
							<li class="{{ Request::is('error-500') ? 'active' : '' }}"><a
									href="{{url('error-500')}}">500 Error</a></li>
						</ul>
					</div>
					<div class="tab-pane fade {{ Request::is('ui-alerts', 'ui-accordion', 'ui-avatar', 'ui-badges', 'ui-buttons', 'ui-buttons-group', 'ui-breadcrumb', 'ui-cards', 'ui-carousel', 'ui-dropdowns', 'ui-grid', 'ui-images', 'ui-modals', 'ui-offcanvas', 'ui-pagination', 'ui-popovers', 'ui-progress', 'ui-placeholders', 'ui-spinner', 'ui-nav-tabs', 'ui-toasts', 'ui-tooltips', 'ui-typography', 'ui-collapse', 'ui-links', 'ui-list-groups', 'ui-ratio', 'ui-clipboard', 'ui-dragula', 'ui-sweetalerts', 'ui-lightbox', 'ui-scrollbar', 'form-basic-inputs', 'form-checkbox-radios', 'form-input-groups', 'form-grid-gutters', 'form-select', 'form-mask', 'form-fileupload', 'form-horizontal', 'form-vertical', 'form-floating-labels', 'form-validation', 'form-wizard', 'form-select2', 'tables-basic', 'data-tables', 'chart-apex', 'chart-js', 'chart-morris', 'chart-flot', 'chart-c3', 'icon-fontawesome', 'icon-feather', 'icon-ionic', 'icon-material', 'icon-pe7', 'icon-simpleline', 'icon-themify', 'icon-weather', 'icon-typicon', 'icon-flag') ? 'show active' : '' }}"
						id="ui-elements">
						<ul>
							<li class="menu-title"><span>UI INTERFACE</span></li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('ui-alerts', 'ui-accordion', 'ui-avatar', 'ui-badges', 'ui-buttons', 'ui-buttons-group', 'ui-breadcrumb', 'ui-cards', 'ui-carousel', 'ui-dropdowns', 'ui-grid', 'ui-images', 'ui-modals', 'ui-offcanvas', 'ui-pagination', 'ui-popovers', 'ui-progress', 'ui-placeholders', 'ui-spinner', 'ui-nav-tabs', 'ui-toasts', 'ui-tooltips', 'ui-typography', 'ui-collapse', 'ui-links', 'ui-list-groups', 'ui-ratio') ? 'subdrop active' : '' }}">Base
									UI<span class="menu-arrow"></span>
								</a>
								<ul>
									<li>
										<a href="{{url('ui-alerts')}}"
											class="{{ Request::is('ui-alerts') ? 'active' : '' }}">Alerts</a>
									</li>
									<li>
										<a href="{{url('ui-accordion')}}"
											class="{{ Request::is('ui-accordion') ? 'active' : '' }}">Accordion</a>
									</li>
									<li>
										<a href="{{url('ui-avatar')}}"
											class="{{ Request::is('ui-avatar') ? 'active' : '' }}">Avatar</a>
									</li>
									<li>
										<a href="{{url('ui-badges')}}"
											class="{{ Request::is('ui-badges') ? 'active' : '' }}">Badges</a>
									</li>
									<li>
										<a href="{{url('ui-buttons')}}"
											class="{{ Request::is('ui-buttons') ? 'active' : '' }}">Buttons</a>
									</li>
									<li>
										<a href="{{url('ui-buttons-group')}}"
											class="{{ Request::is('ui-buttons-group') ? 'active' : '' }}">Button
											Group</a>
									</li>
									<li>
										<a href="{{url('ui-breadcrumb')}}"
											class="{{ Request::is('ui-breadcrumb') ? 'active' : '' }}">Breadcrumb</a>
									</li>
									<li>
										<a href="{{url('ui-cards')}}"
											class="{{ Request::is('ui-cards') ? 'active' : '' }}">Card</a>
									</li>
									<li>
										<a href="{{url('ui-carousel')}}"
											class="{{ Request::is('ui-carousel') ? 'active' : '' }}">Carousel</a>
									</li>
									<li>
										<a href="{{url('ui-collapse')}}"
											class="{{ Request::is('ui-collapse') ? 'active' : '' }}">Collapse</a>
									</li>
									<li>
										<a href="{{url('ui-dropdowns')}}"
											class="{{ Request::is('ui-dropdowns') ? 'active' : '' }}">Dropdowns</a>
									</li>
									<li>
										<a href="{{url('ui-grid')}}"
											class="{{ Request::is('ui-grid') ? 'active' : '' }}">Grid</a>
									</li>
									<li>
										<a href="{{url('ui-ratio')}}"
											class="{{ Request::is('ui-ratio') ? 'active' : '' }}">Ratio</a>
									</li>
									<li>
										<a href="{{url('ui-images')}}"
											class="{{ Request::is('ui-images') ? 'active' : '' }}">Images</a>
									</li>
									<li>
										<a href="{{url('ui-links')}}"
											class="{{ Request::is('ui-links') ? 'active' : '' }}">Links</a>
									</li>
									<li>
										<a href="{{url('ui-list-groups')}}"
											class="{{ Request::is('ui-list-groups') ? 'active' : '' }}">List Groups</a>
									</li>
									<li>
										<a href="{{url('ui-modals')}}"
											class="{{ Request::is('ui-modals') ? 'active' : '' }}">Modals</a>
									</li>
									<li>
										<a href="{{url('ui-offcanvas')}}"
											class="{{ Request::is('ui-offcanvas') ? 'active' : '' }}">Offcanvas</a>
									</li>
									<li>
										<a href="{{url('ui-pagination')}}"
											class="{{ Request::is('ui-pagination') ? 'active' : '' }}">Pagination</a>
									</li>
									<li>
										<a href="{{url('ui-popovers')}}"
											class="{{ Request::is('ui-popovers') ? 'active' : '' }}">Popovers</a>
									</li>
									<li>
										<a href="{{url('ui-progress')}}"
											class="{{ Request::is('ui-progress') ? 'active' : '' }}">Progress</a>
									</li>
									<li>
										<a href="{{url('ui-placeholders')}}"
											class="{{ Request::is('ui-placeholders') ? 'active' : '' }}">Placeholders</a>
									</li>
									<li>
										<a href="{{url('ui-spinner')}}"
											class="{{ Request::is('ui-spinner') ? 'active' : '' }}">Spinner</a>
									</li>
									<li>
										<a href="{{url('ui-nav-tabs')}}"
											class="{{ Request::is('ui-nav-tabs') ? 'active' : '' }}">Tabs</a>
									</li>
									<li>
										<a href="{{url('ui-toasts')}}"
											class="{{ Request::is('ui-toasts') ? 'active' : '' }}">Toasts</a>
									</li>
									<li>
										<a href="{{url('ui-tooltips')}}"
											class="{{ Request::is('ui-tooltips') ? 'active' : '' }}">Tooltips</a>
									</li>
									<li>
										<a href="{{url('ui-typography')}}"
											class="{{ Request::is('ui-typography') ? 'active' : '' }}">Typography</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('ui-clipboard', 'ui-dragula', 'ui-sweetalerts', 'ui-lightbox', 'ui-scrollbar') ? 'subdrop active' : '' }}">
									Advanced UI <span class="menu-arrow"></span> </a>
								<ul>
									<li>
										<a href="{{url('ui-dragula')}}"
											class="{{ Request::is('ui-dragula') ? 'active' : '' }}">Drag & Drop</a>
									</li>
									<li>
										<a href="{{url('ui-clipboard')}}"
											class="{{ Request::is('ui-clipboard') ? 'active' : '' }}">Clipboard</a>
									</li>
									<li>
										<a href="{{url('ui-sweetalerts')}}"
											class="{{ Request::is('ui-sweetalerts') ? 'active' : '' }}">Sweet Alerts</a>
									</li>
									<li>
										<a href="{{url('ui-lightbox')}}"
											class="{{ Request::is('ui-lightbox') ? 'active' : '' }}">Lightbox</a>
									</li>
									<li>
										<a href="{{url('ui-scrollbar')}}"
											class="{{ Request::is('ui-scrollbar') ? 'active' : '' }}">Scrollbar</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('form-basic-inputs', 'form-checkbox-radios', 'form-input-groups', 'form-grid-gutters', 'form-select', 'form-mask', 'form-fileupload', 'form-horizontal', 'form-vertical', 'form-floating-labels', 'form-validation', 'form-wizard', 'form-select2') ? 'subdrop active' : '' }}">
									Forms <span class="menu-arrow"></span>
								</a>
								<ul>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);"
											class="{{ Request::is('form-basic-inputs', 'form-checkbox-radios', 'form-input-groups', 'form-grid-gutters', 'form-select', 'form-mask', 'form-fileupload') ? 'subdrop active' : '' }}">Form
											Elements<span class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{url('form-basic-inputs')}}"
													class="{{ Request::is('form-basic-inputs') ? 'active' : '' }}">Basic
													Inputs</a></li>
											<li><a href="{{url('form-checkbox-radios')}}"
													class="{{ Request::is('form-checkbox-radios') ? 'active' : '' }}">Checkbox
													& Radios</a></li>
											<li><a href="{{url('form-input-groups')}}"
													class="{{ Request::is('form-input-groups') ? 'active' : '' }}">Input
													Groups</a></li>
											<li><a href="{{url('form-grid-gutters')}}"
													class="{{ Request::is('form-grid-gutters') ? 'active' : '' }}">Grid
													& Gutters</a></li>
											<li><a href="{{url('form-select')}}"
													class="{{ Request::is('form-select') ? 'active' : '' }}">Form
													Select</a></li>
											<li><a href="{{url('form-mask')}}"
													class="{{ Request::is('form-mask') ? 'active' : '' }}">Input
													Masks</a></li>
											<li><a href="{{url('form-fileupload')}}"
													class="{{ Request::is('form-fileupload') ? 'active' : '' }}">File
													Uploads</a></li>
										</ul>
									</li>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);"
											class="{{ Request::is('form-horizontal', 'form-vertical', 'form-floating-labels') ? 'subdrop active' : '' }}">Layouts<span
												class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="{{url('form-horizontal')}}"
													class="{{ Request::is('form-horizontal') ? 'active' : '' }}">Horizontal
													Form</a></li>
											<li><a href="{{url('form-vertical')}}"
													class="{{ Request::is('form-vertical') ? 'active' : '' }}">Vertical
													Form</a></li>
											<li><a href="{{url('form-floating-labels')}}"
													class="{{ Request::is('form-floating-labels') ? 'active' : '' }}">Floating
													Labels</a></li>
										</ul>
									</li>
									<li><a href="{{url('form-validation')}}"
											class="{{ Request::is('form-validation') ? 'active' : '' }}">Form
											Validation</a></li>
									<li><a href="{{url('form-select2')}}"
											class="{{ Request::is('form-select2') ? 'active' : '' }}">Select2</a></li>
									<li><a href="{{url('form-wizard')}}"
											class="{{ Request::is('form-wizard') ? 'active' : '' }}">Form Wizard</a>
									</li>
									<li><a href="{{url('form-pickers')}}"
											class="{{ Request::is('form-pickers') ? 'active' : '' }}">Form Picker</a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('tables-basic', 'data-tables') ? 'subdrop active' : '' }}">Tables
									<span class="menu-arrow"></span></a>
								<ul>
									<li><a href="{{url('tables-basic')}}"
											class="{{ Request::is('tables-basic') ? 'active' : '' }}">Basic Tables </a>
									</li>
									<li><a href="{{url('data-tables')}}"
											class="{{ Request::is('data-tables') ? 'active' : '' }}">Data Table </a>
									</li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('chart-apex', 'chart-js', 'chart-morris', 'chart-flot', 'chart-c3') ? 'subdrop active' : '' }}">Charts<span
										class="menu-arrow"></span> </a>
								<ul>
									<li><a href="{{url('chart-apex')}}"
											class="{{ Request::is('chart-apex') ? 'active' : '' }}">Apex Charts</a></li>
									<li><a href="{{url('chart-c3')}}"
											class="{{ Request::is('chart-c3') ? 'active' : '' }}">Chart C3</a></li>
									<li><a href="{{url('chart-js')}}"
											class="{{ Request::is('chart-js') ? 'active' : '' }}">Chart Js</a></li>
									<li><a href="{{url('chart-morris')}}"
											class="{{ Request::is('chart-morris') ? 'active' : '' }}">Morris Charts</a>
									</li>
									<li><a href="{{url('chart-flot')}}"
											class="{{ Request::is('chart-flot') ? 'active' : '' }}">Flot Charts</a></li>
								</ul>
							</li>
							<li class="submenu">
								<a href="javascript:void(0);"
									class="{{ Request::is('icon-fontawesome', 'icon-feather', 'icon-ionic', 'icon-material', 'icon-pe7', 'icon-simpleline', 'icon-themify', 'icon-weather', 'icon-typicon', 'icon-flag') ? 'subdrop active' : '' }}">Icons<span
										class="menu-arrow"></span> </a>
								<ul>
									<li><a href="{{url('icon-fontawesome')}}"
											class="{{ Request::is('icon-fontawesome') ? 'active' : '' }}">Fontawesome
											Icons</a></li>
									<li><a href="{{url('icon-tabler')}}"
											class="{{ Request::is('icon-tabler') ? 'active' : '' }}">Tabler Icons</a>
									</li>
									<li><a href="{{url('icon-bootstrap')}}"
											class="{{ Request::is('icon-bootstrap') ? 'active' : '' }}">Bootstrap
											Icons</a></li>
									<li><a href="{{url('icon-remix')}}"
											class="{{ Request::is('icon-remix') ? 'active' : '' }}">Remix Icons</a></li>
									<li><a href="{{url('icon-feather')}}"
											class="{{ Request::is('icon-feather') ? 'active' : '' }}">Feather Icons</a>
									</li>
									<li><a href="{{url('icon-ionic')}}"
											class="{{ Request::is('icon-ionic') ? 'active' : '' }}">Ionic Icons</a></li>
									<li><a href="{{url('icon-material')}}"
											class="{{ Request::is('icon-material') ? 'active' : '' }}">Material
											Icons</a></li>
									<li><a href="{{url('icon-pe7')}}"
											class="{{ Request::is('icon-pe7') ? 'active' : '' }}">Pe7 Icons</a></li>
									<li><a href="{{url('icon-simpleline')}}"
											class="{{ Request::is('icon-simpleline') ? 'active' : '' }}">Simpleline
											Icons</a></li>
									<li><a href="{{url('icon-themify')}}"
											class="{{ Request::is('icon-themify') ? 'active' : '' }}">Themify Icons</a>
									</li>
									<li><a href="{{url('icon-weather')}}"
											class="{{ Request::is('icon-weather') ? 'active' : '' }}">Weather Icons</a>
									</li>
									<li><a href="{{url('icon-typicon')}}"
											class="{{ Request::is('icon-typicon') ? 'active' : '' }}">Typicon Icons</a>
									</li>
									<li><a href="{{url('icon-flag')}}"
											class="{{ Request::is('icon-flag') ? 'active' : '' }}">Flag Icons</a></li>

								</ul>
							</li>
						</ul>
					</div>
					<div class="tab-pane fade" id="extras">
						<ul>
							<li class="menu-title"><span>EXTRAS</span></li>
							<li><a href="https://smarthr.dreamstechnologies.com/laravel/documentation/index.html"
									target="_blank">Documentation</a></li>
							<li><a href="https://smarthr.dreamstechnologies.com/laravel/documentation/changelog.html"
									target="_blank">Change Log</a></li>
							<li class="submenu">
								<a href="javascript:void(0);"><span>Multi Level</span><span
										class="menu-arrow"></span></a>
								<ul>
									<li><a href="javascript:void(0);">Multilevel 1</a></li>
									<li class="submenu submenu-two">
										<a href="javascript:void(0);">Multilevel 2<span
												class="menu-arrow inside-submenu"></span></a>
										<ul>
											<li><a href="javascript:void(0);">Multilevel 2.1</a></li>
											<li class="submenu submenu-two submenu-three">
												<a href="javascript:void(0);">Multilevel 2.2<span
														class="menu-arrow inside-submenu inside-submenu-two"></span></a>
												<ul>
													<li><a href="javascript:void(0);">Multilevel 2.2.1</a></li>
													<li><a href="javascript:void(0);">Multilevel 2.2.2</a></li>
												</ul>
											</li>
										</ul>
									</li>
									<li><a href="javascript:void(0);">Multilevel 3</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /Two Col Sidebar -->
