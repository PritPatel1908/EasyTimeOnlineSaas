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
					<h2 class="mb-1">Task Report</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Reports
							</li>
							<li class="breadcrumb-item active" aria-current="page">Task Report</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="mb-2">
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
					<div class="head-icons ms-2">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="row">

				<!-- Total Exponses -->
				<div class="col-lg-6 col-md-6 d-flex">
					<div class="row flex-fill">
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div class="row align-items-center">
										<div class="col-8">
											<div>
												<span class="fs-14 fw-normal text-truncate mb-1">Total Tasks</span>
												<h5>800</h5>
											</div>
										</div>
										<div class="col-4">
											<p class="data-attributes">
												<span
													data-peity='{ "fill": ["#F26522", "rgba(67, 87, 133, .09)"], "innerRadius": 16, "radius": 32 }'>6/7</span>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div class="row align-items-center">
										<div class="col-8">
											<div>
												<span class="fs-14 fw-normal text-truncate mb-1">Total Tasks</span>
												<h5>800</h5>
											</div>
										</div>
										<div class="col-4">
											<p class="data-attributes">
												<span
													data-peity='{ "fill": ["#03C95A", "rgba(67, 87, 133, .09)"], "innerRadius": 16, "radius": 32 }'>4/7</span>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="row align-items-center">
										<div class="col-8">
											<div>
												<span class="fs-14 fw-normal text-truncate mb-1">Total Tasks</span>
												<h5>800</h5>
											</div>
										</div>
										<div class="col-4">
											<p class="data-attributes">
												<span
													data-peity='{ "fill": ["#FD3995", "rgba(67, 87, 133, .09)"], "innerRadius": 16, "radius": 32 }'>2/7</span>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body ">
									<div class="row align-items-center">
										<div class="col-8">
											<div>
												<span class="fs-14 fw-normal text-truncate mb-1">Total Tasks</span>
												<h5>800</h5>
											</div>
										</div>
										<div class="col-4">
											<p class="data-attributes">
												<span
													data-peity='{ "fill": ["#0DCAF0", "rgba(67, 87, 133, .09)"], "innerRadius": 16, "radius": 32 }'>1/7</span>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Total Exponses -->

				<!-- Total Exponses -->
				<div class="col-lg-6 col-md-6 d-flex">
					<div class="card flex-fill">
						<div class="card-header border-0">
							<div class="d-flex flex-wrap justify-content-between align-items-center">
								<div class="d-flex align-items-center ">
									<span class="me-2"><i class="ti ti-chart-pie text-danger"></i></span>
									<h5>Tasks</h5>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-sm fs-12 btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										Office Management App
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">PRO-001</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">PRO-002</a>
										</li>
										<li>
											<a href="javascript:void(0);"
												class="dropdown-item rounded-1">PRO-004</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body pt-0">
							<div class="row align-items-center">
								<div class="col-md-6 d-flex align-items-center justify-content-center">
									<div class="position-relative payment-total">
										<div id="task-reports"></div>
										<div class="task-total-content ">
											<p class="fs-16 fw-normal mb-0">Pending</p>
											<span class="display-3 fs-24 fw-bold text-skyblue">30%</span>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="row gy-4">
										<div class="col-md-6">
											<div class="d-flex task-report-icons">
												<span class="me-2"><i
														class="ti ti-arrow-badge-right-filled text-success"></i></span>
												<h6 class="fs-16">Completed <span class="fs-14 fw-normal">40%</span>
												</h6>
											</div>
										</div>
										<div class="col-md-6">
											<div class="d-flex task-report-icons">
												<span class="me-2"><i
														class="ti ti-arrow-badge-right-filled text-skyblue"></i></span>
												<h6 class="fs-16">Pending <span class="fs-14 fw-normal">30 %</span>
												</h6>
											</div>
										</div>
										<div class="col-md-6">
											<div class="d-flex task-report-icons">
												<span class="me-2"><i
														class="ti ti-arrow-badge-right-filled text-warning"></i></span>
												<h6 class="fs-16">Inprogress <span class="fs-14 fw-normal">20
														%</span></h6>
											</div>
										</div>
										<div class="col-md-6">
											<div class="d-flex task-report-icons">
												<span class="me-2"><i
														class="ti ti-arrow-badge-right-filled text-purple"></i></span>
												<h6 class="fs-16">On Hold <span class="fs-14 fw-normal">10 %</span>
												</h6>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Total Exponses -->
			</div>
			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Tasks List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
						<div class="me-3">
							<div class="input-icon position-relative">
								<span class="input-icon-addon">
									<i class="ti ti-calendar text-gray-9"></i>
								</span>
								<input type="text" class="form-control date-range bookingrange"
									placeholder="dd/mm/yyyy - dd/mm/yyyy">
							</div>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Priority
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Low</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Medium</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">High</a>
								</li>
							</ul>
						</div>
						<div class="dropdown me-3">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Select Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Completed</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Inprogress</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Onhold</a>
								</li>
							</ul>
						</div>
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								Sort By : Last 7 Days
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Descending</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card-body p-0">
					<div class="custom-datatable-filter table-responsive">
						<table class="table datatable">
							<thead class="thead-light">
								<tr>
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Task Name</th>
									<th>Project Name</th>
									<th>Created Date</th>
									<th>Due Date</th>
									<th>Priority</th>
									<th>Status</th>
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
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Patient Appointment Booking</a></h6>
										</div>
									</td>
									<td>
										Hospital Administration
									</td>
									<td>
										14 Jan 2024
									</td>
									<td>
										15 Jan 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Completed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Payment Gateway</a></h6>
										</div>
									</td>
									<td>
										Educational Platform
									</td>
									<td>
										21 Jan 2024
									</td>
									<td>
										25 Jan 2024
									</td>
									<td>
										<span class="badge badge-warning-transparent"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<span class="badge badge-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inprogress
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Doctor available module</a></h6>
										</div>
									</td>
									<td>
										Clinic Management
									</td>
									<td>
										20 Feb 2024
									</td>
									<td>
										22 Feb 2024
									</td>
									<td>
										<span class="badge badge-danger-transparent"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Completed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Video Conferencing Module</a></h6>
										</div>
									</td>
									<td>
										Chat & Call Mobile App
									</td>
									<td>
										15 Mar 2024
									</td>
									<td>
										17 Mar 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-warning d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>On Hold
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Private Chat Module</a></h6>
										</div>
									</td>
									<td>
										Travel Planning Website
									</td>
									<td>
										12 Apr 2024
									</td>
									<td>
										16 Apr 2024
									</td>
									<td>
										<span class="badge badge-danger-transparent"><i
												class="ti ti-point-filled me-1"></i>High</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Completed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Services List & Grid View</a></h6>
										</div>
									</td>
									<td>
										Service Booking Software
									</td>
									<td>
										20 Apr 2024
									</td>
									<td>
										21 Apr 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-skyblue d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Pending
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Car Detail Moodule</a></h6>
										</div>
									</td>
									<td>
										Car & Bike Rental Software
									</td>
									<td>
										06 Jul 2024
									</td>
									<td>
										06 Jul 2024
									</td>
									<td>
										<span class="badge badge-warning-transparent"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<span class="badge badge-purple d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Inprogress
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Location Module</a></h6>
										</div>
									</td>
									<td>
										Food Order App
									</td>
									<td>
										02 Sep 2024
									</td>
									<td>
										04 Sep 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Completed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Hotel List & Grid View</a></h6>
										</div>
									</td>
									<td>
										Hotel Booking App
									</td>
									<td>
										15 Nov 2024
									</td>
									<td>
										15 Nov 2024
									</td>
									<td>
										<span class="badge badge-warning-transparent"><i
												class="ti ti-point-filled me-1"></i>Medium</span>
									</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Completed
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<h6 class="fw-medium"><a href="#">Warehouse Module</a></h6>
										</div>
									</td>
									<td>
										POS Admin Software
									</td>
									<td>
										10 Dec 2024
									</td>
									<td>
										11 Dec 2024
									</td>
									<td>
										<span class="badge badge-success-transparent"><i
												class="ti ti-point-filled me-1"></i>Low</span>
									</td>
									<td>
										<span class="badge badge-skyblue d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Pending
										</span>
									</td>
								</tr>
							</tbody>
						</table>
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