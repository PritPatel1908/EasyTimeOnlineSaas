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
					<h2 class="mb-1">Recruitment Dashboard</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Dashboard
							</li>
							<li class="breadcrumb-item active" aria-current="page">Recruitment Dashboard</li>
						</ol>
					</nav>
				</div>
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
					<div class="me-2 mb-2">
						<div class="input-icon w-100 position-relative">
							<span class="input-icon-addon">
								<i class="ti ti-calendar text-gray-9"></i>
							</span>
							<input type="text" class="form-control yearpicker" value="December 2025">
						</div>
					</div>
					<div class="me-2 mb-2">
						<div class="dropdown">
							<a href="javascript:void(0);"
								class="dropdown-toggle btn btn-white d-inline-flex align-items-center"
								data-bs-toggle="dropdown">
								<i class="ti ti-arrow-bar-to-down me-1"></i>Download Report
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
					<a href="#" class="btn btn-primary-gradient mb-2"> <i class="ti ti-plus"></i> Add New Job</a>
					<div class="ms-2 head-icons">
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
				<div class="col-xl-8">
					<!-- Candidate Hiring Analysis -->
					<div class="card position-relative analysis-card">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
								<h3 class="mb-0 card-title">Candidates Hiring Analysis</h3>
								<div class="d-flex align-items-center gap-3">
									<div class="dropdown">
										<a href="javascript:void(0);"
											class="dropdown-toggle btn btn-light rounded-pill text-dark dropdown-icon-none"
											data-bs-toggle="dropdown">
											<i class="ti ti-adjustments-horizontal fs-16"></i>
										</a>
										<ul class="dropdown-menu dropdown-menu-end p-3">
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
									<a href="#"> <span><i
												class="ti ti-square-arrow-down-filled text-dark"></i></span> Last 30
										Days </a>
								</div>
							</div>

							<div class="table-responsive candidates-table">
								<table class="table table-nowrap table-borderless mb-0">
									<thead>
										<tr>
											<th class="fw-bold bg-white px-2 ps-0">Department</th>
											<th class="fw-normal bg-white px-2">Applicants</th>
											<th class="fw-normal bg-white px-2">Shortlisted</th>
											<th class="fw-normal bg-white px-2">Interviewed</th>
											<th class="fw-normal bg-white px-2">Offered</th>
											<th class="fw-normal bg-white px-2">Hired</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="px-0 pe-5">
												<div class="d-flex align-items-center">
													<div>
														<h6 class="fw-normal mb-1 fs-14"><a
																href="{{url('invoice-details')}}">Marketing</a></h6>
														<span class="fs-13 d-inline-flex align-items-center">Product
															Manager</span>
													</div>
												</div>
											</td>
											<td>
												<div class="badge bg-primary fs-13  rounded-xxl py-2">14</div>
											</td>
											<td>
												<div class="badge bg-secondary  fs-13 rounded-xxl py-2">08</div>
											</td>

											<td>
												<div class="badge bg-light fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>

											<td>
												<div class="badge bg-light fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>

											<td>
												<div class="badge bg-light fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>
										</tr>
										<tr>
											<td class="px-0 pe-5">
												<div class="d-flex align-items-center">
													<div>
														<h6 class="fw-normal mb-1 fs-14"><a
																href="{{url('invoice-details')}}">Data Analyst</a></h6>
														<span class="fs-13 d-inline-flex align-items-center">Jr Data
															Analyst</span>
													</div>
												</div>
											</td>
											<td>
												<div class="badge bg-primary  fs-13  rounded-xxl py-2">16</div>
											</td>
											<td>
												<div class="badge bg-secondary   fs-13 rounded-xxl py-2">12</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>
										</tr>
										<tr>
											<td class="px-0 pe-5">
												<div class="d-flex align-items-center">
													<div>
														<h6 class="fw-normal mb-1 fs-14"><a
																href="{{url('invoice-details')}}">Project Coordinator</a>
														</h6>
														<span class="fs-13 d-inline-flex align-items-center">Jr
															Level</span>
													</div>
												</div>
											</td>
											<td>
												<div class="badge bg-primary  fs-13  rounded-xxl py-2">24</div>
											</td>
											<td>
												<div class="badge bg-secondary   fs-13 rounded-xxl py-2">06</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>
										</tr>
										<tr>
											<td class="px-0 pe-5">
												<div class="d-flex align-items-center">
													<div>
														<h6 class="fw-normal mb-1 fs-14"><a
																href="{{url('invoice-details')}}">Design Lead</a></h6>
														<span class="fs-13 d-inline-flex align-items-center">UI
															Designer</span>
													</div>
												</div>
											</td>
											<td>
												<div class="badge bg-primary  fs-13  rounded-xxl py-2">12</div>
											</td>
											<td>
												<div class="badge bg-secondary   fs-13 rounded-xxl py-2">08</div>
											</td>

											<td>
												<div class="badge bg-dark  fs-13 rounded-xxl py-2">06</div>
											</td>

											<td>
												<div class="badge bg-info  fs-13 rounded-xxl py-2">05</div>
											</td>

											<td>
												<div class="badge bg-light  fs-13 rounded-xxl text-light py-2">14
												</div>
											</td>
										</tr>
										<tr>
											<td class="px-0 pe-5">
												<div class="d-flex align-items-center">
													<div>
														<h6 class="fw-normal mb-1 fs-14"><a
																href="{{url('invoice-details')}}">Project Manager</a></h6>
														<span class="fs-13 d-inline-flex align-items-center">Senior
															Manager</span>
													</div>
												</div>
											</td>
											<td>
												<div class="badge bg-primary  fs-13  rounded-xxl py-2">22</div>
											</td>
											<td>
												<div class="badge bg-secondary   fs-13 rounded-xxl py-2">20</div>
											</td>

											<td>
												<div class="badge bg-dark  fs-13 rounded-xxl py-2">16</div>
											</td>

											<td>
												<div class="badge bg-info  fs-13  rounded-xxl py-2">12</div>
											</td>

											<td>
												<div class="badge bg-success  fs-13  rounded-xxl py-2">04</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

						</div>
					</div>

					<div class="card">
						<div class="card-body py-4">
							<div class="row row-gap-4">

								<div class="col-lg-3 col-md-6 col-sm-6 border-end">
									<div class="d-flex align-items-center justify-content-center flex-column gap-2">
										<div
											class="rounded-3 bg-primary-transparent bg-outline-primary d-flex align-items-center justify-content-center p-2">
											<i class="ti ti-briefcase fs-20"></i>
										</div>
										<div class="text-center">
											<h5 class="mb-0 fw-semibold fs-24 mb-1">47</h5>
											<p class="fs-14">Open Positions</p>
										</div>
									</div>
								</div>

								<div class="col-lg-3 col-md-6 col-sm-6 border-end">
									<div class="d-flex align-items-center justify-content-center flex-column gap-2">
										<div
											class="rounded-3 bg-secondary-transparent bg-outline-secondary d-flex align-items-center justify-content-center p-2">
											<i class="ti ti-users-group fs-20"></i>
										</div>
										<div class="text-center">
											<h5 class="mb-0 fw-semibold fs-24 mb-1">2,384</h5>
											<p class=" fs-14">Total Candidates</p>
										</div>
									</div>
								</div>

								<div class="col-lg-3 col-md-6 col-sm-6 border-end">
									<div class="d-flex align-items-center justify-content-center flex-column gap-2">
										<div
											class="rounded-3 bg-dark-transparent bg-outline-dark d-flex align-items-center justify-content-center p-2">
											<i class="ti ti-calendar-due fs-20"></i>
										</div>
										<div class="text-center">
											<h5 class="mb-0 fw-semibold fs-24 mb-1">12</h5>
											<p class=" fs-14">Interviews Today</p>
										</div>
									</div>
								</div>

								<div class="col-lg-3 col-md-6 col-sm-6">
									<div class="d-flex align-items-center justify-content-center flex-column gap-2">
										<div
											class="rounded-3 bg-info-transparent bg-outline-info d-flex align-items-center justify-content-center p-2">
											<i class="ti ti-files fs-20"></i>
										</div>
										<div class="text-center">
											<h5 class="mb-0 fw-semibold fs-24 mb-1">28</h5>
											<p class=" fs-14">Offers Released</p>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<div class="col-xl-4 d-flex flex-column">
					<!-- Salary Range Distribution -->
					<div class="card">
						<div class="card-body">
							<div
								class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
								<h3 class="mb-0 card-title">Recruitment Overview</h3>
								<div class="d-flex align-items-center gap-3">
									<a href="#"> <span><i
												class="ti ti-square-arrow-down-filled text-dark"></i></span>
										Monthly</a>
								</div>
							</div>

							<!-- Top Stats -->
							<div class="d-flex justify-content-between align-items-center mb-3">
								<div class="text-center">
									<p class="mb-1  fw-medium">Offer Acceptance</p>
									<h3 class="fw-bold mb-0">74.4%</h3>
								</div>
								<div class="text-center">
									<p class="mb-1  fw-medium">Overall Hire Rate</p>
									<h3 class="fw-bold mb-0">2.7%</h3>
								</div>
							</div>

							<!-- Chart -->
							<div class="position-relative mx-auto recruitment-chart">
								<canvas id="recruitment"></canvas>

								<!-- Center Text -->
								<div class="position-absolute text-center"
									style="bottom:18%; left:50%; transform:translateX(-50%);">
									<h2 class="fw-bold mb-0">2,384</h2>
									<p class="mb-0 ">Total Applications</p>
								</div>
							</div>
						</div>
					</div>

					<!-- Overview -->
					<div class="card bg-secondary position-relative overflow-hidden flex-fill">
						<div class="card-body d-flex flex-column justify-content-between z-2">
							<div class="mb-4">
								<p class="text-white mb-3">Quick Reminder</p>
								<h3 class="text-white fw-normal fs-20 mb-2">You have 21 Interview Schedule Today!
								</h3>
								<p class="text-white">Dont forget to schedule interviews </p>
							</div>
							<div>
								<a href="#"
									class="btn-primary-gradient rounded-pill d-inline-flex align-items-center gap-2 p-1 ps-3">
									Schedule Now
									<span
										class="btn btn-sm btn-icon bg-white rounded-circle d-flex align-items-center justify-content-center text-dark"><i
											class="ti ti-chevrons-right fs-20"></i></span>
								</a>
							</div>
						</div>
						<img src="{{URL::asset('build/img/bg/bg-05.png')}}" alt="bg" class="img-fluid recruitment-bg">
					</div>
				</div>

			</div>
			<!-- end row -->

			<!-- start row -->
			<div class="row">
				<div class="col-xl-4 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex flex-column justify-content-between">
							<div class="mb-3">
								<div
									class="d-flex flex-column justify-content-center align-items-center mb-4 gap-2">
									<img src="{{URL::asset('build/img/robot.png')}}" alt="robot"
										class="bg-primary-gradient p-2 rounded-3 mb-2">
									<h4 class="mb-0">How can i help you today</h4>
									<p class="d-flex align-items-center gap-2">AI Payroll Assistant <span
											class="badge rounded-pill bg-success d-flex align-items-center gap-1"><i
												class="ti ti-circle-filled fs-6 text-white"></i> Online</span></p>
								</div>
								<div>
									<div class="d-flex align-items-center gap-2 flex-wrap mb-4">
										<span class="click-tag bg-light border text-dark">Analyze top candidates for
											Senior Developer role</span>
										<span class="click-tag bg-light border text-dark">Generate hiring
											report</span>
									</div>
									<div class="card bg-light border shadow-none rounded-md mb-0">
										<div class="card-body">
											<span class="d-flex align-items-center gap-1 mb-2 text-primary"> <i
													class="ti ti-sparkles"></i> AI Assistant</span>
											<p class="mb-0">👋 Welcome to your AI Recruitment Assistant! I'm here to
												help you</p>
										</div>
									</div>
								</div>
							</div>
							<div class="chat-input-item mt-3 bg-white rounded-pill">
								<input type="text" class="form-control"
									placeholder="Ask me anything about Requirement">
								<a href="#" class="btn btn-icon"><i class="ti ti-send"></i></a>
							</div>
						</div>
					</div>
				</div>
				<!-- end col -->

				<div class="col-xl-4 d-flex">
					<div class="card flex-fill">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<div
									class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
									<h3 class="mb-0 card-title">Average Time to Hire</h3>
									<div class="d-flex align-items-center gap-3">
										<a href="#"> <span><i
													class="ti ti-square-arrow-down-filled text-dark"></i></span>
											Last 30 Days </a>
									</div>
								</div>
								<div class="chartjs-wrapper-demo avghiretime-chart">
									<canvas id="avghiretime"></canvas>
								</div>

								<div class="d-flex flex-column mb-3 gap-2">
									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-block border border-2 h-12 border-secondary border-opacity-100 rounded-5 me-2"></span>
											<p class="fs-14">Applied to Shortlisted</p>
										</div>
										<p class="badge bg-light border rounded-3 btn-sm fs-13 text-dark">05</p>
									</div>

									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-block border border-2 h-12 border-secondary border-opacity-75 rounded-5 me-2"></span>
											<p class="fs-14">Shortlisted</p>
										</div>
										<p class="badge bg-light border rounded-3 btn-sm fs-13 text-dark">07</p>
									</div>

									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-block border border-2 h-12 border-secondary border-opacity-50 rounded-5 me-2"></span>
											<p class="fs-14">Interview to Offer</p>
										</div>
										<p class="badge bg-light border rounded-3 btn-sm fs-13 text-dark">10</p>
									</div>

									<div class="d-flex align-items-center justify-content-between">
										<div class="d-flex align-items-center">
											<span
												class="d-block border border-2 h-12 border-secondary border-opacity-25 rounded-5 me-2"></span>
											<p class="fs-14">Acceptance</p>
										</div>
										<p class="badge bg-light border rounded-3 btn-sm fs-13 text-dark">03</p>
									</div>
								</div>
							</div>
							<div
								class="bg-soft-transparent-success rounded-3 text-start w-100 p-2 d-flex align-items-center gap-2 text-dark">
								<div class="avatar avatar-md rounded-3 bg-success flex-shrink-0 p-0">
									<span class="d-flex align-items-center"><i
											class="ti ti-arrow-up-right fs-20"></i></span>
								</div>
								12% faster than industry avg
							</div>
						</div>
					</div>
				</div>
				<!-- end col -->

				<div class="col-xl-4 d-flex">
					<!-- Item 1 -->
					<div class="card flex-fill">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<div
									class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
									<h3 class="mb-0 card-title">Upcoming Schedules</h3>
									<div class="d-flex align-items-center gap-3">
										<a href="#"> <span><i
													class="ti ti-square-arrow-down-filled text-dark"></i></span>
											Last 30 Days </a>
									</div>
								</div>

								<div class="d-flex align-items-center justify-content-between mb-4">
									<div class="d-flex align-items-center">
										<div class="date-card border rounded-3">
											<p class="m-0 fs-13">Mar</p>
											<h4>02</h4>
										</div>
										<div>
											<h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Product
													Designer</a></h6>
											<p class="fs-13"><i class="ti ti-clock-hour-3"></i> 09:00 AM - 10:30 AM
											</p>
										</div>
									</div>
									<div class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/avatar/avatar-01.png')}}" class="rounded-circle" alt="img">
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-4">
									<div class="d-flex align-items-center">
										<div class="date-card border rounded-3">
											<p class="m-0 fs-13">Apr</p>
											<h4>22</h4>
										</div>
										<div>
											<h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Marketing
													Manager</a></h6>
											<p class="fs-13"><i class="ti ti-clock-hour-3"></i> 01:00 PM - 02:00 PM
											</p>
										</div>
									</div>
									<div class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/avatar/avatar-02.png')}}" class="rounded-circle" alt="img">
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-4">
									<div class="d-flex align-items-center">
										<div class="date-card border rounded-3">
											<p class="m-0 fs-13">May</p>
											<h4>11</h4>
										</div>
										<div>
											<h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Sr. Data
													Science</a></h6>
											<p class="fs-13"><i class="ti ti-clock-hour-3"></i> 11:00 AM - 12:30 PM
											</p>
										</div>
									</div>
									<div class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/avatar/avatar-03.jpg')}}" class="rounded-circle" alt="img">
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-4">
									<div class="d-flex align-items-center">
										<div class="date-card border rounded-3">
											<p class="m-0 fs-13">Jun</p>
											<h4>07</h4>
										</div>
										<div>
											<h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Software
													Engineer</a></h6>
											<p class="fs-13"><i class="ti ti-clock-hour-3"></i> 02:00 PM - 03:30 PM
											</p>
										</div>
									</div>
									<div class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/avatar/avatar-04.png')}}" class="rounded-circle" alt="img">
									</div>
								</div>
								<div class="d-flex align-items-center justify-content-between mb-3">
									<div class="d-flex align-items-center">
										<div class="date-card border rounded-3">
											<p class="m-0 fs-13">Aug</p>
											<h4>18</h4>
										</div>
										<div>
											<h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Financial
													Analyst</a></h6>
											<p class="fs-13"><i class="ti ti-clock-hour-3"></i> 03:00 PM - 04:00 PM
											</p>
										</div>
									</div>
									<div class="avatar flex-shrink-0">
										<img src="{{URL::asset('build/img/avatar/avatar-05.png')}}" class="rounded-circle" alt="img">
									</div>
								</div>
							</div>

							<a href="#" class="btn btn-primary-gradient w-100 mt-1">View All Schedule</a>
						</div>
					</div>

				</div>
				<!-- end col -->
			</div>
			<!-- end row -->

			<div class="col-xl-12">
				<div class="card">
					<div class="card-body">

						<div class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
							<h3 class="mb-0 card-title">Stage Performance</h3>
							<div class="d-flex align-items-center gap-3">
								<a href="#" class="btn btn-icon border bg-light rounded-pill p-2 text-dark"> <i
										class="ti ti-adjustments-horizontal fs-16"></i> </a>
								<a href="#"> <span><i class="ti ti-square-arrow-down-filled text-dark"></i></span>
									Last 30 Days </a>
							</div>
						</div>

						<div
							class="row flex-wrap g-3 row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-5 performance-card">

							<div class="col">
								<div class="card shadow-none mb-0 flex-fill">
									<div class="card-body">
										<div class="d-flex align-items-center justify-content-between mb-4">
											<div>
												<p class="fw-semibold text-truncate mb-0">Applied</p>
											</div>
											<div
												class="avatar avatar-lg bg-primary-gradient rounded-4 flex-shrink-0">
												<i class="ti ti-id text-white fs-24"></i>
											</div>
										</div>
										<div>
											<h2 class="mb-2">1,848</h2>
											<p class="fs-14 d-flex align-items-center justify-content-between mb-1">
												Overall Progress <span class="fs-13 fw-normal">36.3%</span> </p>
											<div class="progress progress-pill-primary">
												<div class="progress-bar" role="progressbar" style="width: 36.3%;"
													aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="col">
								<div class="card shadow-none mb-0 flex-fill">
									<div class="card-body">
										<div class="d-flex align-items-center justify-content-between mb-4">

											<div>
												<p class="fw-semibold text-truncate mb-0">Shortlisted</p>
											</div>
											<div
												class="avatar avatar-lg bg-secondary-gradient rounded-4 flex-shrink-0">
												<i class="ti ti-hourglass-low text-white fs-24"></i>
											</div>
										</div>
										<div>
											<h2 class="mb-2">2,384 </h2>
											<p class="fs-14 d-flex align-items-center justify-content-between mb-1">
												Conversion rate <span class="fs-13 fw-normal">37.4%</span> </p>
											<div class="progress progress-pill-secondary">
												<div class="progress-bar" role="progressbar" style="width: 37.4%;"
													aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="col">
								<div class="card shadow-none mb-0 flex-fill">
									<div class="card-body">
										<div class="d-flex align-items-center justify-content-between mb-4">

											<div>
												<p class="fw-semibold text-truncate mb-0">Interviewed</p>
											</div>
											<div class="avatar avatar-lg bg-dark-gradient rounded-4 flex-shrink-0">
												<i class="ti ti-calendar-up text-white fs-24"></i>
											</div>
										</div>
										<div>
											<h2 class="mb-2">892</h2>
											<p class="fs-14 d-flex align-items-center justify-content-between mb-1">
												Conversion rate <span class="fs-13 fw-normal">36.3%</span> </p>
											<div class="progress progress-pill-dark">
												<div class="progress-bar dark-progress" role="progressbar"
													style="width: 36.3%;" aria-valuenow="75" aria-valuemin="0"
													aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="col">
								<div class="card shadow-none mb-0 flex-fill">
									<div class="card-body">
										<div class="d-flex align-items-center justify-content-between mb-4">

											<div>
												<p class="fw-semibold text-truncate mb-0">Offered</p>
											</div>
											<div class="avatar avatar-lg bg-info-gradient rounded-4 flex-shrink-0">
												<i class="ti ti-file-description text-white fs-24"></i>
											</div>
										</div>
										<div>
											<h2 class="mb-2">324</h2>
											<p class="fs-14 d-flex align-items-center justify-content-between mb-1">
												Conversion rate <span class="fs-13 fw-normal">26.5%</span> </p>
											<div class="progress progress-pill-info">
												<div class="progress-bar" role="progressbar" style="width: 26.5%;"
													aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="col">
								<div class="card shadow-none mb-0 flex-fill">
									<div class="card-body">
										<div class="d-flex align-items-center justify-content-between mb-4">

											<div>
												<p class="fw-semibold text-truncate mb-0">Hired</p>
											</div>
											<div
												class="avatar avatar-lg bg-success-gradient rounded-4 flex-shrink-0">
												<i class="ti ti-user-check text-white fs-24"></i>
											</div>
										</div>
										<div>
											<h2 class="mb-2">64</h2>
											<p class="fs-14 d-flex align-items-center justify-content-between mb-1">
												Conversion rate <span class="fs-13 fw-normal">41.2%</span> </p>
											<div class="progress progress-pill-success">
												<div class="progress-bar" role="progressbar" style="width: 41.2%;"
													aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>

					</div>
				</div>
			</div>

			<div class="row">
				<!-- Schedules -->
				<div class="col-xxl-4 d-flex">
					<div class="card flex-fill">
						<div class="card-body">
							<div
								class="pb-2 d-flex align-items-center justify-content-between flex-wrap mb-3 gap-2">
								<h3 class="mb-0 card-title">Recent Applications</h3>
							</div>
							<div class="border bg-light rounded-3 p-3 mb-4">
								<div class="d-flex justify-content-between align-items-center">
									<div class="d-flex align-items-center gap-2">
										<span class="avatar avatar-rounded flex-shrink-0">
											<img class="border border-white" src="{{URL::asset('build/img/users/user-49.jpg')}}"
												alt="img">
										</span>
										<div>
											<h6 class="text-truncate mb-1">Andrew Stuart</h6>
											<div class="d-flex align-items-center flex-wrap">
												<p class="fs-13">Frontend Developer</p>
											</div>
										</div>
									</div>
									<span class="badge badge-purple-transparent d-flex align-items-center gap-1"><i
											class="ti ti-circle-filled fs-6 text-purple"></i> Interview</span>
								</div>
								<div class="d-flex align-items-center justify-content-between border-top mt-2 pt-2">
									<p class="fs-13 mb-0"><i class="ti ti-user me-1"></i>7 years exp</p>
									<p class="fs-13 mb-0"><i class="ti ti-calendar me-1"></i>Applied: Dec 27, 2025
									</p>
								</div>
							</div>
							<div class="border bg-light rounded-3 p-3 mb-3">
								<div class="d-flex justify-content-between align-items-center">
									<div class="d-flex align-items-center gap-2">
										<span class="avatar avatar-rounded flex-shrink-0">
											<img class="border border-white" src="{{URL::asset('build/img/users/user-50.jpg')}}"
												alt="img">
										</span>
										<div>
											<h6 class="text-truncate mb-1">Jessica Brown</h6>
											<div class="d-flex align-items-center flex-wrap">
												<p class="fs-13">UI/UX Designer</p>
											</div>
										</div>
									</div>
									<span
										class="badge badge-secondary-transparent d-flex align-items-center gap-1"><i
											class="ti ti-circle-filled fs-6 text-secondary"></i> Shortlisted</span>
								</div>
								<div class="d-flex align-items-center justify-content-between border-top mt-2 pt-2">
									<p class="fs-13 mb-0"><i class="ti ti-user me-1"></i>7 years exp</p>
									<p class="fs-13 mb-0"><i class="ti ti-calendar me-1"></i>Applied: Dec 27, 2025
									</p>
								</div>
							</div>

							<a class="btn btn-primary-gradient w-100 mt-2">View All Schedule</a>
						</div>
					</div>
				</div>
				<!-- /Schedules -->

				<div class="col-xxl-8">
					<div class="card openings-table">
						<div class="card-body">
							<div class="pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
								<h3 class="mb-0 card-title">Active Job Openings</h3>
								<a href="javascript:void(0);"
									class="btn btn-white border btn-md d-inline-flex align-items-center"
									data-bs-toggle="dropdown">
									View All
								</a>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-nowrap mb-0">
								<thead>
									<tr>
										<th>Job ID</th>
										<th>Date</th>
										<th>Job Title</th>
										<th>Location</th>
										<th>Department</th>
										<th>Applicants</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><a href="#">
												<p class="text-gray-5">JOB-001</p>
											</a></td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-calendar-up"></i> Jan 03, 2026
											</h6>
										</td>
										<td>
											<h6 class="fw-medium mb-1">Frontend Developer</h6>
											<span
												class="badge badge-danger-transparent d-inline-flex align-items-center text-danger">
												High Priority </span>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-map-pin-pin"></i> Remote</h6>
										</td>
										<td>
											<p>Engineering</p>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-user"></i> 1452</h6>
										</td>
									</tr>
									<tr>
										<td><a href="#">
												<p class="text-gray-5">JOB-002</p>
											</a></td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-calendar-up"></i> Jan 02, 2026
											</h6>
										</td>
										<td>
											<h6 class="fw-medium mb-1">Product Manager</h6>
											<span
												class="badge badge-danger-transparent d-inline-flex align-items-center text-danger">
												High Priority </span>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-map-pin-pin"></i> Office</h6>
										</td>
										<td>
											<p>Product</p>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-user"></i> 1342</h6>
										</td>
									</tr>
									<tr>
										<td><a href="#">
												<p class="text-gray-5">JOB-003</p>
											</a></td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-calendar-up"></i> Jan 02, 2026
											</h6>
										</td>
										<td>
											<h6 class="fw-medium mb-1">UX Designer</h6>
											<span
												class="badge badge-danger-transparent d-inline-flex align-items-center text-danger">
												High Priority </span>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-map-pin-pin"></i> Hybrid</h6>
										</td>
										<td>
											<p>Design</p>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-user"></i> 1287</h6>
										</td>
									</tr>
									<tr>
										<td><a href="#">
												<p class="text-gray-5">JOB-004</p>
											</a></td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-calendar-up"></i> Jan 01, 2026
											</h6>
										</td>
										<td>
											<h6 class="fw-medium mb-1">Sales Executive</h6>
											<span
												class="badge badge-info-transparent d-inline-flex align-items-center text-info">
												Medium </span>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-map-pin-pin"></i> Office</h6>
										</td>
										<td>
											<p>Sales</p>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-user"></i> 1198</h6>
										</td>
									</tr>
									<tr>
										<td><a href="#">
												<p class="text-gray-5">JOB-005</p>
											</a></td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-calendar-up"></i> Jan 01, 2026
											</h6>
										</td>
										<td>
											<h6 class="fw-medium mb-1">DevOps Engineer</h6>
											<span
												class="badge badge-info-transparent d-inline-flex align-items-center text-info">
												Medium </span>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-map-pin-pin"></i> Office</h6>
										</td>
										<td>
											<p>Engineering</p>
										</td>
										<td>
											<h6 class="fw-normal"><i class="ti ti-user"></i> 1134</h6>
										</td>
									</tr>
								</tbody>
							</table>
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