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
								Website Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page">Localization</li>
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
					<a class="nav-link active" href="{{url('business-settings')}}"><i
							class="ti ti-world-cog me-2"></i>Website Settings</a>
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
								<a href="{{url('business-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Business Settings</a>
								<a href="{{url('seo-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">SEO Settings</a>
								<a href="{{url('localization-settings')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Localization</a>
								<a href="{{url('prefixes')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Prefixes</a>
								<a href="{{url('preferences')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Preferences</a>
								<a href="{{url('appearance')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Appearance</a>
								<a href="{{url('language')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Language</a>
								<a href="{{url('authentication-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Authentication
									Settings</a>
								<a href="{{url('ai-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">AI Settings</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom mb-3 pb-3">
								<h4>Localization</h4>
							</div>
							<form action="{{url('localization-settings')}}">
								<div class="border-bottom mb-3">
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Language</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>English</option>
														<option>French</option>
														<option>Spanish</option>
													</select>
													<p
														class="fs-13 fw-normal mt-2 form-check form-check-md form-switch me-2">
														<input class="form-check-input me-2" type="checkbox"
															role="switch">Language Switcher
													</p>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Timezone</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>(UTC +5:30)</option>
														<option>(UTC+11:00) INR</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Date Format</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>YYYY-MM-DD</option>
														<option>MM/DD/YYYY</option>
														<option>DD/MM/YYYY</option>
														<option>YYYY/MM/DD</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Time Format</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>12 Hours</option>
														<option>24 Hours</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Financial Year</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>2024</option>
														<option>2023</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Starting Month</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>January</option>
														<option>February</option>
														<option>March</option>
														<option>April</option>
														<option>June</option>
														<option>July</option>
														<option>August</option>
														<option>September</option>
														<option>October</option>
														<option>November</option>
														<option>December</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-3">
									<h6 class="mb-3">Currency Information</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Currency</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>USD</option>
														<option>EURO</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Currency Symbol</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>$</option>
														<option>€</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Currency Position</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>100$</option>
														<option>$100</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Decimal Seperator</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>.</option>
														<option>,</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Thousand Seperator</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>.</option>
														<option>,</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-3">
									<h6 class="mb-3">Country Settings</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Countries Restriction</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>Allow All Countries</option>
														<option>Deny All Countries</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="border-bottom mb-3">
									<h6 class="mb-3">File Settings</h6>
									<div class="row">
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Allowed Files</label>
												</div>
												<div class="col-md-7">
													<select class="select">
														<option>Select</option>
														<option>jpg</option>
														<option>gif</option>
														<option>png</option>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row align-items-center mb-3">
												<div class="col-md-5">
													<label class="form-label mb-md-0">Max File Size</label>
												</div>
												<div class="col-md-7">
													<input type="text" class="form-control" placeholder="5000 MB">
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