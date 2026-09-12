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
					<h2 class="mb-1">Learning Analytics</h2>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{url('index')}}"><i class="ti ti-smart-home"></i></a>
							</li>
							<li class="breadcrumb-item">
								Training
							</li>
							<li class="breadcrumb-item active" aria-current="page">Learning Analytics</li>
						</ol>
					</nav>
				</div>
				<div class="head-icons ms-2">
					<a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
						data-bs-original-title="Collapse" id="collapse-header">
						<i class="ti ti-chevrons-up"></i>
					</a>
				</div>
			</div>
			<!-- /Breadcrumb -->

			<div class="row">

				<!-- Total Exponses -->
				<div class="col-xxl-5 col-lg-12 col-md-12 d-flex">
					<div class="card flex-fill">
						<div class="card-header border-0">
							<div class="d-flex flex-wrap justify-content-between align-items-center">
								<div class="d-flex align-items-center ">
									<h5>Learning Employees</h5>
								</div>
								<div class="dropdown">
									<a href="javascript:void(0);"
										class="btn btn-white border d-inline-flex align-items-center"
										data-bs-toggle="dropdown">
										<i class="ti ti-calendar me-1"></i>2025
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
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
						<div class="card-body py-0">
							<div id="learn-employee"></div>
						</div>
					</div>
				</div>
				<!-- /Total Exponses -->

				<!-- Total Exponses -->
				<div class="col-xxl-7 col-lg-12 col-md-12 d-flex">
					<div class="row flex-fill">
						<div class="col-lg-5 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body pb-0">
									<div class="d-flex flex-column align-items-center">
										<div
											class="p-2 rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2">
											<i class="ti ti-certificate fs-22"></i>
										</div>
										<p class="fw-semibold text-dark mb-1">Certification Completed</p>
										<div class="fw-bold text-dark fs-24 mb-1">225</div>
										<span
											class="badge badge-soft-success rounded-pill border border-success mb-1"><i
												class="ti ti-arrow-up"></i>12%</span>
										<p class="fs-12 fw-normal d-flex align-items-center text-truncate">from last
											month</p>
									</div>
									<div id="certification-chart"></div>
								</div>
							</div>
						</div>
						<div class="col-lg-7 col-md-6 d-flex">
							<div class="card flex-fill">
								<div class="card-body">
									<div class="card-header border-0 p-0">
										<div
											class="d-flex flex-wrap justify-content-between align-items-center row-gap-3">
											<div class="d-flex align-items-center ">
												<h5>Highly Enrolled Courses</h5>
											</div>
											<div class="dropdown">
												<a href="javascript:void(0);"
													class="btn btn-white border d-inline-flex align-items-center"
													data-bs-toggle="dropdown">
													<i class="ti ti-calendar me-1"></i>2025
												</a>
												<ul class="dropdown-menu  dropdown-menu-end p-3">
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1">2024</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1">2023</a>
													</li>
													<li>
														<a href="javascript:void(0);"
															class="dropdown-item rounded-1">2022</a>
													</li>
												</ul>
											</div>
										</div>
									</div>
									<div class="card-body py-0">
										<div id="enroll-course"></div>
										<div>
											<p><i class="ti ti-point-filled me-1 text-info"></i>No of Employees
												increased by <span class="text-success">+20%</span> from last Week
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
				<!-- /Total Exponses -->


			</div>

			<div class="card">
				<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
					<h5>Learning Employee List</h5>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
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
									<th class="no-sort">
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox" id="select-all">
										</div>
									</th>
									<th>Employee Name</th>
									<th>Designation</th>
									<th>Training Course</th>
									<th>Status</th>
									<th>Score(%)</th>
									<th>Completion Date</th>
									<th>Feedback</th>
									<th>Attempts</th>
									<th>Certificate</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Anthony Lewis</a>
											</div>
										</div>
									</td>
									<td>Accountant</td>
									<td>
										<p class="fw-medium text-dark mb-0">Git Training</p>
									</td>
									<td><span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">Completed</span>
									</td>
									<td>80</td>
									<td>14 Jun 2025</td>
									<td>Good</td>
									<td>01</td>
									<td><span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span></td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-13.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Brian Villalobos</a>
											</div>
										</div>
									</td>
									<td>App Developer</td>
									<td>
										<p class="fw-medium text-dark mb-0">HTML Training</p>
									</td>
									<td><span
											class="badge badge-info-transparent d-inline-flex align-items-center badge-xs">In
											Progress</span></td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>
										-
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-12.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Harvey Smith</a>
											</div>
										</div>
									</td>
									<td>Technician</td>
									<td>
										<p class="fw-medium text-dark mb-0">React Training</p>
									</td>
									<td><span
											class="badge badge-purple-transparent d-inline-flex align-items-center badge-xs">Not
											Started</span></td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>
										-
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-16.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Stephan Peralt</a>
											</div>
										</div>
									</td>
									<td>Web Developer</td>
									<td>
										<p class="fw-medium text-dark mb-0">Nodejs Training</p>
									</td>
									<td><span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">Completed</span>
									</td>
									<td>90</td>
									<td>28 Apr 2025</td>
									<td>Excellent</td>
									<td>02</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-15.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Doglas Martini</a>
											</div>
										</div>
									</td>
									<td>Sales Executive Officer</td>
									<td>
										<p class="fw-medium text-dark mb-0">Django Training</p>
									</td>
									<td><span
											class="badge badge-purple-transparent d-inline-flex align-items-center badge-xs">Not
											Started</span></td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>
										-
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-14.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Linda Ray</a>
											</div>
										</div>
									</td>
									<td>Designer</td>
									<td>
										<p class="fw-medium text-dark mb-0">Java Training</p>
									</td>
									<td><span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">Completed</span>
									</td>
									<td>85</td>
									<td>20 Mar 2025</td>
									<td>Excellent</td>
									<td>01</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Elliot Murray</a>
											</div>
										</div>
									</td>
									<td>Account Manager</td>
									<td>
										<p class="fw-medium text-dark mb-0">MySQL Training</p>
									</td>
									<td><span
											class="badge badge-purple-transparent d-inline-flex align-items-center badge-xs">Not
											Started</span></td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>
										-
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-18.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Rebecca Smtih</a>
											</div>
										</div>
									</td>
									<td>SEO Analyst</td>
									<td>
										<p class="fw-medium text-dark mb-0">DevOps Training</p>
									</td>
									<td><span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">Completed</span>
									</td>
									<td>70</td>
									<td>17 Feb 2025</td>
									<td>Good</td>
									<td>02</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
										</span>
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Connie Waters</a>
											</div>
										</div>
									</td>
									<td>Admin</td>
									<td>
										<p class="fw-medium text-dark mb-0">Angular Training</p>
									</td>
									<td><span
											class="badge badge-info-transparent d-inline-flex align-items-center badge-xs">In
											Progress</span></td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>
										-
									</td>
								</tr>
								<tr>
									<td>
										<div class="form-check form-check-md">
											<input class="form-check-input" type="checkbox">
										</div>
									</td>
									<td>
										<div class="d-flex align-items-center file-name-icon">
											<a href="#" class="avatar avatar-md border avatar-rounded">
												<img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="img-fluid" alt="img">
											</a>
											<div class="ms-2">
												<a class="fw-medium fs-14">Lori Broaddus</a>
											</div>
										</div>
									</td>
									<td>Business Analyst</td>
									<td>
										<p class="fw-medium text-dark mb-0">Vuejs Training</p>
									</td>
									<td><span
											class="badge badge-soft-success d-inline-flex align-items-center badge-xs">Completed</span>
									</td>
									<td>95</td>
									<td>24 Jan 2025</td>
									<td>Excellent</td>
									<td>01</td>
									<td>
										<span class="badge badge-success d-inline-flex align-items-center badge-xs">
											<i class="ti ti-point-filled me-1"></i>Issued
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