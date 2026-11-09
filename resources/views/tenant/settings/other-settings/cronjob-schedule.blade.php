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
							<li class="breadcrumb-item active" aria-current="page">Cronjob Schedule</li>
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
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Cronjob</a>
								<a href="{{url('storage-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Storage</a>
								<a href="{{url('ban-ip-address')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Ban IP Address</a>
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
							<div class="row g-3 align-items-center">
								<div class="col-md-6 col-sm-4">
									<h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0"><a
											href="{{url('cronjob')}}">
											<i class="ti ti-arrow-left me-2"></i>Cron Schedule</a>
									</h6>
								</div>
								<div class="col-md-6 col-sm-8">
									<div
										class="d-flex justify-content-sm-end align-items-center flex-wrap row-gap-2">
										<a href="{{url('add-language')}}" class="btn btn-primary" data-bs-toggle="modal"
											data-bs-target="#add_cronjobschedule"><i
												class="ti ti-circle-plus me-2"></i>Add Schedule</a>
									</div>
								</div>
							</div>
						</div>
						<div class="card-body pb-0">
							<div class="card mb-3">
								<div class="card-header">
									<div class="row align-items-center g-3">
										<div class="col-sm-8">
											<h6>Cron Schedule List</h6>
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
													<th>Name</th>
													<th>Interval</th>
													<th>Enable</th>
													<th></th>
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
														<h6 class="fw-medium">5 minutes</h6>
													</td>
													<td>5 seconds</td>
													<td>
														<div class="form-check form-check-md form-switch me-2">
															<input class="form-check-input me-2" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_cronjobschedule"><i
																	class="ti ti-edit"></i></a>
															<a href="javascript:void(0);" data-bs-toggle="modal"
																data-bs-target="#delete_modal"><i
																	class="ti ti-trash"></i></a>
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
														<h6 class="fw-medium">3 minutes</h6>
													</td>
													<td>3 seconds</td>
													<td>
														<div class="form-check form-check-md form-switch me-2">
															<input class="form-check-input me-2" type="checkbox"
																role="switch">
														</div>
													</td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_cronjobschedule"><i
																	class="ti ti-edit"></i></a>
															<a href="javascript:void(0);" data-bs-toggle="modal"
																data-bs-target="#delete_modal"><i
																	class="ti ti-trash"></i></a>
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