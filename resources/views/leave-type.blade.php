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
								App Settings
							</li>
							<li class="breadcrumb-item active" aria-current="page"> Leave Type</li>
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
					<a class="nav-link active" href="{{url('salary-settings')}}"><i
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
								<a href="{{url('salary-settings')}}"
									class="d-inline-flex align-items-center rounded  py-2 px-3">Salary Settings</a>
								<a href="{{url('approval-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Approval Settings</a>
								<a href="{{url('invoice-settings')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Invoice Settings</a>
								<a href="{{url('leave-type')}}"
									class="d-inline-flex align-items-center rounded active py-2 px-3"><i
										class="ti ti-arrow-badge-right me-2"></i>Leave Type</a>
								<a href="{{url('custom-fields')}}"
									class="d-inline-flex align-items-center rounded py-2 px-3">Custom Fields</a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xl-9">
					<div class="card">
						<div class="card-body">
							<div class="border-bottom d-flex align-items-center justify-content-between pb-3 mb-3">
								<h4>Leave Type</h4>
								<div>
									<a href="#" data-bs-toggle="modal" data-bs-target="#add_leaves"
										class="btn btn-primary d-flex align-items-center"><i
											class="ti ti-circle-plus me-2"></i>Add Leave Type</a>
								</div>
							</div>
							<div class="card-body p-0">
								<div class="card mb-0">
									<div class="card-header d-flex align-items-center justify-content-between">
										<h6>Leave Type List</h6>
									</div>
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
													<th>Leave Type</th>
													<th>Leave Days</th>
													<th>Status</th>
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
													<td class="text-dark">Annual Leave</td>
													<td>12</td>

													<td><span class="badge badge-success"><i
																class="ti ti-point-filled"></i>Active</span></td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_leaves"><i
																	class="ti ti-edit"></i></a>
															<a href="#" data-bs-toggle="modal"
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
													<td class="text-dark">Medical Leave</td>
													<td>12</td>

													<td><span class="badge badge-success"><i
																class="ti ti-point-filled"></i>Active</span></td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_leaves"><i
																	class="ti ti-edit"></i></a>
															<a href="#" data-bs-toggle="modal"
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
													<td class="text-dark">Casual Leave</td>
													<td>12</td>

													<td><span class="badge badge-success"><i
																class="ti ti-point-filled"></i>Active</span></td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_leaves"><i
																	class="ti ti-edit"></i></a>
															<a href="#" data-bs-toggle="modal"
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
													<td class="text-dark">Other Leave</td>
													<td>12</td>

													<td><span class="badge badge-success"><i
																class="ti ti-point-filled"></i>Active</span></td>
													<td>
														<div class="action-icon d-inline-flex">
															<a href="#" class="me-2" data-bs-toggle="modal"
																data-bs-target="#edit_leaves"><i
																	class="ti ti-edit"></i></a>
															<a href="#" data-bs-toggle="modal"
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