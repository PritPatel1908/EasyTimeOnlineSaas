@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">

			<!-- Breadcrumb -->
			<div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">HR Dashboard</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">HR Dashboard</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-3 mb-2">
					<div class="avatar-list-stacked avatar-group-md">
						<a href="#" class="avatar avatar-rounded">
							<img class="border border-white" src="{{URL::asset('build/img/users/user-31.jpg')}}" alt="user">
						</a>
						<a href="#" class="avatar avatar-rounded">
							<img class="border border-white" src="{{URL::asset('build/img/users/user-32.jpg')}}" alt="user">
						</a>
						<a href="#" class="avatar avatar-rounded">
							<img class="border border-white" src="{{URL::asset('build/img/users/user-29.jpg')}}" alt="user">
						</a>
						<a href="#" class="avatar avatar-rounded">
							<img class="border border-white" src="{{URL::asset('build/img/users/user-56.jpg')}}" alt="user">
						</a>
						<a class="avatar bg-dark avatar-rounded fs-20 text-fixed-white" href="#">
							+
						</a>
					</div>
					<div class="input-icon position-relative">
						<span class="input-icon-addon">
							<i class="ti ti-calendar text-gray-9"></i>
						</span>
						<input type="text" class="form-control date-range bookingrange"
							placeholder="dd/mm/yyyy - dd/mm/yyyy">
					</div>
					<div class="dropdown">
						<a href="javascript:void(0);"
							class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
							data-bs-toggle="dropdown">
							<i class="ti ti-file-export me-1"></i>Yearly Report
						</a>
						<ul class="dropdown-menu  dropdown-menu-end p-3">
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly Report</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Yearly Report </a>
							</li>
						</ul>
					</div>
					<div class="dropdown">
						<a href="javascript:void(0);"
							class="dropdown-toggle btn btn-primary d-inline-flex align-items-center"
							data-bs-toggle="dropdown">
							Add New
						</a>
						<ul class="dropdown-menu  dropdown-menu-end p-3">
							<li>
								<a href="{{url('employees')}}" class="dropdown-item rounded-1">Employee</a>
							</li>
							<li>
								<a href="{{url('attendance-employee')}}" class="dropdown-item rounded-1">Attendance</a>
							</li>
							<li>
								<a href="{{url('leaves')}}" class="dropdown-item rounded-1">Leave</a>
							</li>
						</ul>
					</div>
					<div class="head-icons mt-2">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- start row -->
			<div class="row">

				<div class="col-xl-5 d-flex flex-column">
					<div class="card flex-fill mb-3">
						<div class="card-body">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Employee Status & Type</h2>
								<a href="{{url('employee-report')}}" class="btn btn-md btn-light">View All</a>
							</div>
							<div id="status-chart" class="mb-3"></div>
							<div class="row">
								<div class="col-4">
									<div class="text-center">
										<h3 class="main-title mb-1">1054</h3>
										<p class="d-inline-flex align-items-center mb-0"><span
												class="chart-line bg-primary me-1"></span>Full-Time</p>
									</div>
								</div>
								<div class="col-4">
									<div class="text-center">
										<h3 class="main-title mb-1">568</h3>
										<p class="d-inline-flex align-items-center mb-0"><span
												class="chart-line bg-secondary me-1"></span>Contract</p>
									</div>
								</div>
								<div class="col-4">
									<div class="text-center">
										<h3 class="main-title mb-1">80</h3>
										<p class="d-inline-flex align-items-center mb-0"><span
												class="chart-line bg-light me-1"></span>Probation</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card flex-fill">
						<div class="card-body pb-sm-2">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Leave Type Distribution</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar-due me-1 fs-14"></i>Monthly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-5">
									<div id="leave-chart"></div>
								</div>
								<div class="col-sm-7">
									<div>
										<div class="d-flex align-items-center justify-content-between mb-2">
											<p class="d-inline-flex align-items-center text-dark mb-0"><i
													class="ti ti-circle-filled text-primary-900 fs-7 me-1"></i>Sick
												Leave</p>
											<span
												class="badge fw-normal bg-light text-dark border rounded-pill fs-13">45</span>
										</div>
										<div class="d-flex align-items-center justify-content-between mb-2">
											<p class="d-inline-flex align-items-center text-dark mb-0"><i
													class="ti ti-circle-filled text-primary-800 fs-7 me-1"></i>Casual
												Leave</p>
											<span
												class="badge fw-normal bg-light text-dark border rounded-pill fs-13">68</span>
										</div>
										<div class="d-flex align-items-center justify-content-between mb-2">
											<p class="d-inline-flex align-items-center text-dark mb-0"><i
													class="ti ti-circle-filled text-primary-700 fs-7 me-1"></i>Unpaid
											</p>
											<span
												class="badge fw-normal bg-light text-dark border rounded-pill fs-13">12</span>
										</div>
									</div>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xl-7">
					<div class="card">
						<div class="card-body">

							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Overview Statistics</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar-due me-1 fs-14"></i>Monthly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="row g-3">

								<div class="col-md-6 d-flex">
									<div class="card shadow-none mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex align-items-center mb-3">
												<div
													class="avatar avatar-lg bg-primary rounded-circle flex-shrink-0">
													<i class="ti ti-users-group text-white fs-24"></i>
												</div>
												<div class="ms-2">
													<p class="fw-semibold text-truncate mb-0">Total Employees</p>
												</div>
											</div>
											<div class="d-flex align-items-center justify-content-between">
												<div>
													<h3 class="main-title mb-1">1,848</h3>
													<p class="fs-13 mb-0">Headcount Overview</p>
												</div>
												<div
													class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">
													+18%<span
														class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i
															class="ti ti-arrow-up-right fs-20"></i></span></div>
											</div>
										</div> <!-- end card -->
									</div> <!-- end card body -->
								</div> <!-- end col -->

								<div class="col-md-6 d-flex">
									<div class="card shadow-none mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex avatar-lg align-items-center mb-3">
												<div class="avatar bg-secondary rounded-circle flex-shrink-0">
													<i class="ti ti-users-plus text-white fs-24"></i>
												</div>
												<div class="ms-2">
													<p class="fw-semibold text-truncate mb-0">New Joinees</p>
												</div>
											</div>
											<div class="d-flex align-items-center justify-content-between">
												<div>
													<h3 class="main-title mb-1">1,248</h3>
													<p class="fs-13 mb-0">All Department</p>
												</div>
												<div
													class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">
													+22%<span
														class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i
															class="ti ti-arrow-up-right fs-20"></i></span></div>
											</div>
										</div> <!-- end card -->
									</div> <!-- end card body -->
								</div> <!-- end col -->

								<div class="col-md-6 d-flex">
									<div class="card shadow-none mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex align-items-center mb-3">
												<div class="avatar avatar-lg bg-dark rounded-circle flex-shrink-0">
													<i class="ti ti-clock-x text-white fs-24"></i>
												</div>
												<div class="ms-2">
													<p class="fw-semibold text-truncate mb-0">Late Arrivals Today
													</p>
												</div>
											</div>
											<div class="d-flex align-items-center justify-content-between">
												<div>
													<h3 class="main-title mb-1">12</h3>
													<p class="fs-13 mb-0">Delayed Logins Today</p>
												</div>
												<div
													class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">
													-16%<span
														class="bg-danger btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i
															class="ti ti-arrow-down-right fs-20"></i></span></div>
											</div>
										</div> <!-- end card -->
									</div> <!-- end card body -->
								</div> <!-- end col -->

								<div class="col-md-6 d-flex">
									<div class="card shadow-none mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex align-items-center mb-3">
												<div
													class="avatar avatar-lg bg-purple rounded-circle flex-shrink-0">
													<i class="ti ti-report-money text-white fs-24"></i>
												</div>
												<div class="ms-2">
													<p class="fw-semibold text-truncate mb-0">Total Payroll Cost</p>
												</div>
											</div>
											<div class="d-flex align-items-center justify-content-between">
												<div>
													<h3 class="main-title mb-1">$2.4M</h3>
													<p class="fs-13 mb-0">Payroll Outflow</p>
												</div>
												<div
													class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">
													+16%<span
														class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i
															class="ti ti-arrow-up-right fs-20"></i></span></div>
											</div>
										</div> <!-- end card -->
									</div> <!-- end card body -->
								</div> <!-- end col -->

							</div>

						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">

				<div class="col-xl-8 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Attendance Trend</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar-due me-1 fs-14"></i>Weekly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
								<div class="d-flex align-items-center flex-wrap gap-3">
									<div class="d-flex align-items-center pe-3 border-end">
										<h3 class="mb-0">82<span
												class="ms-2 fw-normal fs-14 text-default">On-Time</span></h3>
									</div>
									<div class="d-flex align-items-center pe-3 border-end">
										<h3 class="mb-0">11<span
												class="ms-2 fw-normal fs-14 text-default">Late</span></h3>
									</div>
									<div class="d-flex align-items-center">
										<h3 class="mb-0">6<span
												class="ms-2 fw-normal fs-14 text-default">Absent</span></h3>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<p class="mb-0"><i
											class="ti ti-square-rounded-filled text-primary fs-15 me-1"></i>Present
									</p>
									<p class="mb-0"><i
											class="ti ti-square-rounded-filled text-secondary fs-15 me-1"></i>Late
									</p>
									<p class="mb-0"><i
											class="ti ti-square-rounded-filled text-warning fs-15 me-1"></i>Absent
									</p>
								</div>
							</div>
							<div class="d-sm-flex align-items-center flex-sm-row flex-column">
								<div id="attendance-chart" class="w-100"></div>
								<div class="flex-shrink-0">
									<div class="border p-3 rounded text-center mb-3">
										<p class="mb-1">Max Working Hours</p>
										<h3 class="main-title mb-0">8.4 hrs</h3>
									</div>
									<div class="border p-3 rounded text-center mb-3">
										<p class="mb-1">Missed Punches</p>
										<h3 class="main-title mb-0">12</h3>
									</div>
									<div class="border p-3 rounded text-center mb-3">
										<p class="mb-1">Weekly Avg</p>
										<h3 class="main-title mb-0">97.2%</h3>
									</div>
								</div>
							</div>

						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xl-4 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-0">
								<h2 class="card-title mb-0">Top Employee Distribution</h2>
								<a href="{{url('employees')}}" class="btn btn-md btn-light">View All</a>
							</div>
							<div id="employee-distribution"></div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">

				<div class="col-xxl-4 col-xl-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Late Arrivals Today</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar-due me-1 fs-14"></i>Today
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div
								class="p-2 bg-light rounded border-bottom d-flex align-items-center justify-content-between mb-2">
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-26.jpg')}}" class="rounded-circle" alt="user">
									</a>
									<div class="ms-2">
										<p class="fs-14 fw-medium text-truncate mb-1"><a href="#">Jessica Brown</a>
										</p>
										<p class="fs-13">Customer Support</p>
									</div>
								</div>
								<div>
									<p class="fs-13 text-dark mb-1">10:15 AM</p>
									<span class="badge badge-danger-transparent rounded-pill">+45 Min</span>
								</div>
							</div>
							<div
								class="p-2 bg-light rounded border-bottom d-flex align-items-center justify-content-between mb-2">
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-31.jpg')}}" class="rounded-circle" alt="user">
									</a>
									<div class="ms-2">
										<p class="fs-14 fw-medium text-truncate mb-1"><a href="#">Amanda Lewis</a>
										</p>
										<p class="fs-13">HR Admin</p>
									</div>
								</div>
								<div>
									<p class="fs-13 text-dark mb-1">10:25 AM</p>
									<span class="badge badge-danger-transparent rounded-pill">+55 Min</span>
								</div>
							</div>
							<div
								class="p-2 bg-light rounded border-bottom d-flex align-items-center justify-content-between mb-2">
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-27.jpg')}}" class="rounded-circle" alt="user">
									</a>
									<div class="ms-2">
										<p class="fs-14 fw-medium text-truncate mb-1"><a href="#">James Clark</a>
										</p>
										<p class="fs-13">Sales</p>
									</div>
								</div>
								<div>
									<p class="fs-13 text-dark mb-1">10:00 AM</p>
									<span class="badge badge-danger-transparent rounded-pill">+30 Min</span>
								</div>
							</div>
							<div
								class="p-2 bg-light rounded border-bottom d-flex align-items-center justify-content-between mb-2">
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-56.jpg')}}" class="rounded-circle" alt="user">
									</a>
									<div class="ms-2">
										<p class="fs-14 fw-medium text-truncate mb-1"><a href="#">Amanda Davis</a>
										</p>
										<p class="fs-13">Administration</p>
									</div>
								</div>
								<div>
									<p class="fs-13 text-dark mb-1">09:40 AM</p>
									<span class="badge badge-danger-transparent rounded-pill">+20 Min</span>
								</div>
							</div>
							<div
								class="p-2 bg-light rounded border-bottom d-flex align-items-center justify-content-between mb-0">
								<div class="d-flex align-items-center">
									<a href="javascript:void(0);" class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/users/user-29.jpg')}}" class="rounded-circle" alt="user">
									</a>
									<div class="ms-2">
										<p class="fs-14 fw-medium text-truncate mb-1"><a href="#">Lisa Anderson</a>
										</p>
										<p class="fs-13">Finance</p>
									</div>
								</div>
								<div>
									<p class="fs-13 text-dark mb-1">09:35 AM</p>
									<span class="badge badge-danger-transparent rounded-pill">+05 Min</span>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xxl-4 col-xl-6 d-flex flex-column">
					<div class="card mb-3">
						<div class="card-body">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Recruitment Statistics</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar-due me-1 fs-14"></i>Weekly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="mb-4">
								<div class="row g-3">
									<div class="col-6 col-sm-4">
										<div class="text-center">
											<p class="mb-1">Applicants</p>
											<h2 class="mb-0">487</h2>
										</div>
									</div>
									<div class="col-6 col-sm-4">
										<div class="text-center">
											<p class="mb-1">Hired</p>
											<h2 class="mb-0">24</h2>
										</div>
									</div>
									<div class="col-6 col-sm-4">
										<div class="text-center">
											<p class="mb-1">Avg Time</p>
											<h2 class="mb-0">28 days</h2>
										</div>
									</div>
								</div>
							</div>
							<div class="progress-stacked bg-white statistic-progress mb-4">
								<div class="progress overflow-hidden" role="progressbar" aria-label="Segment one"
									aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" style="width: 45%">
									<div class="progress-bar bg-primary"></div>
								</div>
								<div class="progress overflow-hidden" role="progressbar" aria-label="Segment two"
									aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 25%">
									<div class="progress-bar bg-secondary"></div>
								</div>
								<div class="progress overflow-hidden" role="progressbar" aria-label="Segment three"
									aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 23%">
									<div class="progress-bar bg-pink"></div>
								</div>
								<div class="progress overflow-hidden" role="progressbar" aria-label="Segment four"
									aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
									<div class="progress-bar bg-success"></div>
								</div>
							</div>


							<div class="row g-3">
								<div class="col-sm-6">
									<div>
										<p class="d-inline-flex align-items-center text-dark fw-semibold mb-1"><span
												class="chart-line bg-primary me-2"></span>Applications</p>
										<p class="mb-0"><span class="text-dark fw-medium">40%</span> - 57 Employees
										</p>
									</div>
								</div>
								<div class="col-sm-6">
									<div>
										<p class="d-inline-flex align-items-center text-dark fw-semibold mb-1"><span
												class="chart-line bg-secondary me-2"></span>Screening</p>
										<p class="mb-0"><span class="text-dark fw-medium">20%</span> - 36 Employees
										</p>
									</div>
								</div>
								<div class="col-sm-6">
									<div>
										<p class="d-inline-flex align-items-center text-dark fw-semibold mb-1"><span
												class="chart-line bg-pink me-2"></span>Interview</p>
										<p class="mb-0"><span class="text-dark fw-medium">23%</span> - 64 Employees
										</p>
									</div>
								</div>
								<div class="col-sm-6">
									<div>
										<p class="d-inline-flex align-items-center text-dark fw-semibold mb-1"><span
												class="chart-line bg-success me-2"></span>Hired</p>
										<p class="mb-0"><span class="text-dark fw-medium">17%</span> - 18 Employees
										</p>
									</div>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->

					<div class="card bg-secondary shadow-none z-1 overflow-hidden">
						<div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
							<div>
								<p class="text-white mb-1">Employees in Training</p>
								<h2 class="text-white mb-0">80</h2>
							</div>
							<div class="d-inline-flex align-items-center gap-2">
								<div class="avatar-list-stacked avatar-group-md">
									<a href="#" class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/users/user-39.jpg')}}"
											alt="user">
									</a>
									<a href="#" class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/users/user-41.jpg')}}"
											alt="user">
									</a>
									<a href="#" class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/users/user-44.jpg')}}"
											alt="user">
									</a>
								</div>
								<div class="chartjs-wrapper-demo position-relative">
									<canvas id="training-chart" height="50" width="50"></canvas>
								</div>
							</div>
						</div> <!-- end card body -->
						<img src="{{URL::asset('build/img/bg/emp-bg.png')}}" alt="bg"
							class="img-fluid position-absolute top-0 start-1 z-n1">
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xxl-4 col-xl-12 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex justify-content-between flex-column">
							<div>
								<div
									class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
									<h2 class="card-title mb-0">Upcoming Interview</h2>
									<div class="dropdown">
										<a href="javascript:void(0);"
											class="border btn btn-white btn-md d-inline-flex align-items-center"
											data-bs-toggle="dropdown">
											<i class="ti ti-calendar-due me-1 fs-14"></i>Today
										</a>
										<ul class="dropdown-menu mt-2 p-3">
											<li>
												<a href="javascript:void(0);" class="dropdown-item rounded-1">
													Monthly
												</a>
											</li>
											<li>
												<a href="javascript:void(0);" class="dropdown-item rounded-1">
													Weekly
												</a>
											</li>
											<li>
												<a href="javascript:void(0);" class="dropdown-item rounded-1">
													Today
												</a>
											</li>
										</ul>
									</div>
								</div>
								<div
									class="p-3 rounded border border-start border-start-4 border-start-primary mb-3">
									<div
										class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
										<div>
											<p class="text-dark fw-semibold mb-1">UI/UX Design Interview</p>
											<p class="fs-13 mb-0">12:00 PM - 01:50 PM</p>
										</div>
										<div class="avatar-list-stacked avatar-group-md">
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-31.jpg')}}"
													alt="user">
											</a>
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-32.jpg')}}"
													alt="user">
											</a>
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-29.jpg')}}"
													alt="user">
											</a>
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-56.jpg')}}"
													alt="user">
											</a>
											<a class="avatar bg-light border avatar-rounded fs-16 text-dark fw-normal"
												href="#">
												+9
											</a>
										</div>
									</div>
									<div class="row g-2">
										<div class="col-sm-7">
											<a href="#"
												class="btn btn-white d-flex align-items-center justify-content-center w-100"><i
													class="ti ti-calendar-due me-1"></i>Add to Calendar</a>
										</div>
										<div class="col-sm-5">
											<a href="#"
												class="btn btn-light d-flex align-items-center justify-content-center w-100"><i
													class="ti ti-video me-1"></i>Join Now</a>
										</div>
									</div>
								</div>
								<div
									class="p-3 rounded border border-start border-start-4 border-start-secondary mb-3">
									<div
										class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
										<div>
											<p class="text-dark fw-semibold mb-1">Senior Developer React</p>
											<p class="fs-13 mb-0">03:00 PM - 04:00 PM</p>
										</div>
										<div class="avatar-list-stacked avatar-group-md">
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-39.jpg')}}"
													alt="user">
											</a>
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-41.jpg')}}"
													alt="user">
											</a>
											<a href="#" class="avatar avatar-rounded">
												<img class="border border-white" src="{{URL::asset('build/img/users/user-44.jpg')}}"
													alt="user">
											</a>
											<a class="avatar bg-light border avatar-rounded fs-16 text-dark fw-normal"
												href="#">
												+4
											</a>
										</div>
									</div>
									<div class="row g-2">
										<div class="col-sm-7">
											<a href="#"
												class="btn btn-white d-flex align-items-center justify-content-center w-100"><i
													class="ti ti-calendar-due me-1"></i>Add to Calendar</a>
										</div>
										<div class="col-sm-5">
											<a href="#"
												class="btn btn-light d-flex align-items-center justify-content-center w-100"><i
													class="ti ti-video me-1"></i>Join Now</a>
										</div>
									</div>
								</div>
							</div>
							<a href="{{url('candidates-grid')}}" class="btn btn-light w-100">View All<i
									class="ti ti-arrow-right ms-1"></i></a>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->


			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">

				<div class="col-xl-7 d-flex flex-column">

					<!-- start row -->
					<div class="row">

						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<p class="mb-1">Benefits Deductions</p>
											<h2 class="mb-2">$56K</h2>
											<p class="mb-0">Insurance + 401(k)</p>
										</div>
										<div>
											<div id="deduction-chart"></div>
										</div>
									</div>
								</div> <!-- end card body -->
							</div> <!-- end card -->
						</div> <!-- end col -->

						<div class="col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<p class="mb-1">Total Payroll</p>
											<h2 class="mb-2">$2.4M</h2>
											<p class="mb-0"><span class="text-success">
													<i class="ti ti-arrow-wave-right-up me-1"></i>+55%</span>
												Increased</p>
										</div>
										<div class="text-end">
											<div class="dropdown mb-3">
												<a href="javascript:void(0);"
													class="border btn btn-white btn-md d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-calendar-due me-1 fs-14"></i>Monthly
												</a>
												<ul class="dropdown-menu mt-2 p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1">
															Monthly
														</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1">
															Weekly
														</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1">
															Today
														</a>
													</li>
												</ul>
											</div>
											<div id="payroll-chart"></div>
										</div>
									</div>
								</div> <!-- end card body -->
							</div> <!-- end card -->
						</div> <!-- end col -->
					</div>
					<!-- end row -->

					<div class="card flex-fill">
						<div class="card-body">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Top Employees</h2>
								<div>
									<div class="border bg-light rounded p-1">
										<nav class="nav nav-pills flex-row gap-1 custom-tab" role="tablist">
											<a class="flex-fill text-center nav-link p-1 px-2 active">1D</a>
											<a class="flex-fill text-sm-center nav-link p-1 px-2">7D</a>
											<a class="flex-fill text-sm-center nav-link p-1 px-2">1M</a>
											<a class="flex-fill text-sm-center nav-link p-1 px-2">1Y</a>
										</nav>
									</div>
								</div>
							</div>

							<div class="tab-content">
								<div class="tab-pane fade show active" id="day" role="tabpanel"
									aria-labelledby="day-tab">
									<div class="chart-container">
										<canvas id="imageChart" height="110"></canvas>
									</div>
								</div>
								<div class="tab-pane fade" id="week" role="tabpanel" aria-labelledby="week-tab">
									<div class="chart-container">
										<canvas id="imageChartWeek" height="110"></canvas>
									</div>
								</div>
								<div class="tab-pane fade" id="month" role="tabpanel" aria-labelledby="month-tab">
									<div class="chart-container">
										<canvas id="imageChartMonth" height="110"></canvas>
									</div>
								</div>
								<div class="tab-pane fade" id="year" role="tabpanel" aria-labelledby="year-tab">
									<div class="chart-container">
										<canvas id="imageChartYear" height="110"></canvas>
									</div>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

				<div class="col-xl-5 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div
								class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
								<h2 class="card-title mb-0">Pending Approvals</h2>
								<a href="{{url('leaves-employee')}}" class="btn btn-md btn-light">View All</a>
							</div>
							<div
								class="p-2 rounded border d-flex align-items-sm-center justify-content-between gap-2 flex-column flex-sm-row mb-2">
								<div>
									<div class="d-flex align-items-center mb-1">
										<a href="#" class="avatar avatar-sm flex-shrink-0">
											<img src="{{URL::asset('build/img/users/user-29.jpg')}}" class="rounded-circle"
												alt="user">
										</a>
										<div class="ms-2">
											<p class="fs-14 fw-semibold text-truncate mb-0"><a href="#">Hendrita
													Merkel</a></p>
										</div>
									</div>
									<div class="d-inline-flex align-items-center gap-1">
										<p class="fs-13 d-inline-flex align-items-center mb-0"><i
												class="ti ti-calendar-up me-1 fs-14"></i>Jan 10 - Jan 16</p>
										<span><i class="ti ti-minus-vertical border-color fs-14"></i></span>
										<p class="fs-13 d-inline-flex align-items-center mb-0"><i
												class="ti ti-clock-hour-11 me-1 fs-14"></i>4 days</p>
									</div>
									<p class="fs-13 mb-0 mt-1">Reason: Family trip</p>
								</div>
								<div class="d-flex align-items-center gap-2">
									<a href="#" class="btn btn-sm btn-primary">Approve</a>
									<a href="#" class="btn btn-sm btn-outline-primary">Decline</a>
								</div>
							</div>
							<div
								class="p-2 rounded border d-flex align-items-sm-center justify-content-between gap-2 flex-column flex-sm-row  mb-2">
								<div>
									<div class="d-flex align-items-center mb-1">
										<a href="#" class="avatar avatar-sm flex-shrink-0">
											<img src="{{URL::asset('build/img/users/user-27.jpg')}}" class="rounded-circle"
												alt="user">
										</a>
										<div class="ms-2">
											<p class="fs-14 fw-semibold text-truncate mb-0"><a href="#">Michael
													Brown</a></p>
										</div>
									</div>
									<div class="d-inline-flex align-items-center gap-1">
										<p class="fs-13 d-inline-flex align-items-center mb-0"><i
												class="ti ti-calendar-up me-1"></i>Jan 3 - Jan 9</p>
										<span><i class="ti ti-minus-vertical border-color fs-14"></i></span>
										<p class="fs-13 d-inline-flex align-items-center mb-0"><i
												class="ti ti-clock-hour-11 me-1"></i>2 days</p>
									</div>
									<p class="fs-13 mb-0 mt-1">Reason: Medical appointment</p>
								</div>
								<div class="d-flex align-items-center gap-2">
									<a href="#" class="btn btn-sm btn-primary">Approve</a>
									<a href="#" class="btn btn-sm btn-outline-primary">Decline</a>
								</div>
							</div>
							<div
								class="p-2 rounded border d-flex align-items-sm-center justify-content-between gap-2 flex-column flex-sm-row  mb-0">
								<div>
									<div class="d-flex align-items-center mb-1">
										<a href="#" class="avatar avatar-sm flex-shrink-0">
											<img src="{{URL::asset('build/img/users/user-30.jpg')}}" class="rounded-circle"
												alt="user">
										</a>
										<div class="ms-2">
											<p class="fs-14 fw-semibold text-truncate mb-0"><a href="#">Daniel
													Martinez</a></p>
										</div>
									</div>
									<div class="d-inline-flex align-items-center gap-1">
										<p class="fs-13 d-inline-flex align-items-center mb-0"><i
												class="ti ti-calendar-up me-1"></i>Jan 17 - Jan 23</p>
										<span><i class="ti ti-minus-vertical border-color fs-14"></i></span>
										<p class="fs-13 d-inline-flex align-items-center mb-0"><i
												class="ti ti-clock-hour-11 me-1"></i>2 days</p>
									</div>
									<p class="fs-13 mb-0 mt-1">Reason: Personal Work</p>
								</div>
								<div class="d-flex align-items-center gap-2">
									<a href="#" class="btn btn-sm btn-primary">Approve</a>
									<a href="#" class="btn btn-sm btn-outline-primary">Decline</a>
								</div>
							</div>
						</div> <!-- end card body -->
					</div> <!-- end card -->
				</div> <!-- end col -->

			</div>
			<!-- end row -->

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection