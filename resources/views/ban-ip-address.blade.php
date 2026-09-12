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
								Other Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page"> Ban IP address</li>
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
					<a class="nav-link" href="{{url('profile-settings')}}"><i class="ti ti-settings me-2"></i>General
						Settings</a>
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
					<a class="nav-link active" href="{{url('custom-css')}}"><i class="ti ti-settings-2 me-2"></i>Other
						Settings</a>
				</li>
			</ul>
			<div class="row">
				<div class="col-xl-3 theiaStickySidebar">
					<div class="card">
						<div class="card-body">
							<div class="d-flex flex-column list-group settings-list">
								<a href="{{url('custom-css')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Custom CSS</a>
								<a href="{{url('custom-js')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Custom JS</a>
								<a href="{{url('cronjob')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Cronjob</a>
								<a href="{{url('storage-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Storage</a>
								<a href="{{url('ban-ip-address')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Ban IP Address</a>
								<a href="{{url('backup')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Backup</a>
								<a href="{{url('clear-cache')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Clear Cache</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-header px-0 mx-3">
							<div class="d-flex align-items-center justify-content-between">
								<h4>Ban IP Address</h4>
								<a href="#" class="btn btn-primary btn-sm d-flex align-items-center"
									data-bs-toggle="modal" data-bs-target="#add_ban"><i
										class="ti ti-circle-plus me-2"></i>Add IP Address</a>
							</div>
						</div>
						<div class="card-body pb-0 ">
							<div class="row bx-3">
								<div class="col-lg-6">
									<div class="card mb-3">
										<div class="card-body">
											<div
												class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
												<div class="d-flex align-items-center">
													<span class="d-inline-flex me-2"><i
															class="ti ti-ban"></i></span>
													<p class="fs-14 fw-medium text-dark">198.120.16.01</p>
												</div>
												<div class="d-flex align-items-center">
													<a href="#" class="link-default me-2" data-bs-toggle="modal"
														data-bs-target="#edit_ban"><i class="ti ti-edit"></i></a>
													<a href="#" class="link-default" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</div>
											<div>
												<p><span class="me-2"><i
															class="ti ti-info-circle"></i></span>Temporarily block
													to protect user accounts from internet fraudsters</p>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="card mb-3">
										<div class="card-body">
											<div
												class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
												<div class="d-flex align-items-center">
													<span class="d-inline-flex me-2"><i
															class="ti ti-ban"></i></span>
													<p class="fs-14 fw-medium text-dark">198.160.11.20</p>
												</div>
												<div class="d-flex align-items-center">
													<a href="#" class="link-default me-2" data-bs-toggle="modal"
														data-bs-target="#edit_ban"><i class="ti ti-edit"></i></a>
													<a href="#" class="link-default" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</div>
											<div>
												<p><span class="me-2"><i
															class="ti ti-info-circle"></i></span>Unauthorized access
													attempts, or other signs of a potential security</p>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="card mb-3">
										<div class="card-body">
											<div
												class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
												<div class="d-flex align-items-center">
													<span class="d-inline-flex me-2"><i
															class="ti ti-ban"></i></span>
													<p class="fs-14 fw-medium text-dark">198.123.10.2</p>
												</div>
												<div class="d-flex align-items-center">
													<a href="#" class="link-default me-2" data-bs-toggle="modal"
														data-bs-target="#edit_ban"><i class="ti ti-edit"></i></a>
													<a href="#" class="link-default" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</div>
											<div>
												<p><span class="me-2"><i
															class="ti ti-info-circle"></i></span>Attempts to scrape
													large amounts of HR data from the system without authorization.
												</p>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="card mb-3">
										<div class="card-body">
											<div
												class="d-flex align-items-center justify-content-between border-bottom mb-2 pb-2">
												<div class="d-flex align-items-center">
													<span class="d-inline-flex me-2"><i
															class="ti ti-ban"></i></span>
													<p class="fs-14 fw-medium text-dark">198.110.01.05</p>
												</div>
												<div class="d-flex align-items-center">
													<a href="#" class="link-default me-2" data-bs-toggle="modal"
														data-bs-target="#edit_ban"><i class="ti ti-edit"></i></a>
													<a href="#" class="link-default" data-bs-toggle="modal"
														data-bs-target="#delete_modal"><i
															class="ti ti-trash"></i></a>
												</div>
											</div>
											<div>
												<p><span class="me-2"><i class="ti ti-info-circle"></i></span>Found
													downloading or uploading inappropriate content</p>
											</div>
										</div>
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