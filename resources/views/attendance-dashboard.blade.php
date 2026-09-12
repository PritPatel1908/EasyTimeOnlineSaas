@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

	<div class="page-wrapper">

		<!-- Start Content -->
		<div class="content">

			<!-- Breadcrumb -->
			<div class="d-xl-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
				<div class="my-auto mb-2">
					<h2 class="mb-1">Attendance Dashboard</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">Attendance Dashboard</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
					<div class="d-flex align-items-center gap-3 p-2 border avatar-groups  rounded">
						<h5 class="d-flex align-items-center gap-1"><i
								class="ti ti-circle-filled text-primary fs-8"></i> 06 <span
								class="fs-13 fw-normal">Total Leaves</span> </h5>
						<div class="avatar-list-stacked avatar-group-sm ms-4">
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}" alt="img">
							</span>
							<span class="avatar avatar-rounded">
								<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}" alt="img">
							</span>
						</div>
					</div>
					<div class="mb-0">
						<div class="input-icon w-100 position-relative">
							<span class="input-icon-addon">
								<i class="ti ti-calendar text-gray-9"></i>
							</span>
							<input type="text" class="form-control yearpicker" value="December 2025">
						</div>
					</div>
					<a href="#" class="btn btn-primary-gradient d-flex align-items-center gap-1"> <i
							class="ti ti-plus"></i> Apply Leave </a>
					<div class="ms-2 mt-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- Overview Statistics -->
			<div class="card">
				<div class="card-body">
					<div class="mb-1 d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
						<h2 class="card-title mb-0">Overview Statistics</h2>
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded-pill gap-1 fw-medium"
								data-bs-toggle="dropdown">
								<i class="ti ti-calendar fs-14"></i> Today
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
					<!-- start row -->
					<div class="row row-gap-4">

						<!-- Item 1 -->
						<div class="col-xxl-4 col-xl-6 col-lg-6 col-sm-6">
							<div class="card bg-light stat-card border shadow-none">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
										<div>
											<p class="fs-14 fw-medium mb-2">Total Employees</p>
											<div class="d-flex align-items-center gap-2">
												<h3 class="fs-20 mb-0">2,847</h3>
												<div class="d-flex align-items-center gap-1"> <span
														class="badge bg-success d-inline-flex align-items-center gap-1 rounded fs-12">
														<i class="ti ti-arrow-up-right"></i> 12%</span> vs yesterday
												</div>
											</div>
										</div>
										<div class="avatar avatar-lg bg-primary">
											<img src="{{URL::asset('build/img/icons/attendance-icon-1.svg')}}" alt="attendance"
												class="img-fluid img-1">
											<div class="avatar-1"></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Item 2 -->
						<div class="col-xxl-4 col-xl-6 col-lg-6 col-sm-6">
							<div class="card bg-light stat-card border shadow-none">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
										<div>
											<p class="fs-14 fw-medium mb-2">Present Today</p>
											<div class="d-flex align-items-center gap-2">
												<h3 class="fs-20 mb-0">2,458</h3>
												<div class="d-flex align-items-center gap-1"> <span
														class="badge bg-danger d-inline-flex align-items-center gap-1 rounded fs-12">
														<i class="ti ti-arrow-down-right"></i> 3.2%</span> vs
													yesterday </div>
											</div>
										</div>
										<div class="avatar avatar-lg bg-secondary">
											<img src="{{URL::asset('build/img/icons/attendance-icon-2.svg')}}" alt="attendance"
												class="img-fluid img-1">
											<div class="avatar-1 two"></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Item 3 -->
						<div class="col-xxl-4 col-xl-6 col-lg-6 col-sm-6">
							<div class="card bg-light stat-card border shadow-none">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
										<div>
											<p class="fs-14 fw-medium mb-2">Absent Today</p>
											<div class="d-flex align-items-center gap-2">
												<h3 class="fs-20 mb-0">124</h3>
												<div class="d-flex align-items-center gap-1"> <span
														class="badge bg-warning d-inline-flex align-items-center gap-1 rounded fs-12">
														<i class="ti ti-arrow-up-right"></i> 1.4%</span> vs
													yesterday </div>
											</div>
										</div>
										<div class="avatar avatar-lg bg-warning">
											<img src="{{URL::asset('build/img/icons/attendance-icon-2.svg')}}" alt="attendance"
												class="img-fluid img-1">
											<div class="avatar-1 three"></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Item 4 -->
						<div class="col-xxl-4 col-xl-6 col-lg-6 col-sm-6">
							<div class="card bg-light stat-card border shadow-none">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
										<div>
											<p class="fs-14 fw-medium mb-2">Late Arrivals</p>
											<div class="d-flex align-items-center gap-2">
												<h3 class="fs-20 mb-0">89</h3>
												<div class="d-flex align-items-center gap-1"> <span
														class="badge bg-info d-inline-flex align-items-center gap-1 rounded fs-12">
														<i class="ti ti-arrow-up-right"></i> 12%</span> vs yesterday
												</div>
											</div>
										</div>
										<div class="avatar avatar-lg bg-info">
											<img src="{{URL::asset('build/img/icons/attendance-icon-3.svg')}}" alt="attendance"
												class="img-fluid img-1">
											<div class="avatar-1 four"></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Item 5 -->
						<div class="col-xxl-4 col-xl-6 col-lg-6 col-sm-6">
							<div class="card bg-light stat-card border shadow-none">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
										<div>
											<p class="fs-14 fw-medium mb-2">Attendance Rate</p>
											<div class="d-flex align-items-center gap-2">
												<h3 class="fs-20 mb-0">86.3%</h3>
												<div class="d-flex align-items-center gap-1"> <span
														class="badge bg-success d-inline-flex align-items-center gap-1 rounded fs-12">
														<i class="ti ti-arrow-up-right"></i> 2.5%</span> vs
													yesterday </div>
											</div>
										</div>
										<div class="avatar avatar-lg bg-purple">
											<img src="{{URL::asset('build/img/icons/attendance-icon-4.svg')}}" alt="attendance"
												class="img-fluid img-1">
											<div class="avatar-1 five"></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Item 6 -->
						<div class="col-xxl-4 col-xl-6 col-lg-6 col-sm-6">
							<div class="card bg-light stat-card border shadow-none">
								<div class="card-body">
									<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
										<div>
											<p class="fs-14 fw-medium mb-2">Pending Regularizations</p>
											<div class="d-flex align-items-center gap-2">
												<h3 class="fs-20 mb-0">42</h3>
												<div class="d-flex align-items-center gap-1"> <span
														class="badge bg-danger d-inline-flex align-items-center gap-1 rounded fs-12">
														<i class="ti ti-arrow-down-right"></i> 7%</span> vs
													yesterday </div>
											</div>
										</div>
										<div class="avatar avatar-lg bg-danger">
											<img src="{{URL::asset('build/img/icons/attendance-icon-5.svg')}}" alt="attendance"
												class="img-fluid img-1">
											<div class="avatar-1 six"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Attendance Trends -->
			<div class="row row-gap-4 mb-4">
				<div class="col-xxl-8">
					<div class="card mb-0">
						<div class="card-body pb-2">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
								<h2 class="card-title mb-0">Attendance Trends</h2>
								<div class="day-active-item d-inline-flex align-items-center gap-2">
									<button class="day">1D</button>
									<button class="day">7D</button>
									<button class="day">1M</button>
									<button class="day active">1Y</button>
								</div>
							</div>
							<div id="attendancetrend-chart"></div>
						</div>
					</div>
				</div>
				<div class="col-xxl-4">
					<div class="card bg-dark position-relative mb-0">
						<div class="card-body position-relative z-1">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<h2 class="card-title mb-0 text-white">Attendance Status</h2>
								<div class="input-icon rounded-circle position-relative mb-2 custom-datepicker">
									<span class="input-icon-addon">
										<i class="ti ti-calendar text-gray-9"></i>
									</span>
									<input type="text" class="form-control datetimepicker" value="15-04-2025">
								</div>
							</div>
							<div class="mb-3">
								<p class="mb-1 text-white">Total Working Days</p>
								<h3 class="main-title mb-0 text-white">300</h3>
							</div>

							<div class="progress working-progress bg-soft-light mb-1 py-1 px-1 px-1 mb-3">
								<div class="progress-bar bg-primary  me-1" role="progressbar" style="width: 40%;"
									data-bs-toggle="tooltip" data-bs-placement="Top"></div>
								<div class="progress-bar bg-primary-8  me-1" role="progressbar" style="width: 20%;"
									data-bs-toggle="tooltip" data-bs-placement="Top"></div>
								<div class="progress-bar bg-primary-7  me-1" role="progressbar" style="width: 10%;"
									data-bs-toggle="tooltip" data-bs-placement="Top"></div>
								<div class="progress-bar bg-primary-6  me-1" role="progressbar" style="width: 10%;"
									data-bs-toggle="tooltip" data-bs-placement="Top"></div>
								<div class="progress-bar bg-primary-5" role="progressbar" style="width: 20%;"
									data-bs-toggle="tooltip" data-bs-placement="Top"></div>
							</div>


							<div
								class="attendance-percent d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div class="percent"> <i class="ti ti-circle-filled fs-10 text-primary"></i> Present
								</div>
								<p class="mb-0">2458</p>
							</div>
							<div
								class="attendance-percent d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div class="percent"> <i class="ti ti-circle-filled fs-10 text-primary-8"></i> WFH
								</div>
								<p class="mb-0">187</p>
							</div>
							<div
								class="attendance-percent d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div class="percent"> <i class="ti ti-circle-filled fs-10 text-primary-7"></i> Late
								</div>
								<p class="mb-0">89</p>
							</div>
							<div
								class="attendance-percent d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div class="percent"> <i class="ti ti-circle-filled fs-10 text-primary-6"></i> On
									Leave </div>
								<p class="mb-0">78</p>
							</div>
							<div
								class="attendance-percent d-flex align-items-center justify-content-between gap-2 flex-wrap mb-0">
								<div class="percent"> <i class="ti ti-circle-filled fs-10 text-primary-5"></i>
									Absent </div>
								<p class="mb-0">124</p>
							</div>
						</div>
						<img src="{{URL::asset('build/img/bg/attendance-bg-2.png')}}" alt="attendance"
							class="img-fluid img-1 position-absolute top-0 end-0">
					</div>
				</div>
			</div>

			<!-- start row -->
			<div class="row row-gap-4 mb-4">
				<div class="col-xxl-6 col-xl-12 col-lg-12 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body pb-2">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
								<h2 class="card-title mb-0">Office vs Remote</h2>
								<div class="d-flex align-items-center gap-2">
									<p class="d-inline-flex align-items-center gap-1 mb-0"> <i
											class="ti ti-circle-filled fs-8 text-primary"></i> Office </p>
									<p class="d-inline-flex align-items-center gap-1 mb-0"> <i
											class="ti ti-circle-filled fs-8 text-secondary"></i> Remote </p>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Weekly
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
							<div id="office-chart"></div>
						</div>
					</div>
				</div>
				<div class="col-xxl-6 col-xl-12 col-lg-12 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<h2 class="card-title mb-0">Attendance Summary</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Today
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

							<div class="avghours-container mb-4">
								<div class="fs-14 fw-medium text-gray-6">Avg Working Hours</div>
								<div class="progress-area">
									<div class="tooltip-bubble" style="left: 75%;">
										9.5 hrs
										<span class="tooltip-arrow"></span>
									</div>

									<div class="progress-bar-track">
										<div class="progress-bar-fill" style="width: 75%;"></div>
										<div class="track-dots"></div>
									</div>
								</div>
								<p class="d-inline-flex align-items-center gap-2"><span
										class="badge bg-danger d-inline-flex align-items-center gap-1 fs-12"><i
											class="ti ti-arrow-down-right"></i>3.2%</span>Compared to yesterday</p>
							</div>

							<div class="row row-gap-3">
								<div class="col-lg-4">
									<div class="card text-center mb-0 shadow-none">
										<div class="card-body">
											<h3 class="main-title mb-1">2,458</h3>
											<p>Check-in Count</p>
										</div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="card text-center mb-0 shadow-none">
										<div class="card-body">
											<h3 class="main-title mb-1">2,201</h3>
											<p>Check-out Count</p>
										</div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="card text-center mb-0 shadow-none">
										<div class="card-body">
											<h3 class="main-title mb-1">9:12 AM</h3>
											<p>Check-in Time</p>
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
			<!-- end row -->

			<!-- Frequent Late Arrivals -->
			<div class="row row-gap-4 mb-4 justify-content-center">

				<!-- Item 1 -->
				<div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 d-flex">
					<div class="card arrival-card flex-fill mb-0">
						<div class="card-body position-relative z-1">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<h2 class="card-title mb-0">Frequent Late Arrivals</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Today
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
							<p class="mb-1">No of Employees</p>
							<h3 class="main-title mb-0 d-flex align-items-center gap-2">22 <span
									class="badge badge-soft-purple fs-11">Warnings</span> </h3>
							<div class="mt-4 d-flex align-items-center justify-content-between gap-2">
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-white avatar-rounded text-fixed-white fs-12 border text-dark"
										href="javascript:void(0);">
										<span class="text-dark">+9</span>
									</a>
								</div>
								<div
									class="rounded-pill bg-white border fs-13 text-gray-9 d-inline-flex align-items-center gap-2 p-1 ps-2">
									+15.5%
									<p
										class="btn pe-none btn-sm btn-icon bg-danger rounded-circle d-flex align-items-center justify-content-center text-white">
										<i class="ti ti-arrow-down-right fs-14 "></i>
									</p>
								</div>
							</div>
						</div>
						<img src="{{URL::asset('build/img/bg/attendance-bg.png')}}" alt="attendance" class="img-fluid img-1">
					</div>
				</div>

				<!-- Item 2 -->
				<div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 d-flex">
					<div class="card arrival-card arrival-card-1 flex-fill mb-0">
						<div class="card-body position-relative">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<h2 class="card-title mb-0">Missing Punches</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Today
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
							<p class="mb-1">Total Number of Missing Punches</p>
							<h3 class="main-title mb-0 d-flex align-items-center gap-2">09 <span
									class="badge badge-soft-danger fs-11">Critical</span> </h3>
							<div class="mt-4 d-flex align-items-center justify-content-between gap-2">
								<div class="avatar-list-stacked avatar-group-sm">
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}"
											alt="img">
									</span>
									<span class="avatar avatar-rounded">
										<img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}"
											alt="img">
									</span>
									<a class="avatar bg-white avatar-rounded text-fixed-white fs-12 border text-dark"
										href="javascript:void(0);">
										<span class="text-dark">+6</span>
									</a>
								</div>
								<div
									class="rounded-pill value bg-white border fs-13 text-gray-9 d-inline-flex align-items-center gap-2 p-1 ps-2">
									+18.5%
									<p
										class="btn pe-none btn-sm btn-icon bg-success rounded-circle d-flex align-items-center justify-content-center text-white">
										<i class="ti ti-arrow-up-right fs-14 "></i>
									</p>
								</div>
							</div>
						</div>
						<img src="{{URL::asset('build/img/bg/attendance-bg-1.png')}}" alt="attendance" class="img-fluid img-1">
					</div>
				</div>

				<!-- Item 3 -->
				<div class="col-xxl-4 col-xl-6 d-flex">
					<div class="card arrival-card flex-fill mb-0">
						<div class="card-body position-relative">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<h2 class="card-title mb-0">Violations Statistics</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Today
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
							<!-- start row -->
							<div class="row row-gap-3">
								<div class="col-sm-7">
									<p class="mb-3 d-flex align-items-center gap-2 text-dark"> <span
											class="stat-pill bg-primary-gradient"></span> Late Arrivals </p>
									<p class="mb-3 d-flex align-items-center gap-2 text-dark"> <span
											class="stat-pill bg-secondary-gradient"></span> Missing Punches </p>
									<p class="mb-0 d-flex align-items-center gap-2 text-dark"> <span
											class="stat-pill bg-success-gradient"></span> Attendance Below 75% </p>
								</div>
								<div class="col-sm-5">
									<div id="violation-chart"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Late Arrivals & Alerts  -->
			<div class="row row-gap-4 mb-4">
				<div class="col-xxl-7">
					<div class="card mb-0">
						<div class="card-body">
							<div
								class="mb-1 d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<h2 class="card-title mb-0">Late Arrivals & Alerts</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md fw-normal d-inline-flex align-items-center justify-content-center rounded gap-1 fw-medium"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14"></i> Today
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

							<div class="alerts-list">
								<!-- Item 1 -->
								<div class="alerts-item mb-3">
									<div>
										<div class="d-flex align-items-center gap-2">
											<div class="avatar rounded-circle">
												<img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="user"
													class="img-fluid img-1 rounded-circle">
											</div>
											<div>
												<h3 class="fs-14 fw-medium mb-1"><a href="#">Michael Johnson</a>
												</h3>
												<p class="mb-0  fs-13">Administration Head</p>
											</div>
										</div>
									</div>
									<div>
										<h4 class="d-flex align-items-center gap-1 fs-14 mb-0"><span
												class="fw-normal text-gray-6 ">Check-in: </span> 09:45 AM</h4>
									</div>
									<div class="d-flex justify-content-center">
										<div id="arrival-chart-1"></div>
									</div>
								</div>

								<!-- Item 2 -->
								<div class="alerts-item mb-3">
									<div>
										<div class="d-flex align-items-center gap-2">
											<div class="avatar rounded-circle">
												<img src="{{URL::asset('build/img/users/user-07.jpg')}}" alt="user"
													class="img-fluid img-1 rounded-circle">
											</div>
											<div>
												<h3 class="fs-14 fw-medium mb-1"><a href="#">Emily Davis</a></h3>
												<p class="mb-0  fs-13">Frontend Developer</p>
											</div>
										</div>
									</div>
									<div>
										<h4 class="d-flex align-items-center gap-1 fs-14 mb-0"><span
												class="fw-normal text-gray-6 ">Check-in: </span> 09:30 AM</h4>
									</div>
									<div class="d-flex justify-content-center">
										<div id="arrival-chart-2"></div>
									</div>
								</div>

								<!-- Item 3 -->
								<div class="alerts-item mb-3">
									<div>
										<div class="d-flex align-items-center gap-2">
											<div class="avatar rounded-circle">
												<img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="user"
													class="img-fluid img-1 rounded-circle">
											</div>
											<div>
												<h3 class="fs-14 fw-medium mb-1"><a href="#">Robert Martinez</a>
												</h3>
												<p class="mb-0  fs-13">Finance Manager</p>
											</div>
										</div>
									</div>
									<div>
										<h4 class="d-flex align-items-center gap-1 fs-14 mb-0"><span
												class="fw-normal text-gray-6 ">Check-in: </span> 09:25 AM</h4>
									</div>
									<div class="d-flex justify-content-center">
										<div id="arrival-chart-3"></div>
									</div>
								</div>

								<!-- Item 4 -->
								<div class="alerts-item">
									<div>
										<div class="d-flex align-items-center gap-2">
											<div class="avatar rounded-circle">
												<img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="user"
													class="img-fluid img-1 rounded-circle">
											</div>
											<div>
												<h3 class="fs-14 fw-medium mb-1"><a href="#">Megan Walker</a></h3>
												<p class="mb-0  fs-13">SEO Analyst</p>
											</div>
										</div>
									</div>
									<div>
										<h4 class="d-flex align-items-center gap-1 fs-14 mb-0"><span
												class="fw-normal text-gray-6 ">Check-in: </span> 09:10 AM</h4>
									</div>
									<div class="d-flex justify-content-center">
										<div id="arrival-chart-4"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- AI Assistant -->
				<div class="col-xxl-5 d-flex">
					<div class="card flex-fill bg-linear-gradient mb-0">
						<div class="card-header bg-dark">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
								<div class="d-flex align-items-center gap-2">
									<div class="avatar avatar-xl bg-white rounded-circle">
										<i class="ti ti-sparkles fs-24 text-dark"></i>
									</div>
									<div>
										<h4 class="mb-1 text-white">AI Assistant</h4>
										<p class="fs-13 mb-0 text-white">Always here to help</p>
									</div>
								</div>
								<span
									class="badge bg-success d-inline-flex align-items-center gap-1 rounded-pill"><i
										class="ti ti-point-filled text-white fs-5 lh-0"></i>Online</span>
							</div>
						</div>
						<div class="card-body bg-light">
							<span class="d-flex align-items-center gap-2 mb-2 text-secondary"> <i
									class="ti ti-sparkles text-secondary"></i> AI Assistant</span>
							<p class="mb-0 text-gray-5">Hello! I'm your AI Attendance Assistant. I can help you
								analyze attendance patterns, generate reports, and provide insights. How can I
								assist you today?</p>
						</div>

						<div class="card-body chat-inner-item">
							<p class="mb-3 fs-14 text-dark">Suggested Questions</p>
							<div class="d-inline-flex flex-column gap-2">
								<p class="mb-2 prompt">Show me today's attendance summary</p>
								<p class="mb-0 prompt active">Why is attendance low on Fridays?</p>
							</div>
						</div>
						<div class="card-body border-top">
							<div class="chat-input-item item-new">
								<input type="text" class="form-control" placeholder="Ask me anything about payroll">
								<a href="#" class="btn btn-icon"><i class="ti ti-send"></i></a>
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