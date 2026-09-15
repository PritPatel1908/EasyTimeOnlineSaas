    <!-- Bootstrap Core JS -->
    <script src="{{URL::asset('build/js/bootstrap.bundle.min.js')}}"></script>

    <!-- Feather Icon JS -->
    <script src="{{URL::asset('build/js/feather.min.js')}}"></script>

@if (!Route::is(['coming-soon', 'email-verification', 'email-verification-2', 'email-verification-3', 'error-404', 'error-500', 'forgot-password', 'forgot-password-2', 'forgot-password-3', 'lock-screen', 'login', 'login-2', 'login-3', 'register', 'register-2', 'register-3', 'reset-password', 'reset-password-2', 'reset-password-3', 'success', 'success-2', 'success-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3', 'under-construction', 'under-maintenance']))
    <!-- Slimscroll JS -->
    <script src="{{URL::asset('build/plugins/simplebar/simplebar.min.js')}}"></script>
@endif

@if (Route::is(['activity', 'ai-attendance-insights', 'ai-payroll-forecast', 'analytics', 'asset-dashboard', 'attendance-dashboard', 'blog-2', 'blogs', 'client-details', 'company-details', 'contact-details', 'contacts', 'dashboard', 'deals-details', 'email-template', 'employee-dashboard', 'employee-details', 'file-manager', 'gdpr', 'help-desk-dashboard', 'incoming-call', 'index', '/', 'invoice-settings', 'it-admin-dashboard', 'kanban-view', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'leads', 'leads-details', 'leads-grid', 'maintenance-mode', 'notes', 'outgoing-call', 'payment-gateways', 'payroll-dashboard', 'privacy-policy', 'project-details', 'projects', 'projects-grid', 'recruitment-dashboard', 'sms-settings', 'sms-template', 'social-feed', 'task-board', 'task-details', 'tasks', 'terms-condition', 'todo', 'todo-list', 'video-call', 'voice-call']))
    <!-- Summernote JS -->
    <script src="{{URL::asset('build/plugins/quill/quill.min.js')}}"></script>
@endif

@if (Route::is(['activity', 'agents', 'api-keys', 'aptitude-result', 'asset-categories', 'asset-dashboard', 'assets', 'attendance-admin', 'attendance-employee', 'attendance-report', 'blog-categories', 'blog-comments', 'blog-tags', 'budget-expenses', 'budget-revenues', 'budgets', 'call-history', 'campus-hiring', 'candidates', 'candidates-grid', 'categories', 'certification-tracking', 'cities', 'client-details', 'clients', 'companies', 'companies-crm', 'contacts', 'countries', 'cronjob', 'custom-css', 'custom-js', 'daily-report', 'data-tables', 'deals', 'departments', 'designations', 'domain', 'employee-details', 'employee-report', 'employee-salary', 'employees', 'employees-grid', 'escalation-rules', 'estimates', 'expenses', 'expenses-report', 'experience-level', 'faq', 'file-manager', 'form-basic-inputs', 'form-checkbox-radios', 'form-elements', 'form-floating-labels', 'form-grid-gutters', 'form-horizontal', 'form-input-groups', 'form-mask', 'form-select', 'form-select2', 'form-validation', 'goal-tracking', 'goal-type', 'group-video-call', 'holidays', 'interview-questions', 'invoice', 'invoice-details', 'invoice-report', 'invoices', 'job-list', 'knowledgebase', 'knowledgebase-details', 'knowledgebase-view', 'layout-box', 'layout-dark', 'layout-detached', 'leads', 'learning-analytics', 'leave-report', 'leave-settings', 'leave-type', 'leaves', 'leaves-employee', 'manage-jobs', 'notice-period-tracker', 'offer-approvals', 'overtime', 'packages', 'packages-grid', 'pages', 'payment-report', 'payments', 'payroll', 'payroll-deduction', 'payroll-overtime', 'payslip', 'payslip-report', 'performance-appraisal', 'performance-indicator', 'performance-review', 'permission', 'pipeline', 'policy', 'pricing', 'privacy-policy', 'probation-management', 'project-report', 'projects', 'projects-grid', 'promotion', 'provident-fund', 'purchase-transaction', 'refferals', 'resignation', 'resume-parsing', 'roles-permissions', 'schedule-timing', 'shift-swap-requests', 'shortlist-candidates', 'sla-policies', 'social-feed', 'states', 'storage-settings', 'subscription', 'tables-basic', 'task-board', 'task-details', 'task-report', 'tasks', 'taxes', 'tenant-support-tickets', 'tenant-ticket-details', 'tenant-usage-metrics', 'termination', 'terms-condition', 'testimonials', 'ticket-automation', 'ticket-details', 'ticket-reports', 'tickets', 'tickets-grid', 'timesheets', 'todo-list', 'trainers', 'training', 'training-type', 'ui-buttons-group', 'ui-modals', 'ui-offcanvas', 'user-report', 'users', 'work-from-home']))
    <!-- Datatable JS -->
    <script src="{{URL::asset('build/plugins/gridjs/gridjs.umd.js')}}"></script>
@endif

@if (Route::is(['activity', 'agents', 'ai-attendance-insights', 'ai-hiring-forecast', 'ai-payroll-forecast', 'ai-team-performance-insights', 'analytics', 'api-keys', 'aptitude-result', 'asset-categories', 'asset-dashboard', 'assets', 'attendance-admin', 'attendance-dashboard', 'attendance-employee', 'attendance-report', 'blog-2', 'blog-categories', 'blog-comments', 'blog-tags', 'blogs', 'budget-expenses', 'budget-revenues', 'budgets', 'call-history', 'candidates', 'candidates-grid', 'categories', 'chart-apex', 'chart-flot', 'chart-morris', 'cities', 'clients', 'companies', 'countries', 'daily-report', 'dashboard', 'admin.dashboard', 'tenant.dashboard', 'deals-dashboard', 'departments', 'designations', 'domain', 'employee-dashboard', 'employee-report', 'employee-salary', 'employees', 'employees-grid', 'estimates', 'expenses', 'expenses-report', 'experience-level', 'faq', 'file-manager', 'finance-dashboard', 'form-elements', 'goal-tracking', 'goal-type', 'group-video-call', 'help-desk-dashboard', 'holidays', 'hr-dashboard', 'icon-feather', 'icon-flag', 'icon-fontawesome', 'icon-ionic', 'icon-material', 'icon-pe7', 'icon-simpleline', 'icon-themify', 'icon-typicon', 'icon-weather', 'index', '/', 'invoice', 'invoice-details', 'invoice-report', 'invoices', 'it-admin-dashboard', 'knowledgebase', 'knowledgebase-details', 'knowledgebase-view', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'leads-dashboard', 'learning-analytics', 'leave-report', 'leave-settings', 'leave-type', 'leaves', 'leaves-employee', 'manage-jobs', 'offer-approvals', 'overtime', 'packages', 'packages-grid', 'pages', 'payment-report', 'payments', 'payroll', 'payroll-dashboard', 'payroll-deduction', 'payroll-overtime', 'payslip', 'payslip-report', 'performance-appraisal', 'performance-indicator', 'performance-review', 'permission', 'pipeline', 'policy', 'pricing', 'privacy-policy', 'project-report', 'provident-fund', 'purchase-transaction', 'recruitment-dashboard', 'refferals', 'resignation', 'roles-permissions', 'schedule-timing', 'shortlist-candidates', 'states', 'subscription', 'task-report', 'taxes', 'tenant-support-tickets', 'tenant-usage-metrics', 'termination', 'terms-condition', 'testimonials', 'ticket-reports', 'tickets', 'tickets-grid', 'trainers', 'training', 'training-type', 'ui-buttons-group', 'ui-modals', 'ui-offcanvas', 'user-report', 'users']))
    <!-- Chart JS -->
    <script src="{{URL::asset('build/plugins/apexchart/apexcharts.min.js')}}"></script>
@endif

@if (Route::is(['activity', 'agents', 'ai-attendance-insights', 'ai-hiring-forecast', 'ai-payroll-forecast', 'ai-team-performance-insights', 'analytics', 'api-keys', 'aptitude-result', 'asset-categories', 'asset-dashboard', 'assets', 'attendance-admin', 'attendance-dashboard', 'attendance-employee', 'attendance-report', 'blog-2', 'blog-categories', 'blog-comments', 'blog-tags', 'blogs', 'budget-expenses', 'budget-revenues', 'budgets', 'call-history', 'candidates', 'candidates-grid', 'categories', 'chart-apex', 'cities', 'clients', 'companies', 'countries', 'daily-report', 'dashboard', 'admin.dashboard', 'tenant.dashboard', 'deals-dashboard', 'departments', 'designations', 'domain', 'employee-dashboard', 'employee-report', 'employee-salary', 'employees', 'employees-grid', 'estimates', 'expenses', 'expenses-report', 'experience-level', 'faq', 'file-manager', 'finance-dashboard', 'form-elements', 'goal-tracking', 'goal-type', 'group-video-call', 'help-desk-dashboard', 'holidays', 'hr-dashboard', 'icon-feather', 'icon-flag', 'icon-fontawesome', 'icon-ionic', 'icon-material', 'icon-pe7', 'icon-simpleline', 'icon-themify', 'icon-typicon', 'icon-weather', 'index', '/', 'invoice', 'invoice-details', 'invoice-report', 'invoices', 'it-admin-dashboard', 'knowledgebase', 'knowledgebase-details', 'knowledgebase-view', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'leads-dashboard', 'learning-analytics', 'leave-report', 'leave-settings', 'leave-type', 'leaves', 'leaves-employee', 'manage-jobs', 'offer-approvals', 'overtime', 'packages', 'packages-grid', 'pages', 'payment-report', 'payments', 'payroll', 'payroll-dashboard', 'payroll-deduction', 'payroll-overtime', 'payslip', 'payslip-report', 'performance-appraisal', 'performance-indicator', 'performance-review', 'permission', 'pipeline', 'policy', 'pricing', 'privacy-policy', 'project-report', 'provident-fund', 'purchase-transaction', 'recruitment-dashboard', 'refferals', 'resignation', 'roles-permissions', 'schedule-timing', 'shortlist-candidates', 'states', 'task-report', 'taxes', 'tenant-support-tickets', 'tenant-usage-metrics', 'termination', 'terms-condition', 'testimonials', 'ticket-reports', 'trainers', 'training', 'training-type', 'ui-buttons-group', 'ui-modals', 'ui-offcanvas', 'user-report', 'users']))
    <script src="{{URL::asset('build/plugins/apexchart/chart-data.js')}}"></script>
@endif

@if (!Route::is(['file-manager']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/plugins/flatpickr/flatpickr.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/tom-select/tom-select.complete.min.js')}}"></script>
@endif

@if (Route::is(['add-invoices', 'edit-invoices', 'file-manager', 'group-video-call', 'invoice-details', 'invoices', 'manage-jobs', 'payslip', 'promotion', 'resignation', 'termination']))
    <!-- Color Picker JS -->
    <script src="{{URL::asset('build/js/plyr-js.js')}}"></script>
@endif

@if (Route::is(['add-invoices', 'edit-invoices']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/invoice.js')}}"></script>
@endif

@if (Route::is(['ai-attendance-insights', 'ai-payroll-forecast', 'analytics', 'asset-dashboard', 'chart-js', 'dashboard', 'admin.dashboard', 'tenant.dashboard', 'deals-dashboard', 'finance-dashboard', 'help-desk-dashboard', 'hr-dashboard', 'index', '/', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'privacy-policy', 'recruitment-dashboard', 'terms-condition']))
    <!-- Chart JS -->
    <script src="{{URL::asset('build/plugins/chartjs/chart.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/chartjs/chart-data.js')}}"></script>
@endif

@if (Route::is(['ai-attendance-insights', 'ai-payroll-forecast', 'asset-dashboard', 'attendance-dashboard', 'client-details', 'employee-dashboard', 'help-desk-dashboard', 'index', '/', 'it-admin-dashboard', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'payroll-dashboard', 'project-details', 'recruitment-dashboard', 'task-details', 'tasks', 'todo', 'todo-list']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/todo.js')}}"></script>
@endif

@if (Route::is(['attendance-dashboard', 'index', '/', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'payroll-dashboard', 'recruitment-dashboard']))
    <script src="{{URL::asset('build/plugins/flatpickr/plugins/monthSelect/index.js')}}"></script>
@endif

@if (Route::is(['attendance-employee', 'employee-dashboard', 'language']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/circle-progress.js')}}"></script>
@endif

@if (Route::is('budgets'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/budget.js')}}"></script>
@endif

@if (Route::is(['calendar', 'holiday-calendar', 'incoming-call', 'outgoing-call', 'video-call', 'voice-call']))
    <!-- Fullcalendar JS -->
    <script src="{{URL::asset('build/plugins/fullcalendar/index.global.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/fullcalendar/calendar-data.js')}}"></script>
@endif

@if (Route::is(['candidates-kanban', 'deals', 'deals-grid', 'kanban-view', 'leads-grid', 'task-board']))
    <!-- Drag Card -->
    <script src="{{URL::asset('build/plugins/sortablejs/Sortable.min.js')}}"></script>
@endif

@if (Route::is(['candidates-kanban', 'deals-grid', 'kanban-view', 'leads-grid', 'task-board']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/kanban.js')}}"></script>
@endif

@if (Route::is('chart-c3'))
    <!-- Chart JS -->
    <script src="{{URL::asset('build/plugins/c3-chart/d3.v5.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/c3-chart/c3.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/c3-chart/chart-data.js')}}"></script>
@endif

@if (Route::is('chart-flot'))
    <script src="{{URL::asset('build/plugins/apexchart/chart-data-flot.js')}}"></script>
@endif

@if (Route::is('chart-morris'))
    <script src="{{URL::asset('build/plugins/apexchart/chart-data-morris.js')}}"></script>
@endif

@if (Route::is(['chat', 'group-video-call', 'video-call']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/chat.js')}}"></script>
@endif

@if (Route::is('coming-soon'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/coming-soon.js')}}"></script>
@endif

@if (Route::is(['company-details', 'contact-details', 'deals-details', 'leads-details']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/add-comments.js')}}"></script>
@endif

@if (Route::is(['dashboard', 'admin.dashboard', 'tenant.dashboard', 'deals-dashboard', 'subscription', 'task-report', 'tickets', 'tickets-grid']))
    <script src="{{URL::asset('build/plugins/apexchart/chart-data-peity.js')}}"></script>
@endif

@if (Route::is(['email-reply', 'gallery', 'project-details', 'search-result', 'social-feed', 'ui-lightbox']))
    <!-- Fancybox JS -->
    <script src="{{URL::asset('build/plugins/lightbox/glightbox.min.js')}}"></script>
@endif

@if (Route::is(['email', 'email-reply', 'social-feed']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/email.js')}}"></script>
@endif

@if (Route::is(['email-verification', 'email-verification-2', 'email-verification-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/otp.js')}}"></script>
@endif

@if (Route::is('employee-salary'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/employee-salary.js')}}"></script>
@endif

@if (Route::is('estimates'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/estimates.js')}}"></script>
@endif

@if (Route::is(['file-manager', 'notes', 'project-details', 'social-feed', 'task-details']))
    <!-- Swiper -->
    <script src="{{URL::asset('build/plugins/swiper/swiper-bundle.min.js')}}"></script>
@endif

@if (Route::is(['file-manager', 'project-details', 'task-details']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/file-manager.js')}}"></script>
@endif

@if (Route::is('form-fileupload'))
    <!-- Fileupload JS -->
    <script src="{{URL::asset('build/plugins/fileupload/fileupload.min.js')}}"></script>
@endif

@if (Route::is(['form-fileupload', 'interview-questions']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/file-upload.js')}}"></script>
@endif

@if (Route::is('form-mask'))
    <!-- Mask JS -->
    <script src="{{URL::asset('build/plugins/imask/imask.min.js')}}"></script>
    <script src="{{URL::asset('build/js/mask.js')}}"></script>
@endif

@if (Route::is('form-pickers'))
    <script src="{{URL::asset('build/plugins/flatpickr/flatpickr.js')}}"></script>
    <!-- Page JS -->
    <script src="{{URL::asset('build/js/forms-pickers.js')}}"></script>
@endif

@if (Route::is(['form-select', 'form-select2', 'tenant.company-structure.companies.create', 'tenant.company-structure.companies.edit', 'tenant.company-structure.departments.create', 'tenant.company-structure.departments.edit', 'tenant.company-structure.sub-departments.create', 'tenant.company-structure.sub-departments.edit', 'tenant.data-policy.create', 'tenant.data-policy.edit']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/custom-tom-select.js')}}"></script>
@endif

@if (Route::is('form-validation'))
    <!-- Daterangepikcer JS -->
    <script src="{{URL::asset('build/js/form-validation.js')}}"></script>
@endif

@if (Route::is('form-wizard'))
    <!-- Wizard JS -->
    <script src="{{URL::asset('build/js/form-wizard.js')}}"></script>
@endif

@if (Route::is('hr-dashboard'))
    <script src="{{URL::asset('build/plugins/chartjs/chartjs-plugin-datalabels.min.js')}}"></script>
@endif

@if (Route::is(['job-grid-2', 'job-list-2']))
    <!-- Rangeslider JS -->
    <script src="{{URL::asset('build/plugins/noui/nouislider.min.js')}}"></script>
    <script src="{{URL::asset('build/plugins/noui/rangeslider-init.js')}}"></script>
@endif

@if (Route::is(['leads', 'leads-grid']))
    <script src="{{URL::asset('build/js/leads.js')}}"></script>
@endif

@if (Route::is('leave-settings'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/leave-settings.js')}}"></script>
@endif

@if (Route::is('notes'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/notes.js')}}"></script>
@endif

@if (Route::is('project-details'))
    <script src="{{URL::asset('build/js/projects.js')}}"></script>
@endif

@if (Route::is(['reset-password', 'reset-password-2', 'reset-password-3', 'success-3']))
    <!-- Validation-->
    <script src="{{URL::asset('build/js/validation.js')}}"></script>
@endif

@if (Route::is('salary-settings'))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/salary-settings.js')}}"></script>
@endif

@if (Route::is('social-feed'))
    <script src="{{URL::asset('build/js/social-feed.js')}}"></script>
@endif

@if (Route::is('ui-clipboard'))
    <!-- Clipboard JS -->
    <script src="{{URL::asset('build/plugins/clipboard/clipboard.min.js')}}"></script>
    <script src="{{URL::asset('build/js/clipboard.js')}}"></script>
@endif

@if (Route::is('ui-dragula'))
    <!-- Dragula Js-->
    <script src="{{URL::asset('build/plugins/dragula/dragula.min.js')}}"></script>
    <script src="{{URL::asset('build/js/dragula.js')}}"></script>
@endif

@if (Route::is('ui-lightbox'))
    <script src="{{URL::asset('build/plugins/lightbox/lightbox.js')}}"></script>
@endif

@if (Route::is('ui-modals'))
    <!-- Modal JS -->
    <script src="{{URL::asset('build/js/modal.js')}}"></script>
@endif

@if (Route::is(['ui-popovers', 'ui-tooltips']))
    <!-- Custom JS -->
    <script src="{{URL::asset('build/js/popover.js')}}"></script>
@endif

@if (Route::is('ui-sweetalerts'))
    <!-- Sweet Alerts js -->
    <script src="{{URL::asset('build/plugins/sweetalert2/sweetalert2.min.js')}}"></script>
    <script src="{{URL::asset('build/js/sweetalerts.js')}}"></script>
@endif

    <script src="{{URL::asset('build/js/script.js')}}"></script>
