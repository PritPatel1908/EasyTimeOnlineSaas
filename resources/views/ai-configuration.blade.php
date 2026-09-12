@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">
		<div class="content">

			<!-- Breadcrumb -->
			<div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">AI Settings</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								AI Center
							</li>
							<li class="breadcrumb-item active" aria-current="page">AI Settings</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-file-export me-1"></i>Export
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1"><i
											class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
								</li>
							</ul>
						</div>
					</div>
					<a href="#" class="btn btn-primary-gradient mb-2"><i class="ti ti-refresh me-2"></i>Run AI
						Scan</a>
					<div class="ms-2 mb-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->
			<!-- Start Content -->

			<div class="customer-item-wrap">
				<div class="row g-4 justify-content-center">
					<div class="col-xxl-8">

						<div class="card">
							<div class="card-body">


								<ul class="nav nav-tabs nav-bordered border-0 nav-bordered-primary">
									<li class="nav-item">
										<a href="#ai-configuration" data-bs-toggle="tab" aria-expanded="false"
											class="nav-link active d-md-inline-block fw-bold">AI Configuration
										</a>
									</li>
									<li class="nav-item">
										<a href="#ai-model-settings" data-bs-toggle="tab" aria-expanded="true"
											class="nav-link d-md-inline-block fw-bold">AI Model Settings
										</a>
									</li>
									<li class="nav-item">
										<a href="#data-training-settings" data-bs-toggle="tab" aria-expanded="false"
											class="nav-link d-md-inline-block fw-bold">Data Training Settings
										</a>
									</li>
									<li class="nav-item">
										<a href="#ai-permissions" data-bs-toggle="tab" aria-expanded="false"
											class="nav-link d-md-inline-block fw-bold">AI Permissions
										</a>
									</li>
								</ul>

								<div class="tab-content">
									<div class="tab-pane fade" id="ai-model-settings">
										<p class="fw-bold text-dark mb-3 mt-3">Model Configuration</p>
										<!-- Row 1 -->
										<div class="border-bottom mb-3">
											<div class="row">
												<div class="col-md-6">
													<label class="mb-1 fw-medium text-dark">Model Type</label>
													<select class="select mb-2">
														<option value="s-1">Select</option>
														<option value="s-2">AI Attendance Insights</option>
														<option value="s-3">AI Payroll Forecast</option>
														<option value="s-4">AI Hiring Forecast</option>
														<option value="s-5">AI Team Performance Insights</option>
													</select>
													<p class="fs-13 mb-3 mt-1">Select the AI model to use for
														processing
														requests</p>
												</div>
												<div class="col-md-6">
													<label class="mb-1 fw-medium text-dark">Maximum Tokens</label>
													<select class="select mb-2">
														<option value="s-1">Select</option>
														<option value="s-2">256 Tokens</option>
														<option value="s-3">512 Tokens</option>
														<option value="s-4">1024 Tokens</option>
														<option value="s-5">2048 Tokens</option>
														<option value="s-5">3072 Tokens</option>
													</select>
													<p class="fs-13 mt-1">Maximum number of tokens to generate
														(1-4096)
													</p>
												</div>
											</div>
										</div>
										<!-- Row 2 -->
										<div class="row">
											<p class="fw-bold text-dark mb-2">Advanced Parameters</p>
											<div class="col-md-6">
												<label class="mb-1 fw-medium text-dark">Prediction Accuracy
													Level</label>
												<div class="d-flex align-items-center gap-2 mb-3">
													<div class="progress w-100" role="progressbar"
														aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
														<div class="progress-bar bg-primary" style="width: 75%">
														</div>
													</div>
													<span class="fw-medium">75%</span>
												</div>
												<p class="fs-13 mt-1">Higher accuracy uses more computational
													resources
												</p>
											</div>
											<div class="col-md-6">
												<label class="mb-1 fw-medium text-dark">Response Language</label>
												<select class="select mb-2">
													<option value="s-1">Select</option>
													<option value="en">English</option>
													<option value="es">Spanish</option>
													<option value="fr">French</option>
													<option value="de">German</option>
													<option value="it">Italian</option>
												</select>
												<p class="fs-13 mt-1">Maximum number of tokens to generate (1-4096)
												</p>
											</div>
										</div>
									</div>


									<div class="tab-pane fade show active" id="ai-configuration">
										<!-- Row 1 -->
										<div
											class="d-flex justify-content-between align-items-center mt-2 py-3 border-bottom">
											<div>
												<h6 class="mb-1">Natural Language Processing</h6>
												<p class="mb-0 fs-13">Enable AI-powered text analysis
													and understanding</p>
											</div>
											<div class="form-check form-switch mb-0 ps-0">
												<input class="form-check-input ms-0" type="checkbox" role="switch"
													checked>
											</div>
										</div>
										<!-- Row 2 -->
										<div
											class="d-flex justify-content-between align-items-center py-3 border-bottom">
											<div>
												<h6 class="mb-1">Computer Vision</h6>
												<p class="mb-0 fs-13">Image recognition and visual
													content analysis</p>
											</div>
											<div class="form-check form-switch mb-0 ps-0">
												<input class="form-check-input ms-0" type="checkbox" role="switch"
													checked>
											</div>
										</div>
										<!-- Row 3 -->
										<div
											class="d-flex justify-content-between align-items-center py-3 border-bottom">
											<div>
												<h6 class="mb-1">Content Generation</h6>
												<p class="mb-0 fs-13">Automated content creation and
													text generation</p>
											</div>
											<div class="form-check form-switch mb-0 ps-0">
												<input class="form-check-input ms-0" type="checkbox" role="switch">
											</div>
										</div>
										<!-- Row 4 -->
										<div
											class="d-flex justify-content-between align-items-center py-3 border-bottom">
											<div>
												<h6 class="mb-1">Predictive Analytics</h6>
												<p class="mb-0 fs-13">Data forecasting and trend
													prediction</p>
											</div>
											<div class="form-check form-switch mb-0 ps-0">
												<input class="form-check-input ms-0" type="checkbox" role="switch"
													checked>
											</div>
										</div>
										<!-- Row 5 -->
										<div class="d-flex justify-content-between align-items-center pt-3">
											<div>
												<h6 class="mb-1">Recommendation Engine</h6>
												<p class="mb-0 fs-13">Personalized content and product
													recommendations</p>
											</div>
											<div class="form-check form-switch mb-0 ps-0">
												<input class="form-check-input ms-0" type="checkbox" role="switch">
											</div>
										</div>
									</div>

									<div class="tab-pane fade" id="data-training-settings">
										<div class="col-md-6 my-3">
											<div class="d-flex justify-content-between align-items-center">
												<div>
													<label class="mb-1 text-dark fw-bold">Training
														Configuration</label>
													<p>Configure automatic retraining and data
														quality settings</p>
												</div>
											</div>
										</div>
										<div class="border-bottom mb-3">
											<div class="row mb-3">
												<!-- Row 1 -->
												<div class="col-md-6">
													<div class="d-flex justify-content-between align-items-center">
														<div>
															<label class="mb-1 text-dark fw-bold">Automatic
																Retraining</label>
															<p class="fs-13">Automatically retrain models with
																new data</p>
														</div>
														<div class="form-check form-switch mb-0 ps-0">
															<input class="form-check-input ms-0" type="checkbox"
																role="switch">
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<label class="mb-2 text-dark fw-bold">Retrain Frequency</label>
													<select class="select mb-2">
														<option value="s-1">Select</option>
														<option value="s-2">Every 6 Hours</option>
														<option value="s-3">Daily</option>
														<option value="s-4">Weekly</option>
														<option value="s-5">Monthly</option>
														<option value="s-6">Manual Only</option>
													</select>
												</div>
											</div>
											<!-- Row 2 -->
											<div class="row mb-3">
												<div class="col-md-6">
													<label class="mb-2 text-dark fw-bold">Response Language</label>
													<select class="select mb-2">
														<option value="s-1">Select</option>
														<option value="s-2">30 Days</option>
														<option value="s-4">60 Days</option>
														<option value="s-5">90 Days</option>
														<option value="s-6">180 Days</option>
														<option value="s-7">365 Days</option>
														<option value="s-7">Unlimited</option>
													</select>
													<p class="fs-13 mt-1">Maximum number of tokens to generate
														(1-4096)
													</p>
												</div>
												<div class="col-md-6">
													<label class="mb-2 text-dark fw-bold">Prediction Accuracy
														Level</label>
													<div class="d-flex align-items-center gap-2 mb-3">
														<div class="progress w-100" role="progressbar"
															aria-valuenow="75" aria-valuemin="0"
															aria-valuemax="100">
															<div class="progress-bar bg-primary" style="width: 75%">
															</div>
														</div>
														<span class="fw-medium">75%</span>
													</div>
													<p class="fs-13 mt-1">Higher accuracy uses more computational
														resources</p>
												</div>
											</div>
										</div>
										<div class="d-flex justify-content-between align-items-center mb-3">
											<div>
												<label class="mb-1 text-dark fw-bold">Training Datasets</label>
												<p>Manage datasets used for model training
												</p>
											</div>
											<button type="button" class="btn btn-dark btn-md bg-gradient">
												<i class="ti ti-upload me-1"></i>Update Dataset
											</button>
										</div>
										<div class="card">
											<div
												class="card-body d-flex justify-content-between align-items-center">
												<div>
													<p class="text-dark fw-bold mb-1">Customer Interactions
														Dataset
													</p>
													<p class="mb-0 small">
														2.4 GB
														<i class="ti ti-point-filled mx-1 text-danger"></i>
														125 records
														<i class="ti ti-point-filled mx-1 text-danger"></i>
														Updated 2 hours ago
													</p>
												</div>
												<div class="d-flex">
													<button class="btn btn-icon">
														<i class="ti ti-refresh"></i>
													</button>
													<button class="btn btn-icon">
														<i class="ti ti-trash"></i>
													</button>
												</div>
											</div>
										</div>
										<div class="card mb-0">
											<div
												class="card-body d-flex justify-content-between align-items-center">
												<div>
													<p class="text-dark fw-bold mb-1">Customer Interactions
														Dataset
													</p>
													<p class="mb-0 small">
														2.4 GB
														<i class="ti ti-point-filled mx-1 text-danger"></i>
														125 records
														<i class="ti ti-point-filled mx-1 text-danger"></i>
														Updated 2 hours ago
													</p>
												</div>
												<div class="d-flex">
													<button class="btn btn-icon">
														<i class="ti ti-refresh"></i>
													</button>
													<button class="btn btn-icon">
														<i class="ti ti-trash"></i>
													</button>
												</div>
											</div>
										</div>
									</div>


									<div class="tab-pane fade" id="ai-permissions">
											<div class="row g-2 align-items-center my-3">

												<!-- Search -->
												<div class="col-sm-8">
													<div class="input-group input-group-sm">
														<span class="input-group-text bg-white border-end-0">
															<i class="ti ti-search"></i>
														</span>
														<input type="text"
															class="form-control form-control-sm border-start-0"
															placeholder="Search users...">
													</div>
												</div>

												<!-- Admin Dropdown -->
												<div class="col-sm-2">
													<select class="select">
														<option>Admin</option>
														<option>HR Manager</option>
														<option>Recruitment Manager</option>
														<option>Payroll Manager</option>
														<option>Leave Manager</option>
														<option>Performance Manager</option>
														<option>Reports Analyst</option>
														<option>Employee</option>
														<option>Client</option>
														<option>Department Head</option>
													</select>
												</div>

												<!-- Add User Button -->
												<div class="col-sm-2">
													<a href="#" data-bs-toggle="modal" data-bs-target="#add_users"
														class="btn btn-dark text-nowrap d-flex align-items-center justify-content-center">
														<i class="ti ti-plus me-1"></i>Add User
													</a>
												</div>

											</div>


											<!-- Table -->
											<div class="table-responsive border rounded">
												<table class="table mb-0 align-middle">
													<thead class="table-light">
														<tr>
															<th>User</th>
															<th>AI Config</th>
															<th>Model Settings</th>
															<th>Data Training</th>
															<th>API Access</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>
																<div class="d-flex align-items-center gap-2">
																	<span
																		class="avatar avatar me-2 away avatar-rounded">
																		<img src="{{URL::asset('build/img/avatar/avatar-04.jpg')}}"
																			alt="avatar">
																	</span>
																	<a href="#" class="text-dark fw-bold">Anthony
																		Lewis</a>
																</div>
															</td>
															<td><input class="form-check-input" type="checkbox"
																	checked>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox"
																	checked>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
														</tr>
														<tr>
															<td>
																<div class="d-flex align-items-center gap-2">
																	<span
																		class="avatar avatar me-2 away avatar-rounded">
																		<img src="{{URL::asset('build/img/avatar/avatar-05.jpg')}}"
																			alt="avatar">
																	</span>
																	<a href="#" class="text-dark fw-bold">Brian
																		Villalobos
																	</a>
																</div>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox"
																	checked>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
														</tr>
														<tr>
															<td>
																<div class="d-flex align-items-center gap-2">
																	<span
																		class="avatar avatar me-2 away avatar-rounded">
																		<img src="{{URL::asset('build/img/avatar/avatar-06.jpg')}}"
																			alt="avatar">
																	</span>
																	<a href="#" class="text-dark fw-bold">Harvey
																		Smith</a>
																</div>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox"
																	checked>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
														</tr>
														<tr>
															<td>
																<div class="d-flex align-items-center gap-2">
																	<span
																		class="avatar avatar me-2 away avatar-rounded">
																		<img src="{{URL::asset('build/img/avatar/avatar-07.jpg')}}"
																			alt="avatar">
																	</span>
																	<a href="#" class="text-dark fw-bold">Doglas
																		Martini</a>
																</div>
															</td>
															<td><input class="form-check-input" type="checkbox"
																	checked>
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox">
															</td>
															<td><input class="form-check-input" type="checkbox"
																	checked>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>

												</div>
											</div>
									</div> <!-- end col -->

								</div><!-- end tab-content -->

							</div><!-- end card-body -->
						</div><!-- end card -->

						<!-- Footer Buttons -->
						<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
							<p class="mb-0">Last Updated : 15 May 2026
							</p>
							<div class="d-flex gap-2">
								<button type="button"
									class="btn bg-white-gradient btn-white btn-effect">Cancel</button>
								<button type="button" class="btn bg-primary-gradient btn-primary btn-effect">Save
									Changes</button>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div><!-- end content -->
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection