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
					<h2 class="mb-1">AI Attendance Insights</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								AI Center
							</li>
							<li class="breadcrumb-item active" aria-current="page">AI Attendance Insights</li>
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
					<a href="#" class="btn btn-primary-gradient mb-2"><i class="ti ti-refresh me-2"></i>Run AI Scan</a>
					<div class="ms-2 mb-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- End Breadcrumb -->

			<div class="row">

				<div class="col-xxl-6 d-flex flex-column">
					<div class="row flex-fill">
						<div class="col-sm-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between border-bottom mb-3 pb-3">
										<div class="d-flex align-items-center">
											<span class="py-2 px-1 rounded-pill bg-primary me-2">
												<i class="ti ti-users-group fs-16"></i>
											</span>
											<p class="fs-12">Overall Rate</p>
										</div>
										<h3 class="card-title mb-0">91.4%</h3>
									</div>

									<div id="rate-chart" class="mb-3"></div>
									<div class="d-flex align-items-center gap-1 flex-wrap">
										<span class="badge badge-success-transparent">2.3%<i class="ti ti-arrow-up-right ms-1"></i></span>
										<p> vs last month</p>
									</div>
								</div>
							</div>
						</div> <!-- end col -->

						<div class="col-sm-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between border-bottom mb-3 pb-3">
										<div class="d-flex align-items-center">
											<span class="py-2 px-1 rounded-pill bg-secondary me-2">
												<i class="ti ti-user-check fs-16"></i>
											</span>
											<p class="fs-12">Present Today</p>
										</div>
										<h3 class="card-title mb-0">774</h3>
									</div>
									<div id="day-chart" class="mb-3"></div>
									<div class="d-flex align-items-center gap-1 flex-wrap">
										<span class="badge badge-success-transparent">4.2%<i class="ti ti-arrow-up-right ms-1"></i></span>
										<p>of workforce</p>
									</div>
								</div>
							</div>
						</div> <!-- end col -->

						<div class="col-sm-6 d-flex">
							<div class="card bg-dark border-0 z-1 flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between mb-4">
										<div>
											<p class="fw-semibold text-white mb-1">On Leave</p>
											<h3 class="main-title text-white">42</h3>
										</div>
										<span class="avatar rounded-circle bg-primary mb-2">
											<i class="ti ti-user-cancel fs-20"></i>
										</span>
									</div>
									<div class="d-flex align-items-center gap-2 flex-wrap">
										<span class="badge badge-danger-transparent">10.1%<i class="ti ti-arrow-down-right ms-1"></i></span>
										<p class="text-white">less vs last month</p>
									</div>
									<img src="{{URL::asset('build/img/bg/card-bg-06.png')}}" alt="bg" class="img-fluid position-absolute top-0 end-0 z-n1 w-autp h-auto">
								</div>
							</div>
						</div>

						<div class="col-sm-6 d-flex">
							<div class="card bg-secondary border-0 z-1 flex-fill">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between mb-4">
										<div>
											<p class="fw-semibold text-white mb-1">Late Arrivals</p>
											<h3 class="main-title text-white">14</h3>
										</div>
										<span class="avatar rounded-circle bg-white mb-2">
											<i class="ti ti-clock-hour-4 text-dark fs-20"></i>
										</span>
									</div>
									<div class="d-flex align-items-center gap-2 flex-wrap">
										<span class="badge badge-danger-transparent">21.1%<i class="ti ti-arrow-down-right ms-1"></i></span>
										<p class="text-white">less vs last month</p>
									</div>
									<img src="{{URL::asset('build/img/bg/card-bg-06.png')}}" alt="bg" class="img-fluid position-absolute top-0 end-0 z-n1 w-autp h-full">
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Widget Info -->

				<div class="col-xxl-6 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h3 class="sub-title mb-0">Weekly Attendance Pattern</h3>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium" data-bs-toggle="dropdown"> <i class="ti ti-calendar fs-14"></i> Weekly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="d-flex align-items-center gap-2 flex-wrap">
								<p class="text-dark mb-0"><i class="ti ti-circle-filled fs-12 text-secondary me-1"></i>Present</p>
								<p class="text-dark mb-0"><i class="ti ti-circle-filled fs-12 text-secondary-500 me-1"></i>Absent</p>
								<p class="text-dark mb-0"><i class="ti ti-circle-filled fs-12 text-primary me-1"></i>Late</p>
							</div>
							<div id="weekly-attendance"></div>
						</div>
					</div>
				</div> <!-- end col -->

			</div>

			<div class="row">

				<div class="col-xxl-8 col-xl-7 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h3 class="sub-title mb-0">Attendance Statistics</h3>
								<div class="d-flex align-items-center gap-2">
									<span class="badge border fs-14 fw-normal text-body"><i class="ti ti-square-filled text-primary fs-12 me-1"></i>Present</span>
									<span class="badge border fs-14 fw-normal text-body"><i class="ti ti-square-filled text-secondary fs-12 me-1"></i>Absent</span>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);" class="border btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										<i class="ti ti-calendar me-1 fs-14"></i>2026
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2025</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2024</a>
										</li>
									</ul>
								</div>
							</div>
							<div id="attendance-statistics"></div>
						</div>
					</div>
				</div> <!-- end col -->

				<div class="col-xxl-4 col-xl-5 d-flex">
					<div class="card flex-fill">
						<div class="card-body">

							<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
								<h3>Detected Irregularities</h3>
								<div class="dropdown">
									<a href="javascript:void(0);" class="border btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										<i class="ti ti-calendar me-1 fs-14"></i>Today
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Weekly</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="vstack gap-3">
								<div class="d-flex align-items-sm-center gap-3 ps-2 flex-column flex-sm-row border-start border-3 border-warning">
									<div class="flex-shrink-0">
										<p class="fw-medium text-dark mb-0">09:47 AM</p>
									</div>
									<div>
										<p class="fw-semibold text-dark mb-1">Repeated late pattern — R. Mehta</p>
										<p class="fs-13 mb-0">4th consecutive late arrival this week (>30 min)</p>
									</div>
								</div>
								<div class="d-flex align-items-sm-center gap-3 ps-2 flex-column flex-sm-row border-start border-3 border-danger">
									<div class="flex-shrink-0">
										<p class="fw-medium text-dark mb-0">09:47 AM</p>
									</div>
									<div>
										<p class="fw-semibold text-dark mb-1">Early departure cluster — Ops team</p>
										<p class="fs-13 mb-0">3 members clocked out 2h early yesterday</p>
									</div>
								</div>
								<div class="d-flex align-items-sm-center gap-3 ps-2 flex-column flex-sm-row border-start border-3 border-info">
									<div class="flex-shrink-0">
										<p class="fw-medium text-dark mb-0">09:47 AM</p>
									</div>
									<div>
										<p class="fw-semibold text-dark mb-1">Overtime spike — Engineering</p>
										<p class="fs-13 mb-0">Average 11.2 hrs/day — release week pattern</p>
									</div>
								</div>
								<div class="d-flex align-items-sm-center gap-3 ps-2 flex-column flex-sm-row border-start border-3 border-purple">
									<div class="flex-shrink-0">
										<p class="fw-medium text-dark mb-0">09:47 AM</p>
									</div>
									<div>
										<p class="fw-semibold text-dark mb-1">Untracked check-out — 3 employees</p>
										<p class="fs-13 mb-0">No exit scan recorded on 13 May</p>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

			</div>

			<div class="card bg-linear-gradient-100 overflow-hidden border-0 z-1">
				<div class="card-body p-4 p-md-5 d-flex align-items-center justify-content-md-between justify-content-center text-center text-md-start gap-3 flex-column flex-md-row">
					<div>
						<h3 class="text-white mb-1">Real-Time Attendance Dashboard</h3>
						<p class="text-white mb-3">Monitor live check-ins, track patterns, and receive instant alerts</p>
						<a href="#" class="border btn btn-white btn-md d-inline-flex align-items-center">View Realtime Dashboard<i class="ti ti-arrow-right ms-2"></i></a>
					</div>
					<div class="d-flex align-items-center gap-3">
						<div class="p-3 rounded text-center bg-white-100">
							<h3 class="text-white mb-1">242</h3>
							<p class="text-white">Checked In</p>
						</div>
						<div class="p-3 rounded text-center bg-white-100">
							<h3 class="text-white mb-1">8</h3>
							<p class="text-white">Pending</p>
						</div>
					</div>
					<img src="{{URL::asset('build/img/bg/card-bg-07.png')}}" alt="bg" class="img-fluid position-absolute top-0 start-50 translate-middle-x z-n1 w-auto h-auto">
				</div>
			</div>

			<div class="row">

				<div class="col-xxl-6 col-xl-7 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
								<h3 class="sub-title mb-0">Absenteeism Analysis</h3>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium" data-bs-toggle="dropdown"> <i class="ti ti-calendar fs-14"></i> Weekly
									</a>
									<ul class="dropdown-menu mt-2 p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Today
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Weekly
											</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">
												Monthly
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="row align-items-center">
								<div class="col-sm-6">
									<div class="vstack gap-3 me-sm-4">
										<div class="bg-primary-linear-gradient d-flex align-items-center justify-content-between p-2 rounded">
											<p class="text-dark d-inline-flex align-items-center mb-0"><i class="ti ti-square-filled text-primary fs-16 me-1"></i>Emergency</p>
											<span class="badge bg-white  text-dark">42 cases</span>
										</div>
										<div class="bg-warning-linear-gradient d-flex align-items-center justify-content-between p-2 rounded">
											<p class="text-dark d-inline-flex align-items-center mb-0"><i class="ti ti-square-filled text-warning fs-16 me-1"></i>Vacation</p>
											<span class="badge bg-white  text-dark">28 cases</span>
										</div>
										<div class="bg-success-linear-gradient d-flex align-items-center justify-content-between p-2 rounded">
											<p class="text-dark d-inline-flex align-items-center mb-0"><i class="ti ti-square-filled text-success fs-16 me-1"></i>Personal</p>
											<span class="badge bg-white  text-dark">42 cases</span>
										</div>
										<div class="bg-info-linear-gradient d-flex align-items-center justify-content-between p-2 rounded">
											<p class="text-dark d-inline-flex align-items-center mb-0"><i class="ti ti-square-filled text-info fs-16 me-1"></i>Sick Leave</p>
											<span class="badge bg-white  text-dark">42 cases</span>
										</div>

									</div>
								</div>
								<div class="col-sm-6">
									<div id="analytics-chart"></div>
								</div>
							</div>
							<div class="p-3 rounded border d-flex align-items-center gap-2 mt-3">
								<div class="avatar bg-light border avatar-rounded flex-shrink-0">
									<i class="ti ti-thumb-up-filled fs-24 text-primary"></i>
								</div>
								<div>
									<p class="mb-1">Absence Reasons Breakdown</p>
									<p class="text-dark">Sick leave accounts for 38% of absences. Consider wellness programs.</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xxl-6 col-xl-5 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
								<h3 class="sub-title mb-0">On-Time & Late Department</h3>
								<div class="dropdown">
									<a href="javascript:void(0);" class="border btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										<i class="ti ti-calendar me-1 fs-14"></i>Today
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Weekly</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly</a>
										</li>
									</ul>
								</div>
							</div>
							<div id="department-chart"></div>
							<div class="bg-info-transparent p-2 rounded border border-info">
								<p class="text-info fw-medium">Support team leads with 97%  arrival rate.</p>
							</div>
						</div>
					</div>
				</div> <!-- end col -->

			</div>

			<div class="card">
				<div class="card-body">

					<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
						<h3 class="sub-title mb-0">AI Predictions & Alerts</h3>
						<div class="dropdown">
							<a href="javascript:void(0);" class="border btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
								<i class="ti ti-calendar me-1 fs-14"></i>Today
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Today</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Weekly</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly</a>
								</li>
							</ul>
						</div>
					</div>
							<div class="row g-4 justify-content-center">

								<div class="col-xl-4 col-md-6 d-flex">
									<div class="card border-bottom border-bottom-5 border-bottom-secondary mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex align-items-center justify-content-between mb-3">
												<span class="avatar avatar-xl rounded-circle bg-primary border border-8 border-primary-transparent">
													<i class="ti ti-refresh fs-20"></i>
												</span>
												<div class="dropdown">
													<a href="#" class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center p-0 rounded-pill" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
													<ul class="dropdown-menu  dropdown-menu-end p-3">
														<li>
															<a href="#" class="dropdown-item rounded-1">View</a>
														</li>
														<li>
															<a href="#" class="dropdown-item rounded-1">Delete</a>
														</li>
													</ul>
												</div>
											</div>
											<h5 class="mb-1">Predicted Absence Spike</h5>
											<p class="mb-3">15% increase in absences expected next Friday (May 21). Likely due to holiday weekend.</p>
											<div class="d-flex align-items-center flex-wrap gap-2">
												<span class="badge badge-xs badge-secondary-transparent">85% Confidence</span>
												<span class="badge badge-xs badge-secondary-transparent">7 days ahead</span>
											</div>
										</div>
									</div>
								</div> <!-- enc col -->

								<div class="col-xl-4 col-md-6 d-flex">
									<div class="card border-bottom border-bottom-5 border-bottom-purple mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex align-items-center justify-content-between mb-3">
												<span class="avatar avatar-xl rounded-circle bg-secondary border border-8 border-secondary-transparent">
													<i class="ti ti-server-bolt fs-20"></i>
												</span>
												<div class="dropdown">
													<a href="#" class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center p-0 rounded-pill" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
													<ul class="dropdown-menu  dropdown-menu-end p-3">
														<li>
															<a href="#" class="dropdown-item rounded-1">View</a>
														</li>
														<li>
															<a href="#" class="dropdown-item rounded-1">Delete</a>
														</li>
													</ul>
												</div>
											</div>
											<h5 class="mb-1">Optimal Attendance Period</h5>
											<p class="mb-3">Tuesday-Thursday shows consistently high attendance. Schedule critical meetings window.</p>
											<div class="d-flex align-items-center flex-wrap gap-2">
												<span class="badge badge-xs badge-purple-transparent">High Priority</span>
												<span class="badge badge-xs badge-purple-transparent">Actionable</span>
											</div>
										</div>
									</div>
								</div> <!-- enc col -->

								<div class="col-xl-4 col-md-6 d-flex">
									<div class="card border-bottom border-bottom-5 border-bottom-danger mb-0 flex-fill">
										<div class="card-body">
											<div class="d-flex align-items-center justify-content-between mb-3">
												<span class="avatar avatar-xl rounded-circle bg-dark border border-8 border-dark-transparent">
													<i class="ti ti-user-pause fs-20"></i>
												</span>
												<div class="dropdown">
													<a href="#" class="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center p-0 rounded-pill" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
													<ul class="dropdown-menu  dropdown-menu-end p-3">
														<li>
															<a href="#" class="dropdown-item rounded-1">View</a>
														</li>
														<li>
															<a href="#" class="dropdown-item rounded-1">Delete</a>
														</li>
													</ul>
												</div>
											</div>
											<h5 class="mb-1">Late Arrival Pattern Alert</h5>
											<p class="mb-3">Development team shows 12% late arrival rate, up from 8% last month. Traffic or scheduling issue?</p>
											<div class="d-flex align-items-center flex-wrap gap-2">
												<span class="badge badge-xs badge-danger-transparent">Manager Action Needed</span>
												<span class="badge badge-xs badge-danger-transparent">30-day trend</span>
											</div>
										</div>
									</div>
								</div> <!-- enc col -->

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