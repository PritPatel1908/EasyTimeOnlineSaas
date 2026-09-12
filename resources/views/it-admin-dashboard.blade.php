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
					<h2 class="mb-1">IT Admin Dashbaord</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">IT Admin Dashbaord</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
					<div class="active-user-item">
						<div class="avatar avatar-md bg-success rounded"> <i class="ti ti-server fs-16"></i> </div>
						<p class="fs-12 mb-0">Active Users <span class="fs-14 fw-semibold text-dark ms-1">248</span>
						</p>
					</div>
					<div class="active-user-item">
						<div class="avatar avatar-md bg-danger rounded"> <i class="ti ti-info-octagon fs-16"></i>
						</div>
						<p class="fs-12 mb-0">Security Alerts <span
								class="fs-14 fw-semibold text-dark ms-1">3</span> </p>
					</div>
					<ul class="nav nav-tabs nav-tabs-solid custom-solid-tab">
						<li class="nav-item"><a class="nav-link active" href="#solid-tab1"
								data-bs-toggle="tab">Production</a></li>
						<li class="nav-item"><a class="nav-link" href="#solid-tab2" data-bs-toggle="tab">Staging</a>
						</li>
						<li class="nav-item"><a class="nav-link" href="#solid-tab3"
								data-bs-toggle="tab">Development</a></li>
					</ul>
					<div class=" mt-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->


			<!-- HRMS System Uptime -->
			<div class="row row-gap-4 mb-4">

				<!-- Item 1 -->
				<div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
								<p class="mb-0">HRMS System Uptime</p>
								<div class="avatar avatar bg-light rounded-circle border">
									<i class="ti ti-clock-share text-gray-6 fs-20"></i>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
								<div>
									<h2 class="main-title mb-1">99.9%</h2>
									<p class="mb-0 fs-13">Last 30 days</p>
								</div>
								<div>
									<div id="uptime-chart"></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Item 2 -->
				<div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
								<p class="mb-0">API Status</p>
								<div class="avatar avatar bg-light rounded-circle border">
									<i class="ti ti-api-app text-gray-6 fs-20"></i>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
								<div>
									<h2 class="main-title mb-1">Healthy</h2>
									<p class="mb-0 fs-13">All operational</p>
								</div>
								<div>
									<div id="api-chart"></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Item 3 -->
				<div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
								<p class="mb-0">Open IT Tickets</p>
								<div class="avatar avatar bg-light rounded-circle border">
									<i class="ti ti-badge text-gray-6 fs-20"></i>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
								<div>
									<h2 class="main-title mb-1">18</h2>
									<p class="mb-0 fs-13">5 high priority</p>
								</div>
								<div>
									<div id="tickets-chart"></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Item 4 -->
				<div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
								<p class="mb-0">Background Jobs</p>
								<div class="avatar avatar bg-light rounded-circle border">
									<i class="ti ti-briefcase-2 text-gray-6 fs-20"></i>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
								<div>
									<h2 class="main-title mb-1">Running</h2>
									<p class="mb-0 fs-13">12/12 jobs healthy</p>
								</div>
								<div>
									<div id="jobs-chart"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Storage Usage by Module  -->
			<div class="row row-gap-4 mb-4">
				<div class="col-xxl-8">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
								<h2 class="card-title d-flex align-items-center gap-2 mb-0"><i
										class="ti ti-server text-primary fs-16"></i>Storage Usage by Module (GB)
								</h2>
								<div class="day-active-item one d-inline-flex align-items-center gap-2">
									<button class="day">1D</button>
									<button class="day">7D</button>
									<button class="day">1M</button>
									<button class="day active">1Y</button>
								</div>
							</div>

							<div id="storages-chart"></div>

							<!-- start row -->
							<div class="row row-gap-4 mt-3">
								<!-- Item 1 -->
								<div class="col-md-4 col-sm-6">
									<div class="card border bg-light mb-0">
										<div class="card-body">
											<p class="mb-2 fs-13">Hr</p>
											<h3
												class="mb-0 fs-14 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
												Uptime: 99.9%<span><i
														class="ti ti-shield-check-filled text-success fs-16"></i></span>
											</h3>
										</div>
									</div>
								</div>
								<!-- Item 2 -->
								<div class="col-md-4 col-sm-6">
									<div class="card border bg-light mb-0">
										<div class="card-body">
											<p class="mb-2 fs-13">Payroll Engine</p>
											<h3
												class="mb-0 fs-14 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
												Uptime: 96.9%<span><i
														class="ti ti-shield-check-filled text-success fs-16"></i></span>
											</h3>
										</div>
									</div>
								</div>
								<!-- Item 1 -->
								<div class="col-md-4 col-sm-6">
									<div class="card border bg-light mb-0">
										<div class="card-body">
											<p class="mb-2 fs-13">Attendance Sync</p>
											<h3
												class="mb-0 fs-14 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
												Uptime: 99.9%<span><i
														class="ti ti-shield-check-filled text-success fs-16"></i></span>
											</h3>
										</div>
									</div>
								</div>
								<!-- Item 1 -->
								<div class="col-md-4 col-sm-6">
									<div class="card border bg-light mb-0">
										<div class="card-body">
											<p class="mb-2 fs-13">Recruitment Module</p>
											<h3
												class="mb-0 fs-14 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
												Downtime:19.9%<span><i
														class="ti ti-settings-filled text-danger fs-16"></i></span>
											</h3>
										</div>
									</div>
								</div>
								<!-- Item 1 -->
								<div class="col-md-4 col-sm-6">
									<div class="card border bg-light mb-0">
										<div class="card-body">
											<p class="mb-2 fs-13">Leave Management</p>
											<h3
												class="mb-0 fs-14 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
												Uptime: 91.9%<span><i
														class="ti ti-shield-check-filled text-success fs-16"></i></span>
											</h3>
										</div>
									</div>
								</div>
								<!-- Item 1 -->
								<div class="col-md-4 col-sm-6">
									<div class="card border bg-light mb-0">
										<div class="card-body">
											<p class="mb-2 fs-13">Document Storage</p>
											<h3
												class="mb-0 fs-14 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
												Uptime: 95.9%<span><i
														class="ti ti-shield-check-filled text-success fs-16"></i></span>
											</h3>
										</div>
									</div>
								</div>
							</div>
							<!-- end row -->
						</div>
					</div>
				</div>
				<div class="col-xxl-4">
					<div class="card">
						<div class="card-body">
							<h2 class="card-title d-flex align-items-center gap-2 mb-4"><i
									class="ti ti-shield text-primary fs-16"></i>
								MFA Enabled Users
							</h2>
							<p
								class="sub-title d-flex align-items-center justify-content-between flex-wrap gap-2 text-dark">
								<span class="fw-normal text-gray-6 fs-14"> 2,168 out of 2,436 users</span> 89%
							</p>
							<div id="mfa-chart"></div>
						</div>
					</div>

					<div class="card mb-0">
						<div class="card-body">
							<h2 class="card-title d-flex align-items-center gap-2 mb-4"><i
									class="ti ti-settings-2 text-primary fs-16"></i>Quick IT Actions</h2>

							<div class="row row-gap-4">
								<!-- Item 1 -->
								<div class="col-xxl-6 col-xl-4 col-lg-6 col-md-6 col-sm-6">
									<div class="text-center">
										<div class="avatar avatar-xl bg-dark rounded-circle p-3 mb-2">
											<img src="{{URL::asset('build/img/icons/action-icon-1.svg')}}" alt="action"
												class="img-fluid img-1 ">
										</div>
										<p class="mb-0 fw-medium">Restart HRMS Services</p>
									</div>
								</div>
								<!-- Item 2 -->
								<div class="col-xxl-6 col-xl-4 col-lg-6 col-md-6 col-sm-6">
									<div class="text-center">
										<div class="avatar avatar-xl bg-dark rounded-circle p-3 mb-2">
											<img src="{{URL::asset('build/img/icons/action-icon-2.svg')}}" alt="action"
												class="img-fluid img-1 ">
										</div>
										<p class="mb-0 fw-medium">Sync Biometric </p>
									</div>
								</div>
								<!-- Item 3 -->
								<div class="col-xxl-6 col-xl-4 col-lg-6 col-md-6 col-sm-6">
									<div class="text-center">
										<div class="avatar avatar-xl bg-dark rounded-circle p-3 mb-2">
											<img src="{{URL::asset('build/img/icons/action-icon-3.svg')}}" alt="action"
												class="img-fluid img-1 ">
										</div>
										<p class="mb-0 fw-medium">Clear System Cache</p>
									</div>
								</div>
								<!-- Item 4 -->
								<div class="col-xxl-6 col-xl-4 col-lg-6 col-md-6 col-sm-6">
									<div class="text-center">
										<div class="avatar avatar-xl bg-dark rounded-circle p-3 mb-2">
											<img src="{{URL::asset('build/img/icons/action-icon-4.svg')}}" alt="action"
												class="img-fluid img-1 ">
										</div>
										<p class="mb-0 fw-medium">Schedule Maintenance</p>
									</div>
								</div>
								<!-- Item 5 -->
								<div class="col-xxl-6 col-xl-4 col-lg-6 col-md-6 col-sm-6">
									<div class="text-center">
										<div class="avatar avatar-xl bg-dark rounded-circle p-3 mb-2">
											<img src="{{URL::asset('build/img/icons/action-icon-5.svg')}}" alt="action"
												class="img-fluid img-1 ">
										</div>
										<p class="mb-0 fw-medium">Stop Background Jobs</p>
									</div>
								</div>
								<!-- Item 6 -->
								<div class="col-xxl-6 col-xl-4 col-lg-6 col-md-6 col-sm-6">
									<div class="text-center">
										<div class="avatar avatar-xl bg-dark rounded-circle p-3 mb-2">
											<img src="{{URL::asset('build/img/icons/action-icon-6.svg')}}" alt="action"
												class="img-fluid img-1 ">
										</div>
										<p class="mb-0 fw-medium">Re-run Payroll Job</p>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>

			<!-- User Access & Role Management -->
			<div class="card">
				<div class="card-body pb-0">
					<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
						<h2 class="card-title mb-0"> User Access & Role Management </h2>
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
					<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
						<p class="fs-14 fw-semibold text-gray-9 mb-0">Peak Hours (Today)</p>
						<div class="d-flex align-items-center gap-3 flex-wrap">
							<p class="d-flex align-items-center gap-1 mb-0"> <i
									class="ti ti-square-rounded-filled text-primary fs-15"></i> Low (0-50%) </p>
							<p class="d-flex align-items-center gap-1 mb-0"> <i
									class="ti ti-square-rounded-filled text-success fs-15"></i> Medium (50-70%) </p>
							<p class="d-flex align-items-center gap-1 mb-0"> <i
									class="ti ti-square-rounded-filled text-warning fs-15"></i> High (70-90%) </p>
							<p class="d-flex align-items-center gap-1 mb-0"> <i
									class="ti ti-square-rounded-filled text-danger fs-15"></i> Peak (90-100%) </p>
						</div>
					</div>

					<!-- start row -->
					<div class="row row-gap-4 row-cols-1 row-cols-sm-2 row-cols-md-5 mb-4">
						<!-- Item 1 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">9 AM <i
											class="ti ti-circle-filled text-primary fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										85%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
											style="width: 85%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 2 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">10 AM <i
											class="ti ti-circle-filled text-danger fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										92%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-danger progress-bar-striped" role="progressbar"
											style="width: 92%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 3 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">11 AM <i
											class="ti ti-circle-filled text-primary fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										78%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
											style="width: 78%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 4 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">12 AM <i
											class="ti ti-circle-filled text-success fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										45%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-success progress-bar-striped" role="progressbar"
											style="width: 45%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 5 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">1 AM <i
											class="ti ti-circle-filled text-success fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										38%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-success progress-bar-striped" role="progressbar"
											style="width: 38%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 6 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">2 AM <i
											class="ti ti-circle-filled text-warning fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										68%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-warning progress-bar-striped" role="progressbar"
											style="width: 68%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 7 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">3 AM <i
											class="ti ti-circle-filled text-primary fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										82%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
											style="width: 82%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 8 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">4 AM <i
											class="ti ti-circle-filled text-danger fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										85%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-danger progress-bar-striped" role="progressbar"
											style="width: 85%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 9 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">5 AM <i
											class="ti ti-circle-filled text-primary fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										88%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
											style="width: 88%"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- Item 10 -->
						<div class="col">
							<div class="card border mb-0">
								<div class="card-body">
									<p class="mb-2 fs-13 d-flex align-items-center justify-content-between">6 AM <i
											class="ti ti-circle-filled text-warning fs-8"></i> </p>
									<h3
										class="mb-3 fs-16 fw-semibold d-flex align-items-center justify-content-between flex-wrap gap-2">
										52%</h3>
									<div class="progress progress-xs mb-2">
										<div class="progress-bar bg-warning progress-bar-striped" role="progressbar"
											style="width: 52%"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end row -->

					<h2 class="card-title mb-0"> Login Count Analysis </h2>
					<div id="logactivity-chart"></div>
				</div>
			</div>

			<!-- start row -->
			<div class="row row-gap-4 mb-4">
				<div class="col-xxl-5 d-flex">
					<div class="card flex-fill mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
								<h2 class="card-title d-flex align-items-center gap-2 mb-0"><i
										class="ti ti-script text-primary fs-16"></i>HRMS Usage Trend</h2>
								<div class="day-active-item one d-inline-flex align-items-center gap-2">
									<button class="day">1H</button>
									<button class="day">1D</button>
									<button class="day active">1W</button>
									<button class="day">1m</button>
								</div>
							</div>
							<div class="mb-3">
								<div id="Usage-chart"></div>
							</div>

							<!-- Security  -->
							<div>
								<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
									<h2 class="mb-0 fs-14"> Security & Compliance </h2>
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

								<!-- Item 1 -->
								<div
									class="d-flex flex-md-row flex-column align-items-md-center align-items-start justify-content-between gap-3 flex-wrap mb-4">
									<div class="security-item d-flex align-items-center gap-4">
										<h3 class="main-title mb-0">47 <span class="bg-primary line"></span></h3>
										<h4 class="fs-14 mb-0">
											Failed Logins
											<span class="pt-1 d-block fs-12 fw-normal text-gray-6">Last 24h</span>
										</h4>
									</div>
									<div id="failed-chart"></div>
								</div>

								<!-- Item 2 -->
								<div
									class="d-flex flex-md-row flex-column  align-items-md-center align-items-start justify-content-between gap-3 flex-wrap mb-4">
									<div class="security-item d-flex align-items-center gap-4">
										<h3 class="main-title mb-0">12 <span class="bg-secondary line"></span></h3>
										<h4 class="fs-14 mb-0">
											Suspicious Alerts
											<span class="pt-1 d-block fs-12 fw-normal text-gray-6">Active</span>
										</h4>
									</div>
									<div id="alerts-chart"></div>
								</div>

								<!-- Item 3 -->
								<div
									class="d-flex flex-md-row flex-column  align-items-md-center align-items-start justify-content-between gap-3 flex-wrap">
									<div class="security-item d-flex align-items-center gap-4">
										<h3 class="main-title mb-0">27 <span class="bg-warning line"></span></h3>
										<h4 class="fs-14 mb-0">
											Blocked IPs
											<span class="pt-1 d-block fs-12 fw-normal text-gray-6">Currently
												blocked</span>
										</h4>
									</div>
									<div id="blocked-chart"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xxl-7">
					<!-- User Roles Distribution -->
					<div class="card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
								<h2 class="card-title d-flex align-items-center gap-2 mb-0"><i
										class="ti ti-script text-primary fs-16"></i>User Roles Distribution</h2>
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
							<div
								class="d-flex align-items-start align-items-md-center flex-column flex-md-row gap-3">
								<!-- Item 1 -->
								<div class="user-role-item one">
									<p class="mb-0 fs-12 text-dark">Employees</p>
									<div class="progress progress-xl my-1 rounded">
										<div class="progress-bar bg-primary progress-bar-striped" role="progressbar"
											style="width: 100%"></div>
									</div>
									<p class="mb-0">2145</p>
								</div>

								<!-- Item 2 -->
								<div class="user-role-item two">
									<p class="mb-0 fs-12 text-dark">Managers</p>
									<div class="progress progress-xl my-1 rounded">
										<div class="progress-bar bg-secondary progress-bar-striped"
											role="progressbar" style="width: 100%"></div>
									</div>
									<p class="mb-0">234</p>
								</div>

								<!-- Item 3 -->
								<div class="user-role-item three">
									<p class="mb-0 fs-12 text-dark">HR Operations</p>
									<div class="progress progress-xl my-1 rounded">
										<div class="progress-bar bg-dark progress-bar-striped" role="progressbar"
											style="width: 100%"></div>
									</div>
									<p class="mb-0">45</p>
								</div>

								<!-- Item 4 -->
								<div class="user-role-item four">
									<p class="mb-0 fs-12 text-dark">Admins</p>
									<div class="progress progress-xl my-1 rounded">
										<div class="progress-bar bg-warning progress-bar-striped" role="progressbar"
											style="width: 100%"></div>
									</div>
									<p class="mb-0">12</p>
								</div>
							</div>

						</div>
					</div>

					<!-- Integration Error Counts (24h) -->
					<div class="card mb-0">
						<div class="card-body pb-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-1">
								<h2 class="card-title d-flex align-items-center gap-2 mb-0"><i
										class="ti ti-sparkles text-primary fs-16"></i>Integration Error Counts (24h)
								</h2>
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
							<div id="error-chart"></div>
						</div>
					</div>
				</div>
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