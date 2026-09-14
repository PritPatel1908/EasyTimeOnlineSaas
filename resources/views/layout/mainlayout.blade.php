<!DOCTYPE html>

@if (Route::is(['layout-horizontal']))
	<html lang="en" data-layout="horizontal">
@elseif (Route::is(['layout-detached']))
	<html lang="en" data-layout="detached">
@elseif (Route::is(['layout-modern']))
	<html lang="en" data-layout="modern">
@elseif (Route::is(['layout-horizontal-overlay']))
	<html lang="en"  data-layout="horizontal-overlay">
@elseif (Route::is(['layout-two-column']))
	<html lang="en"  data-layout="twocolumn">
@elseif (Route::is(['layout-hovered']))
	<html lang="en" data-layout="layout-hovered">
@elseif (Route::is(['layout-box']))
	<html lang="en" data-layout="default" data-width="box">
@elseif (Route::is(['layout-horizontal-single']))
	<html lang="en"  data-layout="horizontal-single">
@elseif (Route::is(['layout-horizontal-box']))
	<html lang="en"  data-layout="horizontal-box">
@elseif (Route::is(['layout-horizontal-sidemenu']))
	<html lang="en"  data-layout="horizontal-sidemenu">
@elseif (Route::is(['layout-vertical-transparent']))
	<html lang="en" data-layout="transparent">
@elseif (Route::is(['layout-without-header']))
	<html lang="en" data-layout="without-header">
@elseif (Route::is(['layout-dark']))
	<html lang="en" data-theme="dark">
@else
	<html lang="en">
@endif

<head>
    @include('partials.title-meta')
    @include('partials.head-css')
    @vite(['resources/js/admin.js'])
</head>

@if (Route::is(['login', 'login-2', 'register', 'register-2', 'forgot-password', 'forgot-password-2', 'reset-password', 'reset-password-2', 'email-verification', 'email-verification-2', 'two-step-verification', 'two-step-verification-2', 'success', 'success-2', 'tenant.login']))
   <body class="bg-white">
@elseif (($errorPage ?? false) || Route::is(['login-3', 'register-3', 'forgot-password-3', 'reset-password-3', 'email-verification-3', 'two-step-verification-3', 'success-3', 'lock-screen', 'error-404', 'error-500', 'under-maintenance', 'under-construction']))
   <body class="bg-linear-gradiant">
@elseif (Route::is(['coming-soon']))
   <body class="bg-linear-gradiant d-flex align-items-center justify-content-center">
@elseif (Route::is(['layout-horizontal', 'layout-horizontal-overlay', 'layout-horizontal-single', 'layout-horizontal-box', 'layout-horizontal-fullwidth']))
   <body class="menu-horizontal">
@elseif (Route::is(['layout-hovered']))
   <body class="mini-sidebar expand-menu">
@elseif (Route::is(['layout-box']))
   <body class="mini-sidebar layout-box-mode">
@elseif (Route::is(['layout-vertical-transparent']))
   <body class="data-layout-transparent">
@elseif (Route::is(['layout-rtl']))
   <body class="layout-mode-rtl">
@else
   <body>
@endif

@if (($errorPage ?? false) || Route::is(['login', 'login-2', 'login-3', 'register', 'register-2', 'register-3', 'forgot-password', 'forgot-password-2', 'forgot-password-3', 'reset-password', 'reset-password-2', 'reset-password-3', 'email-verification', 'email-verification-2', 'email-verification-3', 'lock-screen', 'error-404', 'error-500', 'coming-soon', 'under-maintenance', 'under-construction', 'success', 'success-2', 'success-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3', 'job-grid-2', 'job-list-2', 'job-details', 'tenant.login']))
	<div id="global-loader" style="display: none;">
		<div class="page-loader"></div>
	</div>
@else
	<div id="global-loader">
		<div class="page-loader"></div>
	</div>
@endif

	<!-- Main Wrapper -->
	<div class="main-wrapper">

		@if (!(($errorPage ?? false) || Route::is(['login', 'login-2', 'login-3', 'register', 'register-2', 'register-3', 'forgot-password', 'forgot-password-2', 'forgot-password-3', 'reset-password', 'reset-password-2', 'reset-password-3', 'email-verification', 'email-verification-2', 'email-verification-3', 'lock-screen', 'error-404', 'error-500', 'coming-soon', 'under-maintenance', 'under-construction', 'success', 'success-2', 'success-3', 'two-step-verification', 'two-step-verification-2', 'two-step-verification-3', 'job-grid-2', 'job-list-2', 'job-details', 'tenant.login'])))
			@include('partials.topbar')
			@include('partials.sidebar')
			@include('partials.horizontal-sidebar')
			@include('partials.stocked-sidebar')
			@include('partials.twocolumn-sidebar')
		@endif

		@if (Route::is(['job-grid-2', 'job-list-2', 'job-details']))
			@include('partials.job-header')
		@endif

		@yield('content')

	</div>
	<!-- /Main Wrapper -->

	@component('components.modal-popup')
	@endcomponent

	@stack('scripts')

	@if (request()->attributes->get('tenantLicenseRequired', false))
		@include('tenant.partials.license-modal')
	@endif

@include('partials.vendor-scripts')

</body>
</html>
