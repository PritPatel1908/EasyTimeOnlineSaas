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
							<li class="breadcrumb-item active" aria-current="page">Settings</li>
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
									class="d-inline-flex align-items-center rounded py-2 px-3">Localization</a>
								<a href="{{url('prefixes')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Prefixes</a>
								<a href="{{url('preferences')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Preferences</a>
								<a href="{{url('appearance')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Appearance</a>
								<a href="{{url('language')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Language</a>
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
						<div class="card-header px-0 mx-3">
							<h4>Language</h4>
						</div>
						<div class="card-body pb-0">
							<div class="card mb-3">
								<div class="card-header">
									<div class="row align-items-center g-3">
										<div class="col-lg-6 col-sm-4">
											<h6>Language</h6>
										</div>
										<div class="col-lg-6 col-sm-8">
											<div class="d-flex align-items-center justify-content-sm-end">
												<a href="{{url('language')}}"
													class="btn btn-sm btn-primary d-inline-flex align-items-center me-3">
													<i class="ti ti-arrow-left me-2"></i>
													Back to Translations
												</a>
												<a href="#"
													class="btn btn-sm btn-outline-dark d-inline-flex align-items-center">
													<img src="{{URL::asset('build/img/flags/ae.png')}}"
														class="me-2 avatar avatar-sm avatar-rounded" alt="Img">
													Arabic
												</a>
											</div>
										</div>
									</div>
								</div>
								<div class="card-body p-0">
									<div class="table-responsive">
										<table class="table">
											<thead class="thead-light">
												<tr>
													<th class="no-sort">
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox"
																id="select-all">
														</div>
													</th>
													<th>Module Name</th>
													<th>Total</th>
													<th>Complete</th>
													<th>Progress </th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="fw-medium fs-14">Employees</h6>
													</td>
													<td>1620</td>
													<td>1296</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="progress progress-xs" style="width: 120px;">
																<div class="progress-bar bg-success rounded"
																	role="progressbar" style="width: 100%;"
																	aria-valuenow="100" aria-valuemin="0"
																	aria-valuemax="100"></div>
															</div>
															<span class="d-inline-flex fs-12 ms-2">100%</span>
														</div>
													</td>
													<td>
														<a href="#" data-bs-toggle="modal"
															data-bs-target="#edit_language"
															class="btn btn-sm btn-icon border btn-light"><i
																class="ti ti-edit"></i></a>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="fw-medium fs-14">Clients</h6>
													</td>
													<td>1620</td>
													<td>972</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="progress progress-xs" style="width: 120px;">
																<div class="progress-bar bg-pink rounded"
																	role="progressbar" style="width: 70%;"
																	aria-valuenow="80" aria-valuemin="0"
																	aria-valuemax="100"></div>
															</div>
															<span class="d-inline-flex fs-12 ms-2">70%</span>
														</div>
													</td>
													<td>
														<a href="#" data-bs-toggle="modal"
															data-bs-target="#edit_language"
															class="btn btn-sm btn-icon border btn-light"><i
																class="ti ti-edit"></i></a>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="fw-medium fs-14">Projects</h6>
													</td>
													<td>1620</td>
													<td>810</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="progress progress-xs" style="width: 120px;">
																<div class="progress-bar bg-purple rounded"
																	role="progressbar" style="width: 40%;"
																	aria-valuenow="40" aria-valuemin="0"
																	aria-valuemax="100"></div>
															</div>
															<span class="d-inline-flex fs-12 ms-2">40%</span>
														</div>
													</td>
													<td>
														<a href="#" data-bs-toggle="modal"
															data-bs-target="#edit_language"
															class="btn btn-sm btn-icon border btn-light"><i
																class="ti ti-edit"></i></a>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="fw-medium fs-14">Tasks</h6>
													</td>
													<td>1620</td>
													<td>324</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="progress progress-xs" style="width: 120px;">
																<div class="progress-bar bg-skyblue rounded"
																	role="progressbar" style="width: 60%;"
																	aria-valuenow="40" aria-valuemin="0"
																	aria-valuemax="100"></div>
															</div>
															<span class="d-inline-flex fs-12 ms-2">60%</span>
														</div>
													</td>
													<td>
														<a href="#" data-bs-toggle="modal"
															data-bs-target="#edit_language"
															class="btn btn-sm btn-icon border btn-light"><i
																class="ti ti-edit"></i></a>
													</td>
												</tr>
											</tbody>
										</table>
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