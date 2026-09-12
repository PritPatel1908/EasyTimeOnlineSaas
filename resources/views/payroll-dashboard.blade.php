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
					<h2 class="mb-1">Payroll Dashboard</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">Payroll Dashboard</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-arrow-bar-to-down me-1"></i>Montly Report
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Daily Report</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Weekly Report </a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Montly Report </a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1">Yearly Report </a>
								</li>
							</ul>
						</div>
					</div>
					<div class="mb-2">
						<div class="input-icon w-100 position-relative">
							<span class="input-icon-addon">
								<i class="ti ti-calendar text-gray-9"></i>
							</span>
							<input type="text" class="form-control yearpicker" value="December 2025">
						</div>
					</div>
					<div class="ms-2 head-icons">
						<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
							data-bs-original-title="Collapse" id="collapse-header">
							<i class="ti ti-chevrons-up"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- Processing -->
			<div class="card mb-4">
				<div class="card-body">
					<!-- Pay -->
					<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
						<div class="d-flex align-items-center flex-wrap gap-3">
							<div class="d-flex align-items-center gap-2 border-end pe-3">
								<p class="mb-0 d-flex align-items-center gap-1 text-gray-9"> <i
										class="ti ti-point-filled text-success lh-0"></i> Payroll Status: </p>
								<span
									class="badge badge-soft-success rounded border border-success">Processing</span>
							</div>
							<div class="d-flex align-items-center gap-2 border-end pe-3">
								<p class="mb-0 d-flex align-items-center gap-1 text-gray-9"> Cut-off: Dec 25, 2024
								</p>
							</div>
							<div class="d-flex align-items-center gap-2">
								<p class="mb-0 d-flex align-items-center gap-1 text-gray-9"> Payment Date: Dec 31,
									2024 </p>
							</div>
						</div>
						<a href="#" class="btn btn-primary-gradient "> <i class="ti ti-analyze"></i> Run Payroll</a>
					</div>
				</div>
			</div>

			<!-- start row -->
			<div class="row mb-4">
				<div class="col-xxl-5">
					<!-- Overview -->
					<div class="card bg-dark position-relative overflow-hidden overview-card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
								<h2 class="card-title text-white mb-0">Overview</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-icon btn-sm d-inline-flex align-items-center justify-content-center rounded-circle"
										data-bs-toggle="dropdown">
										<i class="ti ti-dots-vertical fs-16"></i>
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
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
								<div class="d-flex align-items-center gap-2">
									<div class="avatar avatar-xl bg-primary rounded-circle">
										<i class="ti ti-moneybag fs-24"></i>
									</div>
									<div>
										<h3 class="custom-title mb-2 text-white">$2,458,320</h3>
										<p class="mb-0  text-white fs-13">Total Gross Payroll</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-2">
									<div class="avatar avatar-xl bg-secondary rounded-circle">
										<i class="ti ti-bell-dollar fs-24"></i>
									</div>
									<div>
										<h3 class="custom-title mb-2 text-white">$1,987,450</h3>
										<p class="mb-0  text-white fs-13">Net Payable Amount</p>
									</div>
								</div>
							</div>

							<div
								class="d-flex align-items-center justify-content-between flex-wrap flex-md-nowrap gap-3">
								<!-- Item 1 -->
								<div class="card mb-0 w-100 rounded-xxl pay-card">
									<div class="card-body text-center">
										<div class="d-flex align-items-center gap-2 flex-column">
											<div class="avatar avatar-xl bg-white rounded-circle mb-3">
												<i class="ti ti-flag-dollar fs-24 text-gray-9"></i>
											</div>
											<div>
												<h3 class="mb-2 custom-title">$1,987,450</h3>
												<p class="mb-0  text-gray-9 fs-13">Total <span
														class="d-block">Deductions</span> </p>
											</div>
										</div>
									</div>
								</div>
								<!-- Item 2 -->
								<div class="card mb-0 w-100 rounded-xxl pay-card">
									<div class="card-body text-center">
										<div class="d-flex align-items-center gap-2 flex-column">
											<div class="avatar avatar-xl bg-white rounded-circle mb-3">
												<i class="ti ti-loader-3 fs-24 text-gray-9"></i>
											</div>
											<div>
												<h3 class="mb-2 custom-title">12</h3>
												<p class="mb-0  text-gray-9 fs-13">Pending <span class="d-block">
														Approvals</span></p>
											</div>
										</div>
									</div>
								</div>
								<!-- Item 3 -->
								<div class="card mb-0 w-100 rounded-xxl pay-card">
									<div class="card-body text-center">
										<div class="d-flex align-items-center gap-2 flex-column">
											<div class="avatar avatar-xl bg-white rounded-circle mb-3">
												<i class="ti ti-info-triangle fs-24 text-gray-9"></i>
											</div>
											<div>
												<h3 class="mb-2 custom-title">24</h3>
												<p class="mb-0  text-gray-9 fs-13">Total Payroll <span
														class="d-block">Errors</span> </p>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>
						<img src="{{URL::asset('build/img/bg/bg-04.png')}}" alt="bg" class="img-fluid pay-bg">
					</div>

					<!-- Salery -->
					<div class="row row-gap-3">

						<div class="col-xxl-6 col-xl-3 col-lg-6 col-sm-6">
							<!-- Item 1  -->
							<div class="card mb-0">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar avatar-md bg-dark rounded-circle">
											<i class="ti ti-info-triangle fs-12"></i>
										</div>
										<p class="mb-0 fs-13">Highest Salary</p>
									</div>
									<div
										class="mb-1 d-flex align-items-center justify-content-start gap-2 flex-wrap">
										<h4 class="mb-0 sub-title">$24,500</h4>
										<div
											class="border rounded-pill fs-13 text-gray-9 d-flex align-items-center gap-2 p-1 ps-2">
											+18%
											<p
												class="btn btn-sm btn-icon bg-success pe-none rounded-circle d-flex align-items-center justify-content-center fs-20 text-white">
												<i class="ti ti-arrow-up-right"></i>
											</p>
										</div>
									</div>
									<p class="mb-0 fs-12">CTO - Engineering</p>
								</div>
							</div>
						</div> <!-- end col -->

						<div class="col-xxl-6 col-xl-3 col-lg-6 col-sm-6">
							<!-- Item 2  -->
							<div class="card mb-0">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar avatar-md bg-dark rounded-circle">
											<i class="ti ti-inner-shadow-bottom fs-12"></i>
										</div>
										<p class="mb-0 fs-13">Variable Pay</p>
									</div>
									<div
										class="mb-1 d-flex align-items-center justify-content-start gap-2 flex-wrap">
										<h4 class="mb-0 sub-title">$284K</h4>
										<div
											class="border rounded-pill fs-13 text-gray-9 d-flex align-items-center gap-2 p-1 ps-2">
											+18%
											<p
												class="btn btn-sm btn-icon pe-none bg-success rounded-circle d-flex align-items-center justify-content-center fs-20 text-white">
												<i class="ti ti-arrow-up-right"></i>
											</p>
										</div>
									</div>
									<p class="mb-0 fs-12">Bonuses + Commissions</p>
								</div>
							</div>
						</div> <!-- end col -->

						<div class="col-xxl-6 col-xl-3 col-lg-6 col-sm-6">
							<!-- Item 3  -->
							<div class="card mb-0">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar avatar-md bg-dark rounded-circle">
											<i class="ti ti-info-triangle fs-12"></i>
										</div>
										<p class="mb-0 fs-13">After Deduction</p>
									</div>
									<div
										class="mb-1 d-flex align-items-center justify-content-start gap-2 flex-wrap">
										<h4 class="mb-0 sub-title">$1.99M</h4>
										<div
											class="border rounded-pill fs-13 text-gray-9 d-flex align-items-center gap-2 p-1 ps-2">
											-16%
											<p
												class="btn btn-sm btn-icon bg-danger pe-none rounded-circle d-flex align-items-center justify-content-center fs-20 text-white">
												<i class="ti ti-arrow-down-right"></i>
											</p>
										</div>
									</div>
									<p class="mb-0 fs-12">Employee take-home</p>
								</div>
							</div>
						</div> <!-- end col -->

						<div class="col-xxl-6 col-xl-3 col-lg-6 col-sm-6">
							<!-- Item 4  -->
							<div class="card mb-0">
								<div class="card-body">
									<div class="d-flex align-items-center gap-2 mb-3">
										<div class="avatar avatar-md bg-dark rounded-circle">
											<i class="ti ti-report-money fs-12"></i>
										</div>
										<p class="mb-0 fs-13">Average Salary</p>
									</div>
									<div
										class="mb-1 d-flex align-items-center justify-content-start gap-2 flex-wrap">
										<h4 class="mb-0 sub-title">$78,450</h4>
										<div
											class="border rounded-pill fs-13 text-gray-9 d-flex align-items-center gap-2 p-1 ps-2">
											+18%
											<p
												class="btn btn-sm btn-icon pe-none bg-success rounded-circle d-flex align-items-center justify-content-center fs-20 text-white">
												<i class="ti ti-arrow-up-right"></i>
											</p>
										</div>
									</div>
									<p class="mb-0 fs-12">Median: $72,000</p>
								</div>
							</div>
						</div> <!-- end col -->
					</div>
					<!-- end row -->
				</div>

				<div class="col-xxl-7">
					<!-- Salary Range Distribution -->
					<div class="card mb-0 mt-xxl-0 mt-4">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
								<h5 class="mb-0 card-title">Salary Range Distribution</h5>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-light btn-icon btn-sm d-inline-flex align-items-center justify-content-center rounded-circle"
										data-bs-toggle="dropdown">
										<i class="ti ti-dots-vertical fs-16"></i>
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

							<div>
								<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
									<h6 class="fs-24 d-flex align-items-center gap-2 mb-0 main-title"> <span
											class="fs-14 fw-normal text-gray-6">Average Salary</span> $78,450 </h6>
									<p class="d-flex align-items-center gap-1 mb-0 text-dark"> <i
											class="ti ti-circle-filled text-primary fs-12"></i> Salary Range </p>
								</div>
								<div id="distribution-chart"></div>

								<div class="mt-2">
									<div
										class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
										<h6 class="fs-14 mb-0 fw-medium card-title">Range</h6>
										<div class="dropdown">
											<a href="javascript:void(0);"
												class="d-inline-flex p-2 rounded align-items-center justify-content-center fs-14 fw-medium gap-1"
												data-bs-toggle="dropdown">
												Last 7 Days <i class="ti ti-chevron-down fs-14"></i>
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

									<!-- Item 1 -->
									<div class="range-item mb-3">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<p class="mb-0">$30k-50k</p>
											<p class="fw-medium text-dark mb-0">285<span
													class="ms-2 fw-normal text-gray-6 fs-13">(22.8%)</span></p>
										</div>
										<div class="progress progress-pill-gradient">
											<div class="progress-bar" role="progressbar" style="width: 75%;"
												aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</div>

									<!-- Item 2 -->
									<div class="range-item mb-3">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<p class="mb-0">$50k-80k</p>
											<p class="fw-medium text-dark mb-0">478<span
													class="ms-2 fw-normal text-gray-6 fs-13">(38.3%)</span></p>
										</div>
										<div class="progress progress-pill-gradient">
											<div class="progress-bar" role="progressbar" style="width: 85%;"
												aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</div>

									<!-- Item 3 -->
									<div class="range-item mb-3">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<p class="mb-0">$80k-120k</p>
											<p class="fw-medium text-dark mb-0">342<span
													class="ms-2 fw-normal text-gray-6 fs-13">(27.4%)</span></p>
										</div>
										<div class="progress progress-pill-gradient">
											<div class="progress-bar" role="progressbar" style="width: 72%;"
												aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</div>

									<!-- Item 4 -->
									<div class="range-item mb-3">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<p class="mb-0">$80k-120k</p>
											<p class="fw-medium text-dark mb-0">98<span
													class="ms-2 fw-normal text-gray-6 fs-13">(7.8%)</span></p>
										</div>
										<div class="progress progress-pill-gradient">
											<div class="progress-bar" role="progressbar" style="width: 79%;"
												aria-valuenow="79" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</div>

									<!-- Item 5 -->
									<div class="range-item mb-4">
										<div class="d-flex align-items-center justify-content-between mb-1">
											<p class="mb-0">$80k-120k</p>
											<p class="fw-medium text-dark mb-0">45<span
													class="ms-2 fw-normal text-gray-6 fs-13">(3.6%)</span></p>
										</div>
										<div class="progress progress-pill-gradient">
											<div class="progress-bar" role="progressbar" style="width: 40%;"
												aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</div>

									<div
										class="mb-0 fs-14 fw-medium text-dark d-flex align-items-center justify-content-center gap-2">
										View Salary Report in realtime
										<a href="#"
											class="btn btn-sm btn-icon border bg-light d-flex align-items-center justify-content-center rounded-circle"><i
												class="ti ti-arrow-curve-right fs-16"></i></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row mb-4 row-gap-4">
				<div class="col-xxl-4 col-xl-6">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
								<h2 class="mb-0 card-title">Batch Processing Status</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar me-1"></i>Montly
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Daily
												Report</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Weekly
												Report </a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Montly
												Report </a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Yearly
												Report </a>
										</li>
									</ul>
								</div>
							</div>
							<div>
								<p class="mb-1">Combined Total</p>
								<h2 class="d-flex align-items-center align-items-center gap-2">$2,459,320 <span
										class="badge badge-soft-success fs-12 border border-success rounded-pill">3
										Batches</span> </h2>
							</div>

							<div class="process-chart">
								<div class="chart-1 chart">
									<h3 class="title">
										1120 Employees
										<span>$2,184,500</span>
									</h3>
									<div class="graph"></div>
								</div>
								<div class="chart-2 chart">
									<h3 class="title">
										60 Employees
										<span>$124,850</span>
									</h3>
									<div class="graph"></div>
								</div>
								<div class="chart-3 chart">
									<h3 class="title">
										63 Employees
										<span>$154,850</span>
									</h3>
									<div class="graph"></div>
								</div>
								<!-- <div id="process-chart"></div> -->
							</div>

							<div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
								<p class="d-flex align-items-center gap-2 fs-13 text-gray-9 mb-0"><i
										class="ti ti-circle-filled text-primary fs-12"></i> Full Time</p>
								<p class="d-flex align-items-center gap-2 fs-13 text-gray-9 mb-0"><i
										class="ti ti-circle-filled text-secondary fs-12"></i> Part Time</p>
								<p class="d-flex align-items-center gap-2 fs-13 text-gray-9 mb-0"><i
										class="ti ti-circle-filled text-dark fs-12"></i> Contractors</p>
							</div>


						</div>
					</div>
				</div>
				<!-- end col -->

				<div class="col-xxl-4 col-xl-6">
					<div class="card mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
								<h2 class="mb-0 card-title">6 Months Tax & Deduction Trend</h2>
							</div>
							<div id="chart-trend"></div>
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
								<h3 class="mb-0 d-flex align-items-center gap-2 fs-16">
									$1.71M
									<p
										class="btn btn-sm btn-icon bg-success pe-none d-flex align-items-center justify-content-center rounded-circle">
										<i class="ti ti-arrow-up-right fs-12"></i>
									</p>
								</h3>
								<div class="d-flex align-items-center gap-3">
									<p class="mb-0 fs-12 d-flex align-items-center gap-1"><span
											class="square-icon rounded-circle bg-secondary"><i
												class="ti ti-check text-white fs-9"></i></span> Federal Tax</p>
									<p class="mb-0 fs-12 d-flex align-items-center gap-1"><span
											class="square-icon rounded-circle bg-primary"><i
												class="ti ti-check text-white fs-9"></i></span>Deductions</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end col -->

				<div class="col-xxl-4">
					<div class="row row-gap-4">
						<div class="col-xxl-12 col-xl-4">
							<!-- Item 1 -->
							<div class="card mb-0">
								<div class="card-body">
									<div>
										<div
											class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
											<h4 class="mb-2 fs-14">Life Insurance</h4>
											<div id="insurence-chart"></div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
											<p class="mb-0 fs-13">Per Employee</p>
											<p class="mb-0 fs-13">$24.87</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xxl-12 col-xl-4">
							<!-- Item 2 -->
							<div class="card mb-0">
								<div class="card-body">
									<div>
										<div
											class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
											<h4 class="mb-2 fs-14">401(k) Contribution</h4>
											<div id="contribution-chart"></div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
											<p class="mb-0 fs-13">Per Employee</p>
											<p class="mb-0 fs-13">$10.47</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xxl-12 col-xl-4">
							<!-- Item 3 -->
							<div class="card mb-0">
								<div class="card-body">
									<div>
										<div
											class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
											<h4 class="mb-2 fs-14">Health Insurance</h4>
											<div id="health-chart"></div>
										</div>
										<div
											class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
											<p class="mb-0 fs-13">Per Employee</p>
											<p class="mb-0 fs-13">$26.87</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end col -->
			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row mb-4 row-gap-4">
				<div class="col-xl-6 d-flex">
					<div class="card flex-fill mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
								<h2 class="mb-0 card-title">Payroll Processing Pipeline</h2>
								<span class="badge badge-soft-danger border fs-12 rounded-pill">Processing 4 to
									6</span>
							</div>
							<div class="mb-4">
								<h3 class="fs-14 d-flex align-items-center justify-content-between mb-1">Overall
									Progress <span class="fs-13 fw-normal text-muted">(40%)</span> </h3>
								<div class="progress progress-pill-secondary">
									<div class="progress-bar" role="progressbar" style="width: 75%;"
										aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>


							<!-- Item 1  -->
							<div class="pipline-check mb-2">
								<div class="d-flex align-items-center gap-2">
									<div class="form-check">
										<input class="form-check-input" type="radio" name="flexRadioDefault"
											id="flexRadioDefault1" checked>
										<label class="form-check-label text-dark" for="flexRadioDefault1"></label>
									</div>
									<div class="">
										<h3 class="mb-1 fs-14"> Data Validation</h3>
										<p class="mb-0 fs-13">All employee records validated</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<span
										class="badge badge-soft-success d-inline-flex align-items-center gap-1 rounded-pill">
										<i class="ti ti-circle-filled text-success fs-5"></i> Completed</span>
									<div class="cirle-progress active">
										<span>2 Min</span>
									</div>
								</div>
							</div>

							<!-- Item 2  -->
							<div class="pipline-check mb-2">
								<div class="d-flex align-items-center gap-2">
									<div class="form-check">
										<input class="form-check-input" type="radio" name="flexRadioDefault"
											id="flexRadioDefault2">
										<label class="form-check-label text-dark" for="flexRadioDefault2"></label>
									</div>
									<div class="">
										<h3 class="mb-1 fs-14">Attendance Calculation </h3>
										<p class="mb-0 fs-13">Working days and hours calculated</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<span
										class="badge badge-soft-success d-inline-flex align-items-center gap-1 rounded-pill">
										<i class="ti ti-circle-filled text-success fs-5"></i> Completed</span>
									<div class="cirle-progress active">
										<span>4 Min</span>
									</div>
								</div>
							</div>

							<!-- Item 3  -->
							<div class="pipline-check mb-2">
								<div class="d-flex align-items-center gap-2">
									<div class="form-check">
										<input class="form-check-input" type="radio" name="flexRadioDefault"
											id="flexRadioDefault3">
										<label class="form-check-label text-dark" for="flexRadioDefault3"></label>
									</div>
									<div class="">
										<h3 class="mb-1 fs-14">Salary Computation</h3>
										<p class="mb-0 fs-13">Base salary + allowances calculated</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<span
										class="badge badge-soft-success d-inline-flex align-items-center gap-1 rounded-pill">
										<i class="ti ti-circle-filled text-success fs-5"></i> Completed</span>
									<div class="cirle-progress active">
										<span>3 Min</span>
									</div>
								</div>
							</div>

							<!-- Item 4  -->
							<div class="pipline-check mb-2">
								<div class="d-flex align-items-center gap-2">
									<div class="form-check">
										<input class="form-check-input" type="radio" name="flexRadioDefault"
											id="flexRadioDefault4">
										<label class="form-check-label text-dark" for="flexRadioDefault4"></label>
									</div>
									<div class="">
										<h3 class="mb-1 fs-14"> Deductions Processing </h3>
										<p class="mb-0 fs-13">Tax, PF, and other deductions</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<span
										class="badge badge-soft-info d-inline-flex align-items-center gap-1 rounded-pill">
										<i class="ti ti-circle-filled text-info fs-5"></i> Running</span>
									<div class="cirle-progress"></div>
								</div>
							</div>

							<!-- Item 5  -->
							<div class="pipline-check mb-2">
								<div class="d-flex align-items-center gap-2">
									<div class="form-check">
										<input class="form-check-input" type="radio" name="flexRadioDefault"
											id="flexRadioDefault5">
										<label class="form-check-label text-dark" for="flexRadioDefault5"></label>
									</div>
									<div class="">
										<h3 class="mb-1 fs-14">Final Verification </h3>
										<p class="mb-0 fs-13">Admin approval required</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<span
										class="badge bg-light d-inline-flex align-items-center gap-1 text-dark rounded-pill">
										<i class="ti ti-circle-filled text-dark fs-5"></i> Pending</span>
									<div class="cirle-progress"></div>
								</div>
							</div>

							<!-- Item 6  -->
							<div class="pipline-check mb-4">
								<div class="d-flex align-items-center gap-2">
									<div class="form-check">
										<input class="form-check-input" type="radio" name="flexRadioDefault"
											id="flexRadioDefault6">
										<label class="form-check-label text-dark" for="flexRadioDefault6"></label>
									</div>
									<div class="">
										<h3 class="mb-1 fs-14">Payment Generation</h3>
										<p class="mb-0 fs-13">Bank file creation</p>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<span
										class="badge bg-light d-inline-flex align-items-center gap-1 text-dark rounded-pill">
										<i class="ti ti-circle-filled text-dark fs-5"></i> Pending</span>
									<div class="cirle-progress"></div>
								</div>
							</div>

							<div class="d-flex align-items-center gap-2 flex-sm-row flex-column">
								<a href="#"
									class="btn btn-primary-gradient d-flex align-items-center justify-content-center gap-1 w-100 rounded-pill"><i
										class="ti ti-loader-3"></i>Continue Processing</a>
								<a href="#"
									class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-1 w-100 rounded-pill"><i
										class="ti ti-file-export"></i>Export Report</a>
							</div>

						</div>
					</div>
				</div>
				<!-- end col  -->

				<div class="col-xl-6 d-flex">
					<div class="card flex-fill bg-linear-gradient mb-0">
						<div class="card-body d-flex flex-column align-items-start justify-content-between">
							<div>
								<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
									<div class="d-flex align-items-center gap-2">
										<div class="avatar avatar-xl bg-dark rounded-circle">
											<i class="ti ti-robot fs-24"></i>
										</div>
										<div>
											<h4 class="mb-1 sub-title">How can i help you today</h4>
											<p>AI Payroll Assistant</p>
										</div>
									</div>
									<span
										class="badge bg-success d-inline-flex align-items-center gap-1 rounded-pill"><i
											class="ti ti-point-filled text-white fs-6 lh-0"></i>Online</span>
								</div>

								<div>
									<h4 class="mb-2 fs-16 card-title">Quick Actions</h4>
									<div class="d-flex align-items-center gap-2 flex-wrap mb-3">
										<span class="click-tag active">Calculate overtime pay</span>
										<span class="click-tag">Generate tax report</span>
										<span class="click-tag">Error Detection</span>
										<span class="click-tag">Compliance Monitoring</span>
										<span class="click-tag">Analyze salary trends</span>
										<span class="click-tag">Review payroll errors</span>
									</div>
									<div class="card bg-transparent border rounded-md mb-5">
										<div class="card-body">
											<span class="d-flex align-items-center gap-2 mb-2 text-primary"> <i
													class="ti ti-sparkles fs-16"></i> AI Assistant</span>
											<p class="mb-1">Hello! I'm your AI Payroll Assistant. I can help you
												with payroll calculations, tax questions, compliance issues. How can
												I assist you today?</p>
											<p class="mb-0 text-dark">11:45 PM</p>
										</div>
									</div>
								</div>
							</div>
							<div class="w-100">
								<h5 class="mb-2 fs-13">Suggested Questions</h5>
								<div class="d-flex align-items-center gap-2 flex-wrap">
									<p class="mb-0">How do I process a bonus payment?</p>
									<p class="mb-0">What are the tax implications of workers?</p>
								</div>
								<div class="chat-input-item mt-3">
									<input type="text" class="form-control"
										placeholder="Ask me anything about payroll">
									<a href="#" class="btn btn-icon"><i class="ti ti-send"></i></a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end col  -->
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