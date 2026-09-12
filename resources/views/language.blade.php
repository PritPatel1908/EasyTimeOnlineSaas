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
							<li class="breadcrumb-item active" aria-current="page">Language</li>
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
							<div class="row g-3 align-items-center">
								<div class="col-md-6 col-sm-4">
									<h4>Language</h4>
								</div>
								<div class="col-md-6 col-sm-8">
									<div
										class="d-flex justify-content-sm-end align-items-center flex-wrap row-gap-2">
										<div class="me-3 flex-shrink-0">
											<select class="select">
												<option>Select Language</option>
												<option>English</option>
												<option>Spanish</option>
											</select>
										</div>
										<a href="{{url('add-language')}}" class="btn btn-primary">Add Language</a>
									</div>
								</div>
							</div>
						</div>
						<div class="card-body pb-0">
							<div class="card mb-3">
								<div class="card-header">
									<div class="row align-items-center g-3">
										<div class="col-sm-8">
											<h6>Language List</h6>
										</div>
										<div class="col-sm-4">
											<div class="position-relative search-input">
												<input type="text" class="form-control" placeholder="Search">
												<div class="search-addon">
													<span><i class="ti ti-search"></i></span>
												</div>
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
													<th>Language</th>
													<th>Code</th>
													<th>RTL</th>
													<th>Default </th>
													<th>Total</th>
													<th>Done</th>
													<th>Progress</th>
													<th>Status</th>
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
														<h6 class="d-flex align-items-center fw-medium">
															<img src="{{URL::asset('build/img/flags/us.png')}}"
																class="me-2 avatar avatar-sm avatar-rounded"
																alt="Img">
															English
														</h6>
													</td>
													<td>en</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch" checked>
														</div>
													</td>
													<td>1620</td>
													<td>1296</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="circle-progress" data-value="80">
																<span class="progress-left">
																	<span
																		class="progress-bar border-warning"></span>
																</span>
																<span class="progress-right">
																	<span
																		class="progress-bar border-warning"></span>
																</span>

															</div>
															<div class="progress-value ms-2">80%</div>
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="d-flex align-items-center">
															<a href="#"
																class="btn btn-sm btn-icon btn-light border me-2">
																<i class="ti ti-download"></i>
															</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Web</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">App</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Admin</a>
															<a href="#" data-bs-toggle="modal"
																data-bs-target="#delete_modal"
																class="btn btn-sm btn-icon btn-light border">
																<i class="ti ti-trash"></i>
															</a>
														</div>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="d-flex align-items-center fw-medium">
															<img src="{{URL::asset('build/img/flags/ae.png')}}"
																class="me-2 avatar avatar-sm avatar-rounded"
																alt="Img">
															Arabic
														</h6>
													</td>
													<td>ar</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>1620</td>
													<td>810</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="circle-progress" data-value="50">
																<span class="progress-left">
																	<span class="progress-bar border-purple"></span>
																</span>
																<span class="progress-right">
																	<span class="progress-bar border-purple"></span>
																</span>

															</div>
															<div class="progress-value ms-2">50%</div>
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="d-flex align-items-center">
															<a href="#"
																class="btn btn-sm btn-icon btn-light border me-2">
																<i class="ti ti-download"></i>
															</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Web</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">App</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Admin</a>
															<a href="#" data-bs-toggle="modal"
																data-bs-target="#delete_modal"
																class="btn btn-sm btn-icon btn-light border">
																<i class="ti ti-trash"></i>
															</a>
														</div>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="d-flex align-items-center fw-medium">
															<img src="{{URL::asset('build/img/flags/de.png')}}"
																class="me-2 avatar avatar-sm avatar-rounded"
																alt="Img">
															German
														</h6>
													</td>
													<td>de</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>1620</td>
													<td>972</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="circle-progress" data-value="70">
																<span class="progress-left">
																	<span
																		class="progress-bar border-skyblue"></span>
																</span>
																<span class="progress-right">
																	<span
																		class="progress-bar border-skyblue"></span>
																</span>

															</div>
															<div class="progress-value ms-2">70%</div>
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="d-flex align-items-center">
															<a href="#"
																class="btn btn-sm btn-icon btn-light border me-2">
																<i class="ti ti-download"></i>
															</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Web</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">App</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Admin</a>
															<a href="#" data-bs-toggle="modal"
																data-bs-target="#delete_modal"
																class="btn btn-sm btn-icon btn-light border">
																<i class="ti ti-trash"></i>
															</a>
														</div>
													</td>
												</tr>
												<tr>
													<td>
														<div class="form-check form-check-md">
															<input class="form-check-input" type="checkbox">
														</div>
													</td>
													<td>
														<h6 class="d-flex align-items-center fw-medium">
															<img src="{{URL::asset('build/img/flags/fr.png')}}"
																class="me-2 avatar avatar-sm avatar-rounded"
																alt="Img">
															French
														</h6>
													</td>
													<td>fr</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>1620</td>
													<td>324</td>
													<td>
														<div class="d-flex align-items-center">
															<div class="circle-progress" data-value="20">
																<span class="progress-left">
																	<span class="progress-bar border-danger"></span>
																</span>
																<span class="progress-right">
																	<span class="progress-bar border-danger"></span>
																</span>

															</div>
															<div class="progress-value ms-2">20%</div>
														</div>
													</td>
													<td>
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="d-flex align-items-center">
															<a href="#"
																class="btn btn-sm btn-icon btn-light border me-2">
																<i class="ti ti-download"></i>
															</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Web</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">App</a>
															<a href="{{url('language-web')}}"
																class="btn btn-sm border me-2">Admin</a>
															<a href="#" data-bs-toggle="modal"
																data-bs-target="#delete_modal"
																class="btn btn-sm btn-icon btn-light border">
																<i class="ti ti-trash"></i>
															</a>
														</div>
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