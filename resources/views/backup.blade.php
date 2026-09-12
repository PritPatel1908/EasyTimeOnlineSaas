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
							<li class="breadcrumb-item active" aria-current="page">Backup</li>
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
									class="d-inline-flex align-items-center rounded py-2 px-3">Ban IP Address</a>
								<a href="{{url('backup')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Backup</a>
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
								<h4>Backup</h4>
							</div>
						</div>
						<div class="card-body pb-0 ">
							<div class="d-flex align-items-center justify-content-between">
								<nav class="nav nav-pills flex-column flex-sm-row" role="tablist">
									<a class="flex-sm-fill text-sm-center nav-link tab-dark active"
										data-bs-toggle="tab" role="tab" aria-current="page" href="#system-backup"
										aria-selected="true">System Backup</a>
									<a class="flex-sm-fill text-sm-center nav-link tab-dark" data-bs-toggle="tab"
										role="tab" aria-current="page" href="#database-backup"
										aria-selected="false">Database Backup</a>
								</nav>
								<a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal"
									data-bs-target="#generate_backup">Generate Backup</a>
							</div>
							<div class="tab-content mt-3">
								<div class="tab-pane show active text-muted" id="system-backup" role="tabpanel">
									<div class="card mb-3">
										<div
											class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
											<h5>System Backup List</h5>
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
															<th>File Name</th>
															<th>Date</th>
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
																<p class="fs-14 fw-medium text-dark">
																	Transaction_Logs_Backup_2024_09_Weekly.txt</p>
															</td>
															<td>11 Sep 2024</td>
															<td>
																<div class="action-icon d-inline-flex">
																	<a href="#" class="me-2"><i
																			class="ti ti-download"></i></a>
																	<a href="javascript:void(0);"
																		data-bs-toggle="modal"
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
																<p class="fs-14 fw-medium text-dark">
																	Scheduled_Tasks_Backup_Sep_2024.txt</p>
															</td>
															<td>11 Sep 2024</td>
															<td>
																<div class="action-icon d-inline-flex">
																	<a href="#" class="me-2"><i
																			class="ti ti-download"></i></a>
																	<a href="javascript:void(0);"
																		data-bs-toggle="modal"
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
																<p class="fs-14 fw-medium text-dark">
																	Employee_Session_Log_Sep_2024.txt</p>
															</td>
															<td>11 Sep 2024</td>
															<td>
																<div class="action-icon d-inline-flex">
																	<a href="#" class="me-2"><i
																			class="ti ti-download"></i></a>
																	<a href="javascript:void(0);"
																		data-bs-toggle="modal"
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
								<div class="tab-pane show text-muted" id="database-backup" role="tabpanel">
									<div class="card mb-3">
										<div
											class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
											<h5>Datebase Backup List</h5>
										</div>
										<div class="card-body p-0">
											<div class="table-responsive">
												<table class="table">
													<thead class="thead-light">
														<tr>
															<th class="no-sort">
																<div class="form-check form-check-md">
																	<input class="form-check-input" type="checkbox"
																		id="select-all2">
																</div>
															</th>
															<th>File Name</th>
															<th>Date</th>
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
																<p class="fs-14 fw-medium text-dark">
																	Full_Database_Backup_2024-09-11.sql</p>
															</td>
															<td>11 Sep 2024</td>
															<td>
																<div class="action-icon d-inline-flex">
																	<a href="#" class="me-2"><i
																			class="ti ti-download"></i></a>
																	<a href="javascript:void(0);"
																		data-bs-toggle="modal"
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
																<p class="fs-14 fw-medium text-dark">
																	Full_Database_Backup_2024-09-11.sql</p>
															</td>
															<td>11 Sep 2024</td>
															<td>
																<div class="action-icon d-inline-flex">
																	<a href="#" class="me-2"><i
																			class="ti ti-download"></i></a>
																	<a href="javascript:void(0);"
																		data-bs-toggle="modal"
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
																<p class="fs-14 fw-medium text-dark">
																	Full_Database_Backup_2024-09-11.sql</p>
															</td>
															<td>11 Sep 2024</td>
															<td>
																<div class="action-icon d-inline-flex">
																	<a href="#" class="me-2"><i
																			class="ti ti-download"></i></a>
																	<a href="javascript:void(0);"
																		data-bs-toggle="modal"
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
			</div>
		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection