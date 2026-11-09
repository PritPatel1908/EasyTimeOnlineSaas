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
								System Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Maintenance Mode</li>
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
					<a class="nav-link active" href="{{url('email-settings')}}"><i
							class="ti ti-server-cog me-2"></i>System Settings</a>
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
								<a href="{{url('email-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Email Settings</a>
								<a href="{{url('email-template')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Email Templates</a>
								<a href="{{url('sms-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Settings</a>
								<a href="{{url('sms-template')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SMS Templates</a>
								<a href="{{url('otp-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">OTP</a>
								<a href="{{url('gdpr')}}" class="d-inline-flex align-items-center rounded py-2 px-3">GDPR
									Cookies</a>
								<a href="{{url('maintenance-mode')}}"
									class="d-inline-flex align-items-center active rounded py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Maintenance Mode</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3 pb-3">
								<h4>Maintenance Mode</h4>
							</div>
							<form action="{{url('gdpr')}}">
								<div class="border-bottom mb-3">
									<div class="row">
										<div class="col-md-12">
											<div class="row align-items-center">
												<div class="col-lg-4">
													<div class="mb-3">
														<h6 class="fw-medium">Image</h6>
													</div>
												</div>
												<div class="col-lg-8">
													<div class="mb-3">
														<div
															class="d-flex align-items-center flex-wrap row-gap-3 w-100 rounded mb-4">
															<div
																class="d-flex align-items-center justify-content-center og-upload rounded border border-dashed me-2 flex-shrink-0 text-dark frames">
																<i class="ti ti-photo text-gray-3 fs-16"></i>
															</div>
															<div class="input-block mb-3 row">
																<div class="col-lg-12">
																	<input type="file" class="form-control">
																	<span class="form-text text-muted">Recommended
																		image size is 600px * 400px</span>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="row align-items-center">
												<div class="col-lg-4">
													<div class="mb-3">
														<h6 class="fw-medium">Description</h6>
													</div>
												</div>
												<div class="col-lg-8">
													<div class="mb-3">
														<div class="summernote">
															<p>Write a new comment, send your team notification by
																typing @ followed by their name</p>
														</div>
													</div>
												</div>
											</div>
											<div class="row align-items-center">
												<div class="col-lg-4">
													<div class="mb-3">
														<h6 class="fw-medium">Status</h6>
													</div>
												</div>
												<div class="col-lg-3">
													<div class="form-check form-switch mb-0">
														<input class="form-check-input mb-3" type="checkbox"
															role="switch">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-end">
									<button type="button" class="btn btn-outline-light border me-3">Cancel</button>
									<button type="submit" class="btn btn-primary">Save</button>
								</div>
							</form>
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