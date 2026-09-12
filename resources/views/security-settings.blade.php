@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">

			<!-- Breadcrumb -->
			<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">Settings</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								General settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Security</li>
						</ol>
					</nav>
				</div>
				<div class="head-icons ms-2">
					<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
						data-bs-original-title="Collapse" id="collapse-header">
						<i class="ti ti-chevrons-up"></i>
					</a>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<ul class="nav nav-tabs nav-tabs-solid bg-transparent border-bottom mb-3">
				<li class="nav-item">
					<a class="nav-link active" href="{{url('profile-settings')}}"><i
							class="ti ti-settings me-2"></i>General Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('business-settings')}}"><i class="ti ti-world-cog me-2"></i>Website
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('salary-settings')}}"><i
							class="ti ti-device-ipad-horizontal-cog me-2"></i>App Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('email-settings')}}"><i class="ti ti-server-cog me-2"></i>System
						Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('payment-gateways')}}"><i
							class="ti ti-settings-dollar me-2"></i>Financial Settings</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="{{url('custom-css')}}"><i class="ti ti-settings-2 me-2"></i>Other
						Settings</a>
				</li>
			</ul>
			<div class="row">
				<div class="col-xl-3 theiaStickySidebar">
					<div class="card">
						<div class="card-body">
							<div class="d-flex flex-column list-group settings-list">
								<a href="{{url('profile-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Profile Settings</a>
								<a href="{{url('security-settings')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Security Settings</a>
								<a href="{{url('notification-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Notifications</a>
								<a href="{{url('connected-apps')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Connected Apps</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3 pb-3">
								<h4>Security Settings</h4>
							</div>
							<div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium mb-1">Password</h5>
										<div class="d-flex align-items-center">
											<p class="mb-0 me-2 pe-2 border-end">Set a unique password to protect
												the account</p>
											<p>Last Changed 03 Jan 2024, 09:00 AM</p>
										</div>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal"
											data-bs-target="#change-password">Change Pasword</a>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium mb-1">Two Factor Authentication</h5>
										<p>Receive codes via SMS or email every time you login</p>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-dark btn-sm">Enable</a>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium d-flex align-items-center mb-1">
											Google Authentication
											<span
												class="badge badge-xs ms-2 bg-outline-success rounded-pill d-flex align-items-center">
												<i class="ti ti-point-filled"></i>Connected
											</span>
										</h5>
										<p>Connect to Google</p>
									</div>
									<div class="mb-3">
										<div class="form-check form-check-md form-switch me-2">
											<input class="form-check-input me-2" type="checkbox" role="switch">
										</div>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium d-flex align-items-center mb-1">Phone Number
											Verification <span><i
													class="ti ti-discount-check-filled text-success ms-2"></i></span>
										</h5>
										<div class="d-flex align-items-center">
											<p class="mb-0 me-2 pe-2 border-end">The Phone Number associated with
												the account</p>
											<p>Verified Mobile Number : +99264710583</p>
										</div>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-outline-light btn-sm border me-2">Remove</a>
										<a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal"
											data-bs-target="#change-phone">Change </a>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium d-flex align-items-center mb-1">Email Verification
											<span><i
													class="ti ti-discount-check-filled text-success ms-2"></i></span>
										</h5>
										<div class="d-flex align-items-center">
											<p class="mb-0 me-2 pe-2 border-end">The email address associated with
												the account</p>
											<p>Verified Email : info@example.com</p>
										</div>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-outline-light btn-sm border me-2">Remove</a>
										<a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal"
											data-bs-target="#change-email">Change </a>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium mb-1">Device Management</h5>
										<p>The devices associated with the account</p>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal"
											data-bs-target="#device_management">Manage</a>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium mb-1">Account Activity</h5>
										<p>The activities of the account</p>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal"
											data-bs-target="#account_activity">View</a>
									</div>
								</div>
								<div
									class="d-flex justify-content-between align-items-center flex-wrap border-bottom mb-3">
									<div class="mb-3">
										<h5 class="fw-medium mb-1">Deactivate Account</h5>
										<p>This will shutdown your account. Your account will be reactive when you
											sign in again</p>
									</div>
									<div class="mb-3">
										<a href="#" class="btn btn-dark btn-sm">Deactivate</a>
									</div>
								</div>
								<div class="d-flex justify-content-between align-items-center flex-wrap row-gap-3">
									<div>
										<h5 class="fw-medium mb-1">Delete Account</h5>
										<p>Your account will be permanently deleted</p>
									</div>
									<div>
										<a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal"
											data-bs-target="#del-account">Delete</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection