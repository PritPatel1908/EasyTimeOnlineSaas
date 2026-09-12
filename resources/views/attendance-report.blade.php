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
					<h2 class="mb-1">Attendance Report</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Reports
							</li>
							<li class="breadcrumb-item active" aria-current="page">Attendance Report</li>
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
				<div class="col-xl-6 d-flex">
					<div class="row flex-fill">
						<!-- Total Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center overflow-hidden mb-2">
										<div class="attendence-icon">
											<span><i class="ti ti-calendar text-primary"></i></span>
										</div>
										<div class="ms-2 overflow-hidden">
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Working Days</p>
											<h4>25</h4>
										</div>
									</div>
									<div class="attendance-report-bar mb-2">
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="height: 5px;">
											<div class="progress-bar bg-success" style="width: 85%"></div>
										</div>
									</div>
									<div>
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+20.01%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<!-- /Total Companies -->

						<!-- Total Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center overflow-hidden mb-2">
										<div class="attendence-icon">
											<span><i class="ti ti-calendar text-info"></i></span>
										</div>
										<div class="ms-2 overflow-hidden">
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Leave Taken</p>
											<h4>12</h4>
										</div>
									</div>
									<div class="attendance-report-bar mb-2">
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="height: 5px;">
											<div class="progress-bar bg-success" style="width: 85%"></div>
										</div>
									</div>
									<div>
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+20.01%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<!-- /Total Companies -->

						<!-- Inactive Companies -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center overflow-hidden mb-2">
										<div class="attendence-icon">
											<span><i class="ti ti-calendar text-pink"></i></span>
										</div>
										<div class="ms-2 overflow-hidden">
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Holidays</p>
											<h4>6</h4>
										</div>
									</div>
									<div class="attendance-report-bar mb-2">
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="height: 5px;">
											<div class="progress-bar bg-success" style="width: 85%"></div>
										</div>
									</div>
									<div>
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+20.01%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<!-- /Inactive Companies -->

						<!-- Company Location -->
						<div class="col-lg-6 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center overflow-hidden mb-2">
										<div class="attendence-icon">
											<span><i class="ti ti-calendar text-warning"></i></span>
										</div>
										<div class="ms-2 overflow-hidden">
											<p class="fs-12 fw-normal mb-1 text-truncate">Total Halfdays</p>
											<h4>5</h4>
										</div>
									</div>
									<div class="attendance-report-bar mb-2">
										<div class="progress" role="progressbar" aria-label="Success example"
											aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"
											style="height: 5px;">
											<div class="progress-bar bg-success" style="width: 85%"></div>
										</div>
									</div>
									<div>
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate"><span
												class="text-success fs-12 d-flex align-items-center me-1"><i
													class="ti ti-arrow-wave-right-up me-1"></i>+20.01%</span>from
											last month</p>
									</div>
								</div>
							</div>
						</div>
						<!-- /Company Location -->
					</div>
				</div>
				<div class="col-xl-6">
					<div class="card">
						<div class="card-header border-0 pb-0">
							<div class="d-flex flex-wrap justify-content-between align-items-center">
								<div class="d-flex align-items-center ">
									<span class="me-2"><i class="ti ti-chart-line text-danger"></i></span>
									<h5>Attendance </h5>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="dropdown-toggle btn btn-sm fs-12 btn-white d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										This Year
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-2">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2023</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2022</a>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="card-body py-0 px-2">
							<div id="attendance-report"> </div>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Employee Attendance</h5>
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
								Select Status
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Present</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Absent</a>
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
									<th>Name</th>
									<th>Date</th>
									<th>Check In</th>
									<th>Status</th>
									<th>Check Out</th>
									<th>Break</th>
									<th>Late</th>
									<th>Overtime</th>
									<th>Production Hours</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-32.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Anthony Lewis</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:32 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>06:45 PM</td>
									<td>30 Min</td>
									<td>32 Min</td>
									<td>20 Min</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>8.55 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-09.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Brian Villalobos</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>06:12 PM</td>
									<td>20 Min</td>
									<td>-</td>
									<td>45 Min</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>7.54 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-01.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Harvey Smith</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>06:13 PM</td>
									<td>50 Min</td>
									<td>-</td>
									<td>33 Min</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>8.45 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Stephan Peralt</a></p>
												<span class="fs-12">Executive Officer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>06:23 PM</td>
									<td>41 Min</td>
									<td>-</td>
									<td>50 Min</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>8.55 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-33.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Doglas Martini</a></p>
												<span class="fs-12">Manager</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>06:43 PM</td>
									<td>23 Min</td>
									<td>-</td>
									<td>10 Min</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>8.22 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-02.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Linda Ray</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>07:15 PM</td>
									<td>03 Min</td>
									<td>-</td>
									<td>-</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>8.32 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-35.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Elliot Murray</a></p>
												<span class="fs-12">Finance</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>07:13 PM</td>
									<td>32 Min</td>
									<td>-</td>
									<td>-</td>
									<td>
										<span class="badge badge-info d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>9.15 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-36.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Rebecca Smtih</a></p>
												<span class="fs-12">Executive</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>09:17 PM</td>
									<td>14 Min</td>
									<td>12 Min</td>
									<td>-</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>9.25 Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-37.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Connie Waters</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>09:00 AM</td>
									<td>
										<span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Present
										</span>
									</td>
									<td>08:15 PM</td>
									<td>12 Min</td>
									<td>-</td>
									<td>-</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>8.35Hrs
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="d-flex align-items-center">
											<a href="#" class="avatar avatar-md" data-bs-toggle="modal"
												data-bs-target="#view_details"><img
													src="{{URL::asset('build/img/users/user-38.jpg')}}"
													class="img-fluid rounded-circle" alt="img"></a>
											<div class="ms-2">
												<p class="text-dark mb-0"><a href="#" data-bs-toggle="modal"
														data-bs-target="#view_details">Lori Broaddus</a></p>
												<span class="fs-12">Developer</span>
											</div>
										</div>
									</td>
									<td>14 Jan 2024</td>
									<td>-</td>
									<td>
										<span
											class="badge badge-soft-danger d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Absent
										</span>
									</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>
										<span class="badge badge-danger d-inline-flex align-items-center badge-sm">
											<i class="ti ti-clock-hour-11 me-1"></i>0.00 Hrs
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