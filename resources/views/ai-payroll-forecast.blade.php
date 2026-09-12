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
					<h2 class="mb-1">AI Payroll Forecast</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								AI Center
							</li>
							<li class="breadcrumb-item active" aria-current="page">AI Payroll Forecast</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
					<div class="me-2 mb-2">
						<div class="dropdown">
							<a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-file-export me-1"></i>Full Report
							</a>
							<ul class="dropdown-menu  dropdown-menu-end p-3">
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
								</li>
								<li>
									<a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
								</li>
							</ul>
						</div>
					</div>
					<a href="#" class="btn btn-primary-gradient mb-2"><i class="ti ti-refresh me-2"></i>Reforecast</a>
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

				<div class="col-xxl-12">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div>
									<h3 class="sub-title d-inline-flex align-items-center mb-1"><i class="ti ti-file-dollar text-primary fs-20 me-1"></i>Budget Alerts</h3>
									<p>AI-powered insights</p>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);" class="border btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										<i class="ti ti-calendar me-1 fs-14"></i>July 2026
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">July 2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Aug 2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1"> Sep 2026</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="row g-4">

								<div class="col-xl-3 col-sm-6 d-flex">
									<div class="card bg-danger z-1 border-0 mb-0 flex-fill">
										<div class="card-body">
											<p class="text-white fw-semibold mb-1">Under Budget</p>
											<p class="text-white mb-0">Dev team 3% below forecast</p>
										</div>
										<img src="{{URL::asset('build/img/bg/card-bg-08.png')}}" alt="bg" class="img-fluid position-absolute top-0 end-0 z-n1 w-auto h-auto">
										<div class="budget-bg bg-danger-900"></div>
									</div> <!-- end card -->
								</div> <!-- end col -->

								<div class="col-xl-3 col-sm-6 d-flex">
									<div class="card bg-success z-1 border-0 mb-0 flex-fill">
										<div class="card-body">
											<p class="text-white fw-semibold mb-1">Over Budget</p>
											<p class="text-white mb-0">Sales bonuses exceeded by 8%</p>
										</div>
										<img src="{{URL::asset('build/img/bg/card-bg-08.png')}}" alt="bg" class="img-fluid position-absolute top-0 end-0 z-n1 w-auto h-auto">
										<div class="budget-bg bg-success-900"></div>
									</div> <!-- end card -->
								</div> <!-- end col -->

								<div class="col-xl-3 col-sm-6 d-flex">
									<div class="card bg-purple z-1 border-0 mb-0 flex-fill">
										<div class="card-body">
											<p class="text-white fw-semibold mb-1">Trending Up</p>
											<p class="text-white mb-0">Q3 costs predicted +5.2%</p>
										</div>
										<img src="{{URL::asset('build/img/bg/card-bg-08.png')}}" alt="bg" class="img-fluid position-absolute top-0 end-0 z-n1 w-auto h-auto">
										<div class="budget-bg bg-purple-900"></div>
									</div> <!-- end card -->
								</div> <!-- end col -->

								<div class="col-xl-3 col-sm-6 d-flex">
									<div class="card bg-info z-1 border-0 mb-0 flex-fill">
										<div class="card-body">
											<p class="text-white fw-semibold mb-1">Review Needed</p>
											<p class="text-white mb-0">Benefits costs up 12% YoY</p>
										</div>
										<img src="{{URL::asset('build/img/bg/card-bg-08.png')}}" alt="bg" class="img-fluid position-absolute top-0 end-0 z-n1 w-auto h-auto">
										<div class="budget-bg bg-info-900"></div>
									</div> <!-- end card -->
								</div> <!-- end col -->

							</div>
						</div>
					</div>
				</div>

			</div>

			<div class="card">
				<div class="card-body pb-0">

					<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
						<div>
							<h3 class="sub-title d-inline-flex align-items-center mb-1"><i class="ti ti-server text-primary fs-20 me-1"></i>Payroll Forecast</h3>
							<p>Predictive payroll modelling with 94.2% confidence · LSTM time-series model · Updated 14 May 2026</p>
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

					<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
						<div class="d-flex align-items-center flex-wrap gap-3">
							<p class="fw-medium d-inline-flex align-items-center gap-2 mb-0">Actual Payroll <span class="text-primary custom-title fw-semibold">9.6M</span></p>
							<p class="fw-medium d-inline-flex align-items-center gap-2 mb-0">Forecast Payroll <span class="text-secondary custom-title fw-semibold">12.6M</span></p>
							<p class="fw-medium d-inline-flex align-items-center gap-2 mb-0">Confidence Band <span class="text-info custom-title fw-semibold">95%</span></p>
						</div>
						<div class="d-flex align-items-center flex-wrap gap-3">
							<p class="d-inline-flex align-items-center gap-1 mb-0"><span class="line-icon d-inline-block bg-primary"></span>Actual Payroll</p>
							<p class="d-inline-flex align-items-center gap-1 mb-0"><span class="line-icon d-inline-block bg-secondary"></span>AI Forecast</p>
						</div>

					</div>
					<div id="payroll-forecast"></div>
				</div>
			</div> <!-- end card -->

			<div class="row">

				<div class="col-xxl-6 col-xl-12 d-flex">
					<div class="card flex-fill">
						<div class="card-body pb-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div>
									<h3 class="sub-title d-inline-flex align-items-center mb-1"><i class="ti ti-server text-primary fs-20 me-1"></i>Variance Analysis</h3>
									<p>Budget vs actual comparison</p>
								</div>
								<div class="d-flex align-items-center gap-2">
									<span class="badge border fs-14 fw-normal text-body"><i class="ti ti-square-filled text-primary fs-12 me-1"></i>Budget</span>
									<span class="badge border fs-14 fw-normal text-body"><i class="ti ti-square-filled text-secondary fs-12 me-1"></i>Actual</span>
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
							<div id="variance-chart"></div>
						</div>
					</div>
				</div> <!-- end col -->

				<div class="col-xxl-6 d-flex flex-column">

					<div class="row flex-fill">

						<div class="col-sm-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">

									<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
										<div class="avatar bg-primary oval-rounded">
											<i class="ti ti-clock-share fs-20"></i>
										</div>
										<p class="text-dark fs-16 fw-semibold">Recent Forecast</p>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<h2 class="mb-1">$2.41M</h2>
											<div class="d-flex align-items-center gap-1 flex-wrap">
												<span class="badge badge-success-transparent">2.3%<i class="ti ti-arrow-up-right ms-1"></i></span>
												<p> vs Apr</p>
											</div>
										</div>
										<div id="forecast-chart"></div>
									</div>
								</div> <!-- end card body -->
							</div> <!-- end card -->
						</div> <!-- end col -->

						<div class="col-sm-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">

									<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
										<div class="avatar bg-secondary oval-rounded">
											<i class="ti ti-api-app fs-20"></i>
										</div>
										<p class="text-dark fs-16 fw-semibold">Q2 Projection</p>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<h2 class="mb-1">$7.18M</h2>
											<p class="fs-13">Within Budget</p>
										</div>
										<div id="projection-chart"></div>
									</div>
								</div> <!-- end card body -->
							</div> <!-- end card -->
						</div> <!-- end col -->

						<div class="col-sm-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">

									<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
										<div class="avatar bg-info oval-rounded">
											<i class="ti ti-badge fs-20"></i>
										</div>
										<p class="text-dark fs-16 fw-semibold">Overtime Est</p>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<h2 class="mb-1">$84K</h2>
											<div class="d-flex align-items-center gap-1 flex-wrap">
												<span class="badge badge-danger-transparent">−12%<i class="ti ti-arrow-down-right ms-1"></i></span>
												<p>Vs Forecast</p>
											</div>
										</div>
										<div id="overtime-chart"></div>
									</div>
								</div> <!-- end card body -->
							</div> <!-- end card -->
						</div> <!-- end col -->

						<div class="col-sm-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">

									<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
										<div class="avatar bg-danger oval-rounded">
											<i class="ti ti-briefcase-2 fs-20"></i>
										</div>
										<p class="text-dark fs-16 fw-semibold">Variance Risk</p>
									</div>
									<div class="d-flex align-items-center justify-content-between">
										<div>
											<h2 class="mb-1">1.2%</h2>
											<p class="fs-13">Low Risk Band</p>
										</div>
										<div id="risk-chart"></div>
									</div>
								</div> <!-- end card body -->
							</div> <!-- end card -->
						</div> <!-- end col -->

					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-xxl-6 col-xl-7 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div>
									<h3 class="sub-title d-inline-flex align-items-center mb-1"><i class="ti ti-server text-primary fs-20 me-1"></i>Department Payroll Costs</h3>
									<p>Monthly allocation by team</p>
								</div>
								<a href="#" class="border btn btn-light btn-icon btn-sm d-inline-flex align-items-center justify-content-center rounded-circle">
									<i class="ti ti-arrow-up-right fs-16"></i>
								</a>
							</div>
							<div class="w-100 d-flex flex-column flex-sm-row">
								<div class="payroll-item col-development flex-grow-1 px-2 position-relative">
									<p class="fs-16 fw-semibold text-dark mb-1">$105000</p>
									<p class="fs-12 mb-5">Development</p>
									<p class="fs-13 text-success mb-4"><i class="ti ti-circle-arrow-up me-1"></i>32.1%</p>
									<div class="progress progress-xl rounded mb-2 mb-sm-0" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-primary" style="width: 100%"></div>
									</div>
								</div>

								<div class="payroll-item flex-grow-1 px-2 position-relative">
									<p class="fs-16 fw-semibold text-dark mb-1">$70000</p>
									<p class="fs-12 mb-5">Sales</p>
									<p class="fs-13 text-danger mb-4"><i class="ti ti-circle-arrow-down me-1"></i>16%</p>
									<div class="progress progress-xl rounded mb-2 mb-sm-0" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-secondary" style="width: 100%"></div>
									</div>
								</div>

								<div class="payroll-item flex-grow-1 px-2 position-relative">
									<p class="fs-16 fw-semibold text-dark mb-1">$35000</p>
									<p class="fs-12 mb-5">Marketing</p>
									<p class="fs-13 text-success mb-4"><i class="ti ti-circle-arrow-up me-1"></i>15.3%</p>
									<div class="progress progress-xl rounded mb-2 mb-sm-0" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-warning" style="width: 100%"></div>
									</div>
								</div>

								<div class="payroll-item flex-grow-1 px-2 position-relative">
									<p class="fs-16 fw-semibold text-dark mb-1">$12000</p>
									<p class="fs-12 mb-5">Support</p>
									<p class="fs-13 text-success mb-4"><i class="ti ti-circle-arrow-up me-1"></i>12.1%</p>
									<div class="progress progress-xl rounded mb-2 mb-sm-0" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-info" style="width: 100%"></div>
									</div>
								</div>

								<div class="payroll-item flex-grow-1 px-2 position-relative">
									<p class="fs-16 fw-semibold text-dark mb-1">$8000</p>
									<p class="fs-12 mb-5">Operations</p>
									<p class="fs-13 text-success mb-4"><i class="ti ti-circle-arrow-up me-1"></i>8.4%</p>
									<div class="progress progress-xl rounded mb-2 mb-sm-0" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										<div class="progress-bar progress-bar-striped bg-purple" style="width: 100%"></div>
									</div>
								</div>

							</div>

						</div>
					</div>
				</div>

				<div class="col-xxl-6 col-xl-5 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<div>
									<h3 class="sub-title d-inline-flex align-items-center mb-1"><i class="ti ti-server text-primary fs-20 me-1"></i>Cost Breakdown</h3>
									<p>Current month distribution</p>
								</div>
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
							<div class="row align-items-center">
								<div class="col-sm-6">
									<div id="breakdown-chart"></div>
								</div>
								<div class="col-sm-6">
									<div class="breakdown-labels vstack gap-3">
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-13 mb-0 d-inline-block"><i class="ti ti-circle-filled text-secondary fs-10 me-1"></i>Salary</p>
											<p class="fs-12 mb-0 d-flex align-items-center gap-2 text-dark"><span class="lebel-line d-inline-block flex-grow-1"></span>70.%</p>
										</div>
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-13 mb-0 d-inline-block"><i class="ti ti-circle-filled text-warning fs-10 me-1"></i>Taxes</p>
											<p class="fs-12 mb-0 d-flex align-items-center gap-2 text-dark"><span class="lebel-line d-inline-block flex-grow-1"></span>10%</p>
										</div>
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-13 mb-0 d-inline-block"><i class="ti ti-circle-filled text-info fs-10 me-1"></i>Bonuses</p>
											<p class="fs-12 mb-0 d-flex align-items-center gap-2 text-dark"><span class="lebel-line d-inline-block flex-grow-1"></span>14%</p>
										</div>
										<div class="d-flex align-items-center justify-content-between">
											<p class="fs-13 mb-0 d-inline-block"><i class="ti ti-circle-filled text-primary fs-10 me-1"></i>Benefits</p>
											<p class="fs-12 mb-0 d-flex align-items-center gap-2 text-dark"><span class="lebel-line d-inline-block flex-grow-1"></span>6%</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div> <!-- end col -->

			</div>

		</div>
		<!-- End Content -->

		@include('partials.footer')

	</div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection