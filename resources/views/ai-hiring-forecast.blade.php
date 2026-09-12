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
					<h2 class="mb-1">AI Hiring Forecast</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								AI Center
							</li>
							<li class="breadcrumb-item active" aria-current="page">AI Hiring Forecast</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-3">
					<div class="dropdown">
						<a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown"> <i class="ti ti-file-export me-1"></i>Export</a>
						<ul class="dropdown-menu  dropdown-menu-end p-3">
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a>
							</li>
						</ul>
					</div>
					<a href="#" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2"> <i class="ti ti-repeat"></i> Update Forecast</a>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- start row -->
			<div class="row row-gap-4 mb-4">
				<!-- Start Hiring Timeline Forecast -->
				<div class="col-lg-12">
					<div class="card mb-0">
						<div class="card-body pb-3">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Hiring Timeline Forecast</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
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
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
								<div class="d-flex align-items-center gap-sm-4 gap-2 flex-wrap">
									<div class="border rounded p-3">
										<p class="mb-2">Avaerage Actual Hire </p>
										<h3 class="mb-0 fs-20 fw-semibold text-primary">169</h3>
									</div>
									<div class="border rounded p-3">
										<p class="mb-2">Average Predicted Hire  </p>
										<h3 class="mb-0 fs-20 fw-semibold text-secondary">215</h3>
									</div>
								</div>
								<div class="d-flex align-items-center gap-3">
									<p class="d-flex align-items-center gap-1 text-dark mb-0">
										<i class="ti ti-circle-filled text-primary fs-13"></i> Actual Hires
									</p>
									<p class="d-flex align-items-center gap-1 text-dark mb-0">
										<i class="ti ti-square-rounded-filled text-secondary fs-13"></i>Predicted Hires
									</p>
								</div>
							</div>
							<!-- Hiring chart -->
							<div id="hiring-timline-chart"></div>
						</div>
					</div>
				</div>
				<!-- End Hiring Timeline Forecast -->

				<!-- Start Hiring Statistics -->
				<div class="col-xxl-7 col-xl-8 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Hiring Statistics</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center gap-2"
										data-bs-toggle="dropdown">
										<i class="ti ti-chart-arrows-vertical"></i>Q3
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q3</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q2</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q1</a>
										</li>
									</ul>
								</div>
							</div>

							<!-- start row -->
							<div class="row row-gap-4">
								<!-- Item 1 -->
								<div class="col-sm-6">
									<div class="card mb-0">
										<div class="card-body">
											<div class="d-flex align-items-center flex-wrap gap-3 mb-3">
												<div class="avatar avatar-lg bg-primary rounded-circle flex-shrink-0">
													<i class="ti ti-users-group text-white fs-24"></i>
												</div>
												<div>
													<p class="mb-1">Q3 Headcount Need</p>
													<div  class="d-flex align-items-center gap-2">
														<h3 class="text-dark mb-0">+23 </h3>
														<div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2"> +4<span class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i class="ti ti-arrow-up-right fs-20"></i></span></div>
													</div>
												</div>
											</div>
											<div id="statistics-chart"></div>
										</div>
									</div>
								</div>
								<!-- Item 2 -->
								<div class="col-sm-6">
									<div class="card mb-0">
										<div class="card-body">
											<div class="d-flex align-items-center flex-wrap gap-3 mb-3">
												<div class="avatar avatar-lg bg-secondary rounded-circle flex-shrink-0">
													<i class="ti ti-info-octagon text-white fs-24"></i>
												</div>
												<div>
													<p class="mb-1">Attrition Risk</p>
													<div  class="d-flex align-items-center gap-2">
														<h3 class="text-dark mb-0">7.2% </h3>
														<div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2"> +18%<span class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i class="ti ti-arrow-up-right fs-20"></i></span></div>
													</div>
												</div>
											</div>
											<div id="statistics-chart-two"></div>
										</div>
									</div>
								</div>
								<!-- Item 3 -->
								<div class="col-sm-6">
									<div class="card mb-0">
										<div class="card-body">
											<div class="d-flex align-items-center flex-wrap gap-3 mb-3">
												<div class="avatar avatar-lg bg-purple rounded-circle flex-shrink-0">
													<i class="ti ti-briefcase text-white fs-24"></i>
												</div>
												<div>
													<p class="mb-1">Open Roles</p>
													<div  class="d-flex align-items-center gap-2">
														<h3 class="text-dark mb-0">18 </h3>
														<div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">-16%<span class="bg-danger btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i class="ti ti-arrow-down-right fs-20"></i></span></div>
													</div>
												</div>
											</div>
											<div id="statistics-chart-three"></div>
										</div>
									</div>
								</div>
								<!-- Item 4 -->
								<div class="col-sm-6">
									<div class="card mb-0">
										<div class="card-body">
											<div class="d-flex align-items-center flex-wrap gap-3 mb-3">
												<div class="avatar avatar-lg bg-info rounded-circle flex-shrink-0">
													<i class="ti ti-file-search text-white fs-24"></i>
												</div>
												<div>
													<p class="mb-1">Offer Accept Rate</p>
													<div  class="d-flex align-items-center gap-2">
														<h3 class="text-dark mb-0">83% </h3>
														<div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2"> +8%<span class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1"><i class="ti ti-arrow-up-right fs-20"></i></span></div>
													</div>
												</div>
											</div>
											<div id="statistics-chart-four"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Hiring Statistics -->

				<!-- Start Pipeline Overview -->
				<div class="col-xxl-5 col-xl-4 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Hiring Pipeline Overview</h2>
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
							<div id="pipeline-overview-chart"></div>
							<div class="pipeline-value">
								<div class="value position-relative d-flex align-items-center justify-content-between text-dark"><p class="d-flex align-items-center gap-2 mb-0"><span class="bg-primary line"></span> Applied </p><span class="fs-20 fw-semibold">59%</span></div>
								<div class="value position-relative d-flex align-items-center justify-content-between text-dark"><p class="d-flex align-items-center gap-2 mb-0"><span class="bg-secondary line"></span> Screening </p><span class="fs-20 fw-semibold">21%</span></div>
								<div class="value position-relative d-flex align-items-center justify-content-between text-dark"><p class="d-flex align-items-center gap-2 mb-0"><span class="bg-warning line"></span> Interview </p><span class="fs-20 fw-semibold">12%</span></div>
								<div class="value position-relative d-flex align-items-center justify-content-between text-dark"><p class="d-flex align-items-center gap-2 mb-0"><span class="bg-success line"></span> Accepted </p><span class="fs-20 fw-semibold">8%</span></div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Pipeline Overview -->

				<!-- Start Open Role Pipeline -->
				<div class="col-xxl-8 col-xl-12 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body p-0">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 my-3 px-4">
								<h2 class="mb-0 card-title">Open Role Pipeline</h2>
								<a href="javascript:void(0);" class="border btn btn-light btn-icon btn-sm d-inline-flex align-items-center justify-content-center rounded-circle">
									<i class="ti ti-arrow-up-right fs-16"></i>
								</a>
							</div>

							<div class="table-responsive">
								<table class="table table-nowrap mb-0">
									<thead>
										<tr>
											<th>Role</th>
											<th>Department</th>
											<th>Urgency</th>
											<th>Openings</th>
											<th>Pipeline fill</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">Office Management App</a></p>
											</td>
											<td><p class="fw-medium mb-0">Engineering</p></td>
											<td>
												<span
													class="badge bg-outline-danger d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Critical
												</span>
											</td>
											<td>2</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-primary progress-bar-striped" style="width: 90%"></div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">Sales Executive</a></p>
											</td>
											<td><p class="fw-medium mb-0">Sales</p></td>
											<td>
												<span
													class="badge bg-outline-purple d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>High
												</span>
											</td>
											<td>5</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-purple progress-bar-striped" style="width: 60%"></div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">Product Designer</a></p>
											</td>
											<td><p class="fw-medium mb-0">Product</p></td>
											<td>
												<span
													class="badge bg-outline-info d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Planned
												</span>
											</td>
											<td>3</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-info progress-bar-striped" style="width: 78%"></div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">Data Analyst</a></p>
											</td>
											<td><p class="fw-medium mb-0">Operations</p></td>
											<td>
												<span
													class="badge bg-outline-purple d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>High
												</span>
											</td>
											<td>6</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-purple progress-bar-striped" style="width: 85%"></div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">HR Business Partner</a></p>
											</td>
											<td><p class="fw-medium mb-0">HR & Admin</p></td>
											<td>
												<span
													class="badge bg-outline-success d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Low
												</span>
											</td>
											<td>3</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-success progress-bar-striped" style="width: 95%"></div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">Digital Marketing Specialist</a></p>
											</td>
											<td><p class="fw-medium mb-0">Marketing</p></td>
											<td>
												<span
													class="badge bg-outline-danger d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Critical
												</span>
											</td>
											<td>2</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-primary progress-bar-striped" style="width: 90%"></div>
												</div>
											</td>
										</tr>
										<tr>
											<td>
												<p class="fw-semibold mb-0"><a href="{{url('project-details')}}">Business Analyst</a></p>
											</td>
											<td><p class="fw-medium mb-0">Operations</p></td>
											<td>
												<span class="badge bg-outline-info d-inline-flex align-items-center badge-xs">
													<i class="ti ti-point-filled me-1"></i>Planned
												</span>
											</td>
											<td>5</td>
											<td>
												<div class="progress progress-xs w-100" role="progressbar"
													aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
													<div class="progress-bar bg-info progress-bar-striped" style="width: 95%"></div>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<!-- End Open Role Pipeline -->

				<!-- Start Budget Allocation by Department -->
				<div class="col-xxl-4 col-xl-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Budget Allocation by Department</h2>
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
							<div class="d-flex align-items-center gap-3">
								<p class="d-flex align-items-center gap-1 text-dark mb-0">
									<i class="ti ti-square-rounded-filled text-primary fs-13"></i> Used
								</p>
								<p class="d-flex align-items-center gap-1 text-dark mb-0">
									<i class="ti ti-square-rounded-filled text-light fs-13"></i>Available
								</p>
							</div>
							<div id="budget-allocation-chart"></div>
						</div>
					</div>
				</div>
				<!-- End Budget Allocation by Department -->

				<!-- Start Role Demand Forecast -->
				<div class="col-xxl-4 col-xl-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="mb-4">
								<h2 class="mb-0 card-title">Role Demand Forecast</h2>
							</div>

							<div id="role-demand-chart"></div>
							<p class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom-dashed">Backend Dev <span class="badge bg-success">32%</span></p>
							<p class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom-dashed">Sales Rep <span class="badge bg-purple">24%</span></p>
							<p class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom-dashed">Designer <span class="badge bg-warning">24%</span></p>
							<p class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom-dashed">Support <span class="badge bg-info">20%</span></p>
							<p class="d-flex align-items-center justify-content-between gap-2 mb-0 pb-0">Engineer <span class="badge bg-primary">10%</span></p>
						</div>
					</div>
				</div>
				<!-- End Role Demand Forecast -->

				<!-- Start AI Hiring Predictions -->
				<div class="col-xxl-4 col-xl-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="mb-4">
								<h2 class="mb-0 card-title">AI Hiring Predictions</h2>
							</div>

							<!-- Item 1 -->
							<div class="card bg-info border-info mb-4">
								<div class="card-body">
									<h3 class="fs-16 fw-semibold text-white mb-2">High-Demand Alert: Frontend Developers</h3>
									<p class="fs-13 mb-4 text-white">Predicted need for 6 frontend developers by Q3. Market competition increasing. Recommend starting recruitment now</p>
									<div class="d-flex align-items-center gap-2">
										<span class="badge bg-white p-2 text-dark">Manager Action Needed</span>
										<span class="badge bg-white p-2 text-dark">30-day trend</span>
									</div>
								</div>
								<img src="{{URL::asset('build/img/icons/star.svg')}}" alt="star-icon" class="img-fluid w-25 position-absolute bottom-0 end-0">
							</div>

							<!-- Item 2 -->
							<div class="card bg-purple border-purple mb-0">
								<div class="card-body">
									<h3 class="fs-16 fw-semibold text-white mb-2">Budget Optimization</h3>
									<p class="fs-13 mb-4 text-white">Development budget under-utilized by $35K. Recommend accelerating senior engineer hiring or increasing referral bonuses.</p>
									<div class="d-flex align-items-center gap-2">
										<span class="badge bg-white p-2 text-dark">High Priority</span>
										<span class="badge bg-white p-2 text-dark">Actionable</span>
									</div>
								</div>
								<img src="{{URL::asset('build/img/icons/star.svg')}}" alt="star-icon" class="img-fluid w-25 position-absolute bottom-0 end-0">
							</div>
						</div>
					</div>
				</div>
				<!-- End AI Hiring Predictions -->

				<!-- Start Active Position -->
				<div class="col-xxl-4 col-xl-6 d-flex">
					<div class="card mb-0 flex-fill">
						<div class="card-body">
							<div class="mb-4">
								<h2 class="mb-0 card-title">Active Position</h2>
							</div>
							<!-- Item 1 -->
							<div class="bg-success-gradient-100 rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div>
									<h3 class="fs-14 mb-2">Product Manager</h3>
									<div class="d-flex align-items-center gap-2">
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-users fw-medium text-gray-7"></i>15 Applicants</p>
										<span class="inner-line"></span>
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-clock-edit fw-medium text-gray-7"></i>3 Interview</p>
									</div>
								</div>
								<span class="badge badge-success d-inline-flex align-items-center">
									<i class="ti ti-point-filled me-1"></i>Active
								</span>
							</div>
							<!-- Item 2 -->
							<div class="bg-success-gradient-100 rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div>
									<h3 class="fs-14 mb-2">QA Analyst</h3>
									<div class="d-flex align-items-center gap-2">
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-users fw-medium text-gray-7"></i>12 Applicants</p>
										<span class="inner-line"></span>
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-clock-edit fw-medium text-gray-7"></i>2 Interview</p>
									</div>
								</div>
								<span class="badge badge-success d-inline-flex align-items-center">
									<i class="ti ti-point-filled me-1"></i>Active
								</span>
							</div>
							<!-- Item 3 -->
							<div class="bg-success-gradient-100 rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div>
									<h3 class="fs-14 mb-2">DevOps Engineer</h3>
									<div class="d-flex align-items-center gap-2">
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-users fw-medium text-gray-7"></i>16 Applicants</p>
										<span class="inner-line"></span>
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-clock-edit fw-medium text-gray-7"></i>5 Interview</p>
									</div>
								</div>
								<span class="badge badge-success d-inline-flex align-items-center">
									<i class="ti ti-point-filled me-1"></i>Active
								</span>
							</div>
							<!-- Item 4 -->
							<div class="bg-danger-gradient-100 rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
								<div>
									<h3 class="fs-14 mb-2">Data Scientist</h3>
									<div class="d-flex align-items-center gap-2">
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-users fw-medium text-gray-7"></i>18 Applicants</p>
										<span class="inner-line"></span>
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-clock-edit fw-medium text-gray-7"></i>4 Interview</p>
									</div>
								</div>
								<span class="badge badge-danger d-inline-flex align-items-center">
									<i class="ti ti-point-filled me-1"></i>Closed
								</span>
							</div>
							<!-- Item 5 -->
							<div class="bg-success-gradient-100 rounded p-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
								<div>
									<h3 class="fs-14 mb-2">UX Designer</h3>
									<div class="d-flex align-items-center gap-2">
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-users fw-medium text-gray-7"></i>22 Applicants</p>
										<span class="inner-line"></span>
										<p class="mb-0 d-flex align-items-center gap-1"> <i class="ti ti-clock-edit fw-medium text-gray-7"></i>6 Interview</p>
									</div>
								</div>
								<span class="badge badge-purple d-inline-flex align-items-center">
									<i class="ti ti-point-filled me-1"></i>Interview
								</span>
							</div>
						</div>
					</div>
				</div>
				<!-- End Active Position -->
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