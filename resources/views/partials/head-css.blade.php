    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{URL::asset('build/img/favicon.png')}}">

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{URL::asset('build/img/apple-touch-icon.png')}}">

@if (!Route::is(['coming-soon', 'email-verification', 'email-verification-2', 'email-verification-3', 'error-404', 'error-500', 'forgot-password', 'forgot-password-2', 'forgot-password-3', 'job-details', 'job-grid-2', 'job-list-2', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'lock-screen', 'login', 'login-2', 'login-3', 'register', 'register-2', 'register-3', 'reset-password', 'reset-password-2', 'reset-password-3', 'success', 'success-2', 'success-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3', 'under-construction', 'under-maintenance']))
    <!-- Theme Script js -->
    <script src="{{URL::asset('build/js/theme-script.js')}}"></script>
@endif

@if (!Route::is(['layout-rtl']))
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/bootstrap.min.css')}}">
@endif

@if (Route::is('layout-rtl'))
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/bootstrap.rtl.min.css')}}">
@endif

@if (!Route::is(['chart-apex', 'chart-c3', 'chart-flot', 'chart-js', 'chart-morris', 'data-tables', 'form-basic-inputs', 'form-checkbox-radios', 'form-elements', 'form-fileupload', 'form-floating-labels', 'form-grid-gutters', 'form-horizontal', 'form-input-groups', 'form-mask', 'form-pickers', 'form-select', 'form-select2', 'form-validation', 'form-vertical', 'form-wizard', 'icon-bootstrap', 'icon-flag', 'icon-fontawesome', 'icon-material', 'icon-pe7', 'icon-remix', 'icon-simpleline', 'icon-tabler', 'icon-themify', 'icon-typicon', 'icon-weather', 'tables-basic', 'ui-accordion', 'ui-alerts', 'ui-avatar', 'ui-badges', 'ui-breadcrumb', 'ui-buttons', 'ui-buttons-group', 'ui-cards', 'ui-carousel', 'ui-clipboard', 'ui-collapse', 'ui-dragula', 'ui-dropdowns', 'ui-grid', 'ui-images', 'ui-lightbox', 'ui-links', 'ui-list-groups', 'ui-modals', 'ui-nav-tabs', 'ui-offcanvas', 'ui-pagination', 'ui-placeholders', 'ui-popovers', 'ui-progress', 'ui-ratio', 'ui-scrollbar', 'ui-spinner', 'ui-sweetalerts', 'ui-toasts', 'ui-tooltips', 'ui-typography']))
    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/feather/feather.css')}}">
@endif

@if (!Route::is(['gallery', 'goal-tracking', 'goal-type', 'maintenance-mode', 'manage-jobs', 'tables-basic', 'task-board', 'task-details', 'task-report', 'tasks', 'tax-rates', 'taxes', 'tenant-support-tickets', 'tenant-ticket-details', 'termination', 'terms-condition', 'testimonials', 'ticket-details', 'tickets', 'tickets-grid', 'timeline', 'video-call', 'voice-call']))
    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/tabler-icons/tabler-icons.min.css')}}">
@endif

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/fontawesome/css/fontawesome.min.css')}}">
    <link rel="stylesheet" href="{{URL::asset('build/plugins/fontawesome/css/all.min.css')}}">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/flatpickr/flatpickr.min.css')}}">

@if (Route::is(['activity', 'agents', 'api-keys', 'aptitude-result', 'asset-categories', 'asset-dashboard', 'assets', 'attendance-admin', 'attendance-employee', 'attendance-report', 'blog-categories', 'blog-comments', 'blog-tags', 'budget-expenses', 'budget-revenues', 'budgets', 'call-history', 'campus-hiring', 'candidates', 'candidates-grid', 'categories', 'certification-tracking', 'chart-apex', 'chart-c3', 'chart-js', 'chart-morris', 'cities', 'client-details', 'clients', 'companies', 'companies-crm', 'contacts', 'countries', 'cronjob', 'custom-css', 'custom-js', 'daily-report', 'data-tables', 'deals', 'departments', 'designations', 'domain', 'employee-details', 'employee-report', 'employee-salary', 'employees', 'employees-grid', 'escalation-rules', 'estimates', 'expenses', 'expenses-report', 'experience-level', 'faq', 'file-manager', 'form-basic-inputs', 'form-checkbox-radios', 'form-elements', 'form-fileupload', 'form-floating-labels', 'form-grid-gutters', 'form-horizontal', 'form-input-groups', 'form-mask', 'form-select', 'form-select2', 'form-validation', 'form-vertical', 'goal-tracking', 'goal-type', 'group-video-call', 'holidays', 'icon-feather', 'icon-fontawesome', 'interview-questions', 'invoice', 'invoice-details', 'invoice-report', 'invoices', 'job-list', 'knowledgebase', 'knowledgebase-details', 'knowledgebase-view', 'leads', 'learning-analytics', 'leave-report', 'leave-settings', 'leave-type', 'leaves', 'leaves-employee', 'manage-jobs', 'notice-period-tracker', 'offer-approvals', 'overtime', 'packages', 'packages-grid', 'pages', 'payment-report', 'payments', 'payroll', 'payroll-deduction', 'payroll-overtime', 'payslip', 'payslip-report', 'performance-appraisal', 'performance-indicator', 'performance-review', 'permission', 'pipeline', 'policy', 'pricing', 'privacy-policy', 'probation-management', 'project-report', 'projects', 'projects-grid', 'promotion', 'provident-fund', 'purchase-transaction', 'refferals', 'resignation', 'resume-parsing', 'roles-permissions', 'schedule-timing', 'shift-swap-requests', 'shortlist-candidates', 'sla-policies', 'social-feed', 'states', 'storage-settings', 'subscription', 'tables-basic', 'task-board', 'task-details', 'task-report', 'tasks', 'taxes', 'tenant-support-tickets', 'tenant-ticket-details', 'tenant-usage-metrics', 'termination', 'terms-condition', 'testimonials', 'ticket-automation', 'ticket-details', 'ticket-reports', 'tickets', 'tickets-grid', 'timesheets', 'todo-list', 'trainers', 'training', 'training-type', 'ui-buttons-group', 'ui-nav-tabs', 'ui-pagination', 'ui-placeholders', 'ui-progress', 'ui-toasts', 'ui-tooltips', 'ui-typography', 'user-report', 'users', 'work-from-home']))
    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/gridjs/theme-mermaid.min.css')}}">
@endif

@if (Route::is(['activity', 'ai-attendance-insights', 'ai-payroll-forecast', 'analytics', 'asset-dashboard', 'blog-2', 'blogs', 'client-details', 'company-details', 'contact-details', 'contacts', 'dashboard', 'deals-details', 'email-template', 'employee-dashboard', 'employee-details', 'file-manager', 'gdpr', 'help-desk-dashboard', 'incoming-call', 'index', '/', 'invoice-settings', 'kanban-view', 'layout-box', 'layout-dark', 'layout-detached', 'layout-horizontal', 'layout-horizontal-box', 'layout-horizontal-fullwidth', 'layout-horizontal-overlay', 'layout-horizontal-sidemenu', 'layout-horizontal-single', 'layout-hovered', 'layout-modern', 'layout-rtl', 'layout-stacked', 'layout-two-column', 'layout-vertical-transparent', 'layout-without-header', 'leads', 'leads-details', 'leads-grid', 'maintenance-mode', 'notes', 'outgoing-call', 'payment-gateways', 'privacy-policy', 'project-details', 'projects', 'projects-grid', 'recruitment-dashboard', 'sms-settings', 'sms-template', 'social-feed', 'task-board', 'task-details', 'tasks', 'terms-condition', 'todo', 'todo-list', 'video-call', 'voice-call']))
    <!-- Summernote CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/quill/quill.snow.css')}}">
@endif

@if (!Route::is(['file-manager']))
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/tom-select/tom-select.bootstrap5.min.css')}}">
@endif

@if (Route::is(['add-invoices', 'edit-invoices', 'file-manager', 'group-video-call', 'invoice-details', 'invoices', 'manage-jobs', 'payslip', 'promotion', 'resignation', 'termination']))
    <!-- Player CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/plyr.css')}}">
@endif

@if (Route::is(['attendance-dashboard', 'index', '/', 'payroll-dashboard', 'recruitment-dashboard']))
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/flatpickr/plugins/monthSelect/style.css')}}">
@endif

@if (Route::is(['chart-apex', 'chart-c3', 'chart-js', 'chart-morris', 'data-tables', 'form-basic-inputs', 'form-checkbox-radios', 'form-elements', 'form-fileupload', 'form-floating-labels', 'form-grid-gutters', 'form-horizontal', 'form-input-groups', 'form-mask', 'form-select', 'form-select2', 'form-validation', 'form-vertical', 'icon-feather', 'icon-flag', 'icon-fontawesome', 'icon-ionic', 'icon-material', 'icon-pe7', 'icon-simpleline', 'icon-themify', 'icon-typicon', 'icon-weather', 'tables-basic', 'ui-accordion', 'ui-alerts', 'ui-avatar', 'ui-badges', 'ui-breadcrumb', 'ui-buttons', 'ui-buttons-group', 'ui-cards', 'ui-carousel', 'ui-collapse', 'ui-dropdowns', 'ui-grid', 'ui-images', 'ui-lightbox', 'ui-links', 'ui-list-groups', 'ui-modals', 'ui-nav-tabs', 'ui-offcanvas', 'ui-pagination', 'ui-placeholders', 'ui-popovers', 'ui-progress', 'ui-sweetalerts', 'ui-toasts', 'ui-typography']))
    <!-- animation CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/animate.css')}}">
@endif

@if (Route::is('chart-c3'))
    <!-- ChartC3 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/c3-chart/c3.min.css')}}">
@endif

@if (Route::is(['email-reply', 'gallery', 'project-details', 'search-result', 'social-feed', 'ui-lightbox', 'ui-ratio']))
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/lightbox/glightbox.min.css')}}">
@endif

@if (Route::is(['file-manager', 'notes', 'project-details', 'social-feed', 'task-details']))
    <!-- Swiper -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/swiper/swiper-bundle.min.css')}}">
@endif

@if (Route::is(['form-pickers', 'icon-bootstrap', 'icon-remix', 'icon-tabler', 'ui-clipboard']))
    <!-- Dragula CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/dragula/css/dragula.min.css')}}">
@endif

@if (Route::is(['form-pickers', 'icon-tabler']))
    <link rel="stylesheet" href="{{URL::asset('build/plugins/flatpickr/flatpickr.css')}}" />
@endif

@if (Route::is('form-pickers'))
    <link rel="stylesheet" href="{{URL::asset('build/plugins/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
    <link rel="stylesheet" href="{{URL::asset('build/plugins/jquery-timepicker/jquery-timepicker.css')}}" />
@endif

@if (Route::is(['form-pickers', 'icon-remix', 'icon-tabler']))
    <link rel="stylesheet" href="{{URL::asset('build/plugins/pickr/pickr-themes.css')}}" />
@endif

@if (Route::is(['form-vertical', 'tables-basic', 'ui-alerts', 'ui-nav-tabs', 'ui-sweetalerts']))
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/css/feather.css')}}">
@endif

@if (Route::is(['gallery', 'goal-tracking', 'goal-type', 'maintenance-mode', 'manage-jobs', 'tables-basic', 'task-board', 'task-details', 'task-report', 'tasks', 'tax-rates', 'taxes', 'tenant-support-tickets', 'tenant-ticket-details', 'termination', 'terms-condition', 'testimonials', 'ticket-details', 'tickets', 'tickets-grid', 'timeline', 'video-call', 'voice-call']))
    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/tabler-icons/tabler-icons.css')}}">
@endif

@if (Route::is('icon-bootstrap'))
    <!-- Bootstrap Icon CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/bootstrap/bootstrap-icons.min.css')}}">
@endif

@if (Route::is('icon-flag'))
    <!-- Pe7 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/flags/flags.css')}}">
@endif

@if (Route::is('icon-ionic'))
    <!-- Ionic CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/ionic/ionicons.css')}}">
@endif

@if (Route::is('icon-material'))
    <!-- Material CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/material/materialdesignicons.css')}}">
@endif

@if (Route::is('icon-pe7'))
    <!-- Pe7 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/pe7/pe-icon-7.css')}}">
@endif

@if (Route::is(['icon-remix', 'icon-tabler']))
    <!-- Remix Icon CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/remix/fonts/remixicon.css')}}">
@endif

@if (Route::is('icon-simpleline'))
    <!-- Material CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/simpleline/simple-line-icons.css')}}">
@endif

@if (Route::is('icon-themify'))
    <!-- Pe7 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/themify/themify.css')}}">
@endif

@if (Route::is('icon-typicon'))
    <!-- Pe7 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/typicons/typicons.css')}}">
@endif

@if (Route::is('icon-weather'))
    <!-- Pe7 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/icons/weather/weathericons.css')}}">
@endif

@if (Route::is(['job-grid-2', 'job-list-2']))
    <!-- Rangeslider CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/noui/nouislider.min.css')}}">
@endif

@if (Route::is('ui-dragula'))
    <!-- Dragula CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/dragula/dragula.min.css')}}">
@endif

@if (Route::is(['ui-scrollbar', 'ui-spinner']))
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/scrollbar/scroll.min.css')}}">
@endif

@if (Route::is('ui-scrollbar'))
    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/simplebar/simplebar.min.css')}}">
@endif

@if (Route::is('ui-sweetalerts'))
    <!-- Sweetalert2 CSS -->
    <link rel="stylesheet" href="{{URL::asset('build/plugins/sweetalert2/sweetalert2.min.css')}}">
@endif

    <link rel="stylesheet" href="{{URL::asset('build/css/style.css')}}">
