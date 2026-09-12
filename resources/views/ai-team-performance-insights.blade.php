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
					<h2 class="mb-1">AI Team Performance Insights</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								AI Center
							</li>
							<li class="breadcrumb-item active" aria-current="page">AI Team Performance Insights</li>
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
					<a href="#" class="btn btn-primary-gradient d-inline-flex align-items-center gap-2"> <i class="ti ti-repeat"></i> Deep Analysis</a>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<!-- start row -->
			<div class="row row-gap-4 mb-4">

				<!-- Start Overall Performance -->
				<div class="col-xxl-5 col-xl-12 d-flex">
					<div class="card w-100 flex-fill mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-4">
								<div>
									<p class="mb-1">Overall Performance</p>
									<h3 class="mb-0 fs-24 fw-bold">89.8</h3>
								</div>
								<div class="avatar avatar-lg bg-secondary rounded">
									<i class="ti ti-users-plus fs-20"></i>
								</div>
							</div>
							<div class="d-flex align-items-center justify-content-between gap-5 flex-lg-nowrap mb-4 pb-4 border-bottom">
								<div class="w-100 d-flex align-items-center justify-content-between gap-2 flex-wrap">
									<p class="mb-0 d-flex align-items-center justify-content-between gap-2 flex-wrap w-100">Team Average <span class="fw-semibold fs-16 text-dark">89%</span> </p>
									<div class="w-100" id="performance-chart"></div>
								</div>
								<div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2"> +4%
									<span class="bg-success btn-icon btn-sm rounded-circle d-flex align-items-center justify-content-center ms-1">
										<i class="ti ti-arrow-up-right fs-20"></i>
									</span>
								</div>
							</div>

							<div class="d-flex align-items-center justify-content-between gap-lg-2 gap-4 flex-lg-nowrap flex-wrap">
								<div class="text-center">
									<p class="mb-1">Employees</p>
									<h4 class="mb-0 fs-24 fw-bold">250</h4>
								</div>
								<div class="text-center">
									<p class="mb-1">Top Performers</p>
									<h4 class="mb-0 fs-24 fw-bold">38</h4>
								</div>
								<div class="text-center">
									<p class="mb-1">Teams Tracked</p>
									<h4 class="mb-0 fs-24 fw-bold">12</h4>
								</div>
								<div class="text-center">
									<p class="mb-1">Engagement</p>
									<h4 class="mb-0 fs-24 fw-bold">4.6/5</h4>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Overall Performance -->

				<!-- Start Productivity -->
				<div class="col-xxl-7 col-xl-12 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body p-0">
							<!-- start row -->
							<div class="row g-0">
								<div class="col-lg-6 col-md-6">
									<div class="bg-white p-4 border-bottom border-end">
										<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
											<div>
												<p class="mb-1">Team Productivity</p>
												<h4 class="mb-0 fs-24 fw-bold">94%</h4>
											</div>
											<div id="productivity-chart"></div>
										</div>
										<p class="d-flex align-items-center gap-1">
											<span class="badge bg-success-transparent rounded">2.3%<i class="ti ti-arrow-up-right ms-1"></i></span>
											vs last month
										</p>
									</div>
								</div>
								<div class="col-lg-6 col-md-6">
									<div class="bg-white p-4 border-bottom">
										<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
											<div>
												<p class="mb-1">Top Performers</p>
												<h4 class="mb-0 fs-24 fw-bold">94%</h4>
											</div>
											<div id="performers-chart"></div>
										</div>
										<p class="d-flex align-items-center gap-1">
											<span class="badge bg-success-transparent rounded">+5 members<i class="ti ti-arrow-up-right ms-1"></i></span>
											vs last month
										</p>
									</div>
								</div>
								<div class="col-lg-6 col-md-6">
									<div class="bg-white p-4 border-end">
										<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
											<div>
												<p class="mb-1">Engagement Score</p>
												<h4 class="mb-0 fs-24 fw-bold">4.6/5</h4>
											</div>
											<div id="engagement-chart"></div>
										</div>
										<p class="d-flex align-items-center gap-1">
											<span class="badge bg-success-transparent rounded">+0.3%<i class="ti ti-arrow-up-right ms-1"></i></span>
											vs last month
										</p>
									</div>
								</div>
								<div class="col-lg-6 col-md-6">
									<div class="bg-white p-4">
										<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
											<div>
												<p class="mb-1">Goals Completed</p>
												<h4 class="mb-0 fs-24 fw-bold">94%</h4>
											</div>
											<div id="goals-chart"></div>
										</div>
										<p class="d-flex align-items-center gap-1">
											<span class="badge bg-danger-transparent rounded">-12%<i class="ti ti-arrow-down-right ms-1"></i></span>
											vs last month
										</p>
									</div>
								</div>
							</div>
							<!-- End row -->
						</div>
					</div>
				</div>
				<!-- End Productivity -->

				<!-- Start Engagement -->
				<div class="col-xxl-12 col-xl-12 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Performance, Productivity & Engagement</h2>
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
							<div id="performance-productivity-engagement-chart"></div>
						</div>
					</div>
				</div>
				<!-- End Engagement -->

				<!-- Start AI Hiring Predictions -->
				<div class="col-xxl-12 col-xl-12 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">AI Hiring Predictions</h2>
								<a href="javascript:void(0);" class="border btn btn-light btn-icon btn-sm d-inline-flex align-items-center justify-content-center rounded-circle">
									<i class="ti ti-arrow-up-right fs-16"></i>
								</a>
							</div>

							<!-- start row -->
							<div class="row row-gap-4">
								<div class="col-xl-4 col-lg-6 col-md-6">
									<div class="card mb-0 overflow-hidden position-relative border border-primary border-opacity-10">
										<div class="card-body position-relative z-1">
											<div class="fs-16 fw-semibold text-dark mb-1">High-Performance Team</div>
											<p class="mb-4">
												Support team consistently exceeds targets. Consider them for complex projects and mentorship roles.
											</p>
											<div class="d-flex align-items-center gap-2">
												<span class="badge badge-primary">Manager Action Needed</span>
												<span class="badge badge-primary">30-day trend</span>
											</div>
										</div>
										<img src="{{URL::asset('build/img/icons/shadow-1.svg')}}" alt="shadow" class="img-fluid position-absolute top-0 start-0">
										<img src="{{URL::asset('build/img/icons/shadow-2.svg')}}" alt="shadow" class="img-fluid position-absolute bottom-0 end-0">
										<img src="{{URL::asset('build/img/icons/star-1.svg')}}" alt="shadow" class="img-fluid position-absolute bottom-0 end-0 star-icon">
									</div>
								</div>
								<div class="col-xl-4 col-lg-6 col-md-6">
									<div class="card mb-0 overflow-hidden position-relative border border-primary border-opacity-10">
										<div class="card-body position-relative z-1">
											<div class="fs-16 fw-semibold text-dark mb-1">Skills Gap Identified</div>
											<p class="mb-4">
												Leadership scores below target. Recommend leadership training for senior team members in Q3.
											</p>
											<div class="d-flex align-items-center gap-2">
												<span class="badge badge-primary">Manager Action Needed</span>
												<span class="badge badge-primary">30-day trend</span>
											</div>
										</div>
										<img src="{{URL::asset('build/img/icons/shadow-1.svg')}}" alt="shadow" class="img-fluid position-absolute top-0 start-0">
										<img src="{{URL::asset('build/img/icons/shadow-2.svg')}}" alt="shadow" class="img-fluid position-absolute bottom-0 end-0">
										<img src="{{URL::asset('build/img/icons/star-1.svg')}}" alt="shadow" class="img-fluid position-absolute bottom-0 end-0 star-icon">
									</div>
								</div>
								<div class="col-xl-4 col-lg-12 col-md-12">
									<div class="card mb-0 overflow-hidden position-relative border border-primary border-opacity-10">
										<div class="card-body position-relative z-1">
											<div class="fs-16 fw-semibold text-dark mb-1">Goal Achievement Prediction</div>
											<p class="mb-4">
												Based on current trajectory, 92% of team goals will month-end. On track for exceptional quarter.
											</p>
											<div class="d-flex align-items-center gap-2">
												<span class="badge badge-primary">Manager Action Needed</span>
												<span class="badge badge-primary">30-day trend</span>
											</div>
										</div>
										<img src="{{URL::asset('build/img/icons/shadow-1.svg')}}" alt="shadow" class="img-fluid position-absolute top-0 start-0">
										<img src="{{URL::asset('build/img/icons/shadow-2.svg')}}" alt="shadow" class="img-fluid position-absolute bottom-0 end-0">
										<img src="{{URL::asset('build/img/icons/star-1.svg')}}" alt="shadow" class="img-fluid position-absolute bottom-0 end-0 star-icon">
									</div>
								</div>
							</div>
							<!-- end row -->

						</div>
					</div>
				</div>
				<!-- End AI Hiring Predictions -->

				<!-- Start Productivity & Quality Metrics -->
				<div class="col-xxl-4 col-xl-12 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Productivity & Quality Metrics</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-chart-arrows-vertical fs-14 me-1"></i>Q1
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q1</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q2</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q3</a>
										</li>
									</ul>
								</div>
							</div>
							<div id="productivity-quality-chart"></div>
							<div class="d-flex align-items-center justify-content-center gap-3 mb-4">
								<p class="d-flex align-items-center gap-1 text-dark mb-0">
									<i class="ti ti-square-rounded-filled text-primary fs-13"></i> Tasks Completed
								</p>
								<p class="d-flex align-items-center gap-1 text-dark mb-0">
									<i class="ti ti-square-rounded-filled text-secondary fs-13"></i>Quality Score
								</p>
							</div>
							<p class="badge badge-outline-success text-dark bg-success-transparent fs-12 fw-medium mb-0 px-3 py-2 w-100 text-wrap"> Productivity up 18% while maintaining 94+.</p>
						</div>
					</div>
				</div>
				<!-- End Productivity & Quality Metrics -->

				<!-- Start Team Collaboration Scores -->
				<div class="col-xxl-5 col-xl-6 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Team Collaboration Scores</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14 me-1"></i>Jan 2026
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Jan 2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Feb 2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Mar 2026</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="d-flex align-items-center gap-3 mb-4">
								<p class="d-flex align-items-center gap-1 text-dark mb-0 border rounded px-2 py-1">
									<i class="ti ti-circle-filled text-primary fs-12"></i> Internal
								</p>
								<p class="d-flex align-items-center gap-1 text-dark mb-0 border rounded px-2 py-1">
									<i class="ti ti-circle-filled text-secondary fs-12"></i>Cross-team
								</p>
							</div>
							<div class="mb-4" id="team-collaboration-chart"></div>
							<p class="fs-14 mb-0 px-3 py-2 w-100 mb-0 text-dark bg-light-gradient-100 rounded"> Sales excels in cross-team collaboration (88 score).</p>
						</div>
					</div>
				</div>
				<!-- End Team Collaboration Scores -->

				<!-- Start Goal Completion -->
				<div class="col-xxl-3 col-xl-6 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Goal Completion</h2>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-chart-arrows-vertical fs-14 me-1"></i>Q1
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q1</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q2</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Q3</a>
										</li>
									</ul>
								</div>
							</div>
							<div id="goal-completion-chart"></div>
							<div class="fs-14 fw-medium text-dark mb-4 text-center">Total Goals : 695</div>
							<div>
								<div class="d-flex align-items-center justify-content-between mb-2 border rounded-pill p-2 px-3">
									<p class="d-flex align-items-center gap-1 mb-0">
										<i class="ti ti-square-rounded-filled text-primary fs-10"></i>Completed
									</p>
									<span class="fs-14 fw-semibold mb-0 text-dark">342</span>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-2 border rounded-pill p-2 px-3">
									<p class="d-flex align-items-center gap-1 mb-0">
										<i class="ti ti-square-rounded-filled text-secondary-700 fs-10"></i> In Progress
									</p>
									<span class="fs-14 fw-semibold mb-0 text-dark">185</span>
								</div>
								<div class="d-flex align-items-center justify-content-between border rounded-pill p-2 px-3">
									<p class="d-flex align-items-center gap-1 mb-0">
										<i class="ti ti-square-rounded-filled text-warning-300 fs-10"></i> Not Started
									</p>
									<span class="fs-14 fw-semibold mb-0 text-dark">64</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Goal Completion -->

				<!-- Start Productivity & Quality Metrics -->
				<div class="col-xxl-7 col-xl-6 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Productivity & Quality Metrics</h2>
								<div class="d-flex align-items-center gap-3">
									<p class="d-flex align-items-center gap-1 text-dark mb-0">
										<i class="ti ti-square-rounded-filled text-secondary fs-13"></i> Tasks Completed
									</p>
									<p class="d-flex align-items-center gap-1 text-dark mb-0">
										<i class="ti ti-square-rounded-filled text-primary fs-13"></i>Quality Score
									</p>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="border btn btn-white btn-md d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar fs-14 me-1"></i>Jan 2026
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Jan 2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Feb 2026</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Mar 2026</a>
										</li>
									</ul>
								</div>
							</div>
							<p class="mb-0 px-3 py-2 w-100 fs-14 border rounded text-dark"> Productivity up 18% while maintaining 94+ quality score.</p>
							<div id="productivity-quality-metrics-chart"></div>
						</div>
					</div>
				</div>
				<!-- End Productivity & Quality Metrics -->

				<!-- Start Skill Assesments -->
				<div class="col-xxl-5 col-xl-6 d-flex">
					<div class="card w-100 mb-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
								<h2 class="mb-0 card-title">Skill Assesments</h2>
							</div>
							<div class="mb-4" id="skill-assesment-chart"></div>
							<p class="mb-0 p-2 w-100 fs-12 fw-medium border rounded text-secondary bg-secondary-transparent d-flex align-items-center gap-2"> <span class="btn-icon btn-sm bg-white rounded d-flex align-items-center justify-content-center"><i class="ti ti-thumb-up-filled fs-16"></i></span>  68% of employees score 80+, Strong Skills.</p>
						</div>
					</div>
				</div>
				<!-- End Skill Assesments -->

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